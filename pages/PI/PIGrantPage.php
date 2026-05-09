<?php 
require_once __DIR__ . '/../../includes/header.php'; 

// Silence VS Code Intelephense warnings
$grants = $grants ?? [];
$users = $users ?? [];
$pageTitle = $pageTitle ?? 'Grant Management';
$flash = $flash ?? null;
?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bank text-primary me-2"></i> <?= htmlspecialchars($pageTitle) ?></h2>
        <div>
            <a href="/LabSync-System/grants/manage" class="btn btn-outline-warning text-dark fw-medium border-0 me-2">
                <i class="bi bi-pencil-square me-1"></i> Manage Assignment
            </a>
            <a href="/LabSync-System/grants/assign" class="btn btn-outline-primary border-0 me-2">
                <i class="bi bi-person-plus me-1"></i> Assign Grants
            </a>
            <a href="/LabSync-System/grants/add" class="btn btn-outline-primary border-0">
                <i class="bi bi-plus-circle me-1"></i> Add Grant
            </a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show shadow-sm">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark"><i class="bi bi-list-ul me-2"></i> All System Grants</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Grant Name</th>
                            <th>PI Name</th>
                            <th>Total Budget</th>
                            <th>Current Balance</th>
                            <th>Status</th>
                            <th>Expiry Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($grants)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No grants found in the system.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($grants as $grant): ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-light text-dark border">#<?= (int)$grant['grantID'] ?></span></td>
                                    <td class="fw-semibold text-primary"><?= htmlspecialchars($grant['grantName']) ?></td>
                                    <td><?= htmlspecialchars($grant['piName'] ?? 'N/A') ?></td>
                                    <td>$<?= number_format($grant['totalBudget'], 2) ?></td>
                                    <td class="fw-medium text-dark">$<?= number_format($grant['currentBalance'], 2) ?></td>
                                    <td>
                                        <?php 
                                            $displayStatus = strtolower($grant['grantStatus']);
                                            
                                            $expDate = date('Y-m-d', strtotime($grant['expirationDate']));
                                            $today = date('Y-m-d');

                                            if ($expDate <= $today) {
                                                $displayStatus = 'expired';
                                            }

                                            $textClass = 'fw-bold';
                                            $inlineStyle = '';
                                            
                                            if ($displayStatus === 'active') {
                                                $textClass .= ' text-success'; 
                                            } elseif ($displayStatus === 'inactive') {
                                                $textClass .= ' text-danger'; 
                                            } elseif ($displayStatus === 'expired') {
                                                $inlineStyle = 'color: #fd7e14;'; 
                                            } elseif ($displayStatus === 'depleted') {
                                                $inlineStyle = 'color: #6f42c1;'; 
                                            } else {
                                                $textClass .= ' text-secondary';
                                            }
                                        ?>
                                        <span class="<?= $textClass ?>" <?= $inlineStyle ? 'style="'.$inlineStyle.'"' : '' ?>>
                                            <?= ucfirst(htmlspecialchars($displayStatus)) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($grant['expirationDate']))) ?></td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-danger rounded-pill fw-medium shadow-sm border-0" 
                                                style="font-size: 0.8rem; padding: 0.2rem 0.7rem;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#removeGrantModal" 
                                                data-id="<?= (int)$grant['grantID'] ?>">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark"><i class="bi bi-people me-2"></i> User Access Overview</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User ID</th>
                            <th>User Name</th>
                            <th>Role</th>
                            <th>Assigned Grants</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?= (int)$user['userID'] ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($user['userName']) ?></td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucwords($user['userType']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(empty($user['assignedGrants'])): ?>
                                            <span class="text-muted fst-italic">No grants assigned</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($user['assignedGrants']) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeGrantModal" tabindex="-1" aria-labelledby="removeGrantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-danger fw-bold" id="removeGrantModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Remove Grant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/LabSync-System/grants/delete" method="POST">
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to completely remove this grant? This action cannot be undone.</p>
                    <input type="hidden" name="grantID" id="modalGrantID" value="">
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Removal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var removeModal = document.getElementById('removeGrantModal');
    if(removeModal) {
        removeModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var grantId = button.getAttribute('data-id');
            var inputId = removeModal.querySelector('#modalGrantID');
            inputId.value = grantId;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>   