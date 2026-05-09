<?php 
require_once __DIR__ . '/../../includes/header.php'; 

// Silence VS Code Intelephense warnings
$users = $users ?? [];
$activeGrants = $activeGrants ?? [];
$flash = $flash ?? null;
?>

<div class="container mt-4 mb-5" style="max-width: 800px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-person-bounding-box me-2"></i> Assign Grants to User</h5>
            <a href="/LabSync-System/grants" class="btn btn-sm btn-light text-info fw-bold">Cancel</a>
        </div>
        <div class="card-body p-4">
            
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="/LabSync-System/grants/processAssign" method="POST">
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">1. Select User</label>
                    <select class="form-select" name="userID" id="userSelect" required onchange="toggleBillingSection()">
                        <option value="" disabled selected>-- Choose a user --</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?= (int)$user['userID'] ?>" data-role="<?= htmlspecialchars($user['userType']) ?>">
                                <?= htmlspecialchars($user['userName']) ?> (<?= htmlspecialchars(str_replace('_', ' ', $user['userType'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">2. Select Grants (Check all that apply)</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                        <?php if(empty($activeGrants)): ?>
                            <p class="text-muted mb-0">No active grants available to assign.</p>
                        <?php else: ?>
                            <?php foreach($activeGrants as $grant): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input border-secondary" type="checkbox" name="grantIDs[]" value="<?= (int)$grant['grantID'] ?>" id="grant_<?= $grant['grantID'] ?>">
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
                    <label class="form-label fw-semibold text-dark">3. Billing Coverage Percentage</label>
                    <div class="input-group" style="max-width: 200px;">
                        <input type="number" step="0.01" min="1" max="100" class="form-control" id="billingInput" name="billingPercentage" value="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">How much of the booking cost will this grant cover for this user?</small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-info text-white px-4 py-2">
                        <i class="bi bi-link-45deg me-1"></i> Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBillingSection() {
    const select = document.getElementById('userSelect');
    const selectedOption = select.options[select.selectedIndex];
    const role = selectedOption.getAttribute('data-role');
    const billingSection = document.getElementById('billingSection');
    const billingInput = document.getElementById('billingInput');

    // If Lab Manager, hide the billing section and remove the "required" attribute
    if (role === 'lab_manager') {
        billingSection.style.display = 'none';
        billingInput.removeAttribute('required');
        billingInput.value = '0'; 
    } else {
        // Otherwise, show it and make it required
        billingSection.style.display = 'block';
        billingInput.setAttribute('required', 'required');
        if (billingInput.value === '0') billingInput.value = '100'; 
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>