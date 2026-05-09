<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php 
$users = $users ?? [];
$activeGrants = $activeGrants ?? [];
$userMappings = $userMappings ?? '{}';
$flash = $flash ?? null;
?>

<div class="container mt-4 mb-5" style="max-width: 800px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Manage User Assignments</h5>
            <a href="/LabSync-System/grants" class="btn btn-sm btn-light text-warning fw-bold border">Cancel</a>
        </div>
        <div class="card-body p-4">
            
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="alert alert-warning d-flex align-items-center mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Warning:</strong> Saving this form will completely <strong>replace</strong> the user's current grant assignments with whatever is checked below.
                </div>
            </div>

            <form action="/LabSync-System/grants/updateAssignment" method="POST">
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">1. Select User to Manage</label>
                    <select class="form-select border-warning" name="userID" id="userSelect" required onchange="loadUserData()">
                        <option value="" disabled selected>-- Choose a user --</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?= (int)$user['userID'] ?>" data-role="<?= htmlspecialchars($user['userType']) ?>">
                                <?= htmlspecialchars($user['userName']) ?> (<?= htmlspecialchars(str_replace('_', ' ', $user['userType'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">2. Update Grants</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                        <?php if(empty($activeGrants)): ?>
                            <p class="text-muted mb-0">No active grants available.</p>
                        <?php else: ?>
                            <?php foreach($activeGrants as $grant): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input border-secondary grant-checkbox" type="checkbox" name="grantIDs[]" value="<?= (int)$grant['grantID'] ?>" id="grant_<?= $grant['grantID'] ?>">
                                    <label class="form-check-label" for="grant_<?= $grant['grantID'] ?>">
                                        <strong><?= htmlspecialchars($grant['grantName']) ?></strong> 
                                        <span class="text-muted ms-1">(Bal: $<?= number_format($grant['currentBalance'], 2) ?> | Exp: <?= date('M d, Y', strtotime($grant['expirationDate'])) ?>)</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4" id="billingSection">
                    <label class="form-label fw-semibold text-dark">3. Update Billing Coverage</label>
                    <div class="input-group" style="max-width: 200px;">
                        <input type="number" step="0.01" min="1" max="100" class="form-control" id="billingInput" name="billingPercentage" value="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Distributed equally across all checked grants.</small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2">
                        <i class="bi bi-save2 me-1"></i> Overwrite Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load the user mappings from PHP into JavaScript
const userMappings = <?= $userMappings ?>;

function loadUserData() {
    const select = document.getElementById('userSelect');
    const selectedOption = select.options[select.selectedIndex];
    const role = selectedOption.getAttribute('data-role');
    const userID = select.value;
    const billingSection = document.getElementById('billingSection');
    const billingInput = document.getElementById('billingInput');

    // 1. Clear all checkboxes and reset percentage
    document.querySelectorAll('.grant-checkbox').forEach(cb => cb.checked = false);
    billingInput.value = '100';

    // 2. Handle Lab Manager logic
    if (role === 'lab_manager') {
        billingSection.style.display = 'none';
        billingInput.removeAttribute('required');
        billingInput.value = '0'; 
    } else {
        billingSection.style.display = 'block';
        billingInput.setAttribute('required', 'required');
    }

    // 3. Pre-check boxes and set percentage based on existing data
    if (userID && userMappings[userID]) {
        const grants = userMappings[userID];
        if (grants.length > 0) {
            // Set input to their current percentage
            if (role !== 'lab_manager') {
                billingInput.value = parseFloat(grants[0].billingPercentage);
            }
            // Check their current grants
            grants.forEach(g => {
                const cb = document.getElementById('grant_' + g.grantID);
                if (cb) cb.checked = true;
            });
        }
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>