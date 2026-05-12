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
                    <label class="form-label fw-semibold text-dark">2. Select Grants &amp; Set Billing % per Grant</label>
                    <p class="text-muted small mb-2" id="billingHelp">
                        For researchers, set the billing percentage for each selected grant. The user's total across all assigned grants (including any existing ones) must equal exactly 100%.
                    </p>
                    <div class="border rounded p-3 bg-light" style="max-height: 320px; overflow-y: auto;">
                        <?php if(empty($activeGrants)): ?>
                            <p class="text-muted mb-0">No active grants available to assign.</p>
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
                    <button type="submit" class="btn btn-info text-white px-4 py-2">
                        <i class="bi bi-link-45deg me-1"></i> Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
        // Lab managers don't have a billing share — hide and disable inputs.
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

function toggleBillingSection() {
    const labManager = isLabManagerSelected();
    const help = document.getElementById('billingHelp');
    help.textContent = labManager
        ? 'Lab managers are given access to the selected grants without a billing share.'
        : "For researchers, set the billing percentage for each selected grant. The user's total across all assigned grants (including any existing ones) must equal exactly 100%.";

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
