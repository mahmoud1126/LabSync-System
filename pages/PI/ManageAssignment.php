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
                    <label class="form-label fw-semibold text-dark">2. Update Grants &amp; Billing %</label>
                    <p class="text-muted small mb-2" id="billingHelp">
                        For researchers, set the billing percentage for each checked grant. The total must equal exactly 100%.
                    </p>
                    <div class="border rounded p-3 bg-light" style="max-height: 320px; overflow-y: auto;">
                        <?php if(empty($activeGrants)): ?>
                            <p class="text-muted mb-0">No active grants available.</p>
                        <?php else: ?>
                            <?php foreach($activeGrants as $grant): ?>
                                <div class="d-flex align-items-center justify-content-between mb-2 grant-row">
                                    <div class="form-check flex-grow-1 me-3">
                                        <input class="form-check-input border-secondary grant-checkbox" type="checkbox"
                                               name="grantIDs[]" value="<?= (int)$grant['grantID'] ?>"
                                               id="grant_<?= (int)$grant['grantID'] ?>"
                                               data-grant-id="<?= (int)$grant['grantID'] ?>">
                                        <label class="form-check-label" for="grant_<?= (int)$grant['grantID'] ?>">
                                            <strong><?= htmlspecialchars($grant['grantName']) ?></strong>
                                            <span class="text-muted ms-1">(Bal: $<?= number_format($grant['currentBalance'], 2) ?> | Exp: <?= date('M d, Y', strtotime($grant['expirationDate'])) ?>)</span>
                                        </label>
                                    </div>
                                    <div class="input-group input-group-sm grant-percent-group" style="max-width: 130px; display: none;">
                                        <input type="number" step="0.01" min="0.01" max="100"
                                               class="form-control grant-percent-input"
                                               name="billingPercentages[<?= (int)$grant['grantID'] ?>]"
                                               value="100"
                                               disabled>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 text-end" id="totalDisplay" style="display: none;">
                        <small class="text-muted">Total for selected grants: </small>
                        <strong id="totalValue" class="text-primary">0%</strong>
                    </div>
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
const userMappings = <?= $userMappings ?>;

function isLabManagerSelected() {
    const select = document.getElementById('userSelect');
    if (!select.value) return false;
    const opt = select.options[select.selectedIndex];
    return opt && opt.getAttribute('data-role') === 'lab_manager';
}

function updatePercentRowVisibility(row) {
    const checkbox = row.querySelector('.grant-checkbox');
    const group = row.querySelector('.grant-percent-group');
    const input = row.querySelector('.grant-percent-input');
    if (!checkbox || !group || !input) return;

    const labManager = isLabManagerSelected();

    if (labManager) {
        group.style.display = 'none';
        input.disabled = true;
        input.removeAttribute('required');
        return;
    }

    if (checkbox.checked) {
        group.style.display = 'flex';
        input.disabled = false;
        input.setAttribute('required', 'required');
    } else {
        group.style.display = 'none';
        input.disabled = true;
        input.removeAttribute('required');
    }
}

function recalcTotal() {
    const totalWrap = document.getElementById('totalDisplay');
    const totalValue = document.getElementById('totalValue');
    if (isLabManagerSelected()) {
        totalWrap.style.display = 'none';
        return;
    }

    let sum = 0;
    let anyChecked = false;
    document.querySelectorAll('.grant-row').forEach(row => {
        const cb = row.querySelector('.grant-checkbox');
        const input = row.querySelector('.grant-percent-input');
        if (cb.checked) {
            anyChecked = true;
            const v = parseFloat(input.value);
            if (!isNaN(v)) sum += v;
        }
    });

    totalWrap.style.display = anyChecked ? 'block' : 'none';
    totalValue.textContent = sum.toFixed(2).replace(/\.00$/, '') + '%';
    totalValue.classList.toggle('text-danger', Math.abs(sum - 100) > 0.01);
    totalValue.classList.toggle('text-success', Math.abs(sum - 100) <= 0.01);
}

function loadUserData() {
    const select = document.getElementById('userSelect');
    const userID = select.value;
    const labManager = isLabManagerSelected();
    const help = document.getElementById('billingHelp');

    help.textContent = labManager
        ? 'Lab managers are given access to the selected grants without a billing share.'
        : 'For researchers, set the billing percentage for each checked grant. The total must equal exactly 100%.';

    document.querySelectorAll('.grant-row').forEach(row => {
        const cb = row.querySelector('.grant-checkbox');
        const input = row.querySelector('.grant-percent-input');
        cb.checked = false;
        input.value = '100';
    });

    if (userID && userMappings[userID]) {
        const grants = userMappings[userID];
        grants.forEach(g => {
            const cb = document.getElementById('grant_' + g.grantID);
            if (cb) {
                cb.checked = true;
                const row = cb.closest('.grant-row');
                const input = row.querySelector('.grant-percent-input');
                if (!labManager) {
                    input.value = parseFloat(g.billingPercentage);
                }
            }
        });
    }

    document.querySelectorAll('.grant-row').forEach(updatePercentRowVisibility);
    recalcTotal();
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.grant-row').forEach(row => {
        const cb = row.querySelector('.grant-checkbox');
        const input = row.querySelector('.grant-percent-input');
        cb.addEventListener('change', function () {
            updatePercentRowVisibility(row);
            recalcTotal();
        });
        input.addEventListener('input', recalcTotal);
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
