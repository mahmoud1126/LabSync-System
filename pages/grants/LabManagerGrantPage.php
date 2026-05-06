<?php 
/** @var array $activeGrants */
/** @var array $grants */
require_once __DIR__ . '/../../includes/header.php'; 
?>

<?php if (isset($_SESSION['reallocation_success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_SESSION['reallocation_success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['reallocation_success']); // Clear it so it only shows once ?>
<?php endif; ?>

<?php if (isset($_SESSION['reallocation_error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $_SESSION['reallocation_error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['reallocation_error']); ?>
<?php endif; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>System-Wide Grant Management</h2>
        <span class="badge bg-primary">Lab Manager Access</span>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Grant Fund Reallocation</h5>
        </div>
        <div class="card-body">
            <form action="/LabSync-System/grants/reallocate" method="POST" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Source Grant (Deduct From)</label>
                    <select name="sourceGrantID" class="form-select" required>
                        <option value="">Select Grant...</option>
                        <?php if (isset($activeGrants)): ?>
                            <?php foreach ($activeGrants as $grant): ?>
                                <option value="<?= $grant['grantID'] ?>">
                                    <?= htmlspecialchars($grant['grantName']) ?> (<?= number_format($grant['currentBalance'], 2) ?> EGP)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Destination Grant (Add To)</label>
                    <select name="destGrantID" class="form-select" required>
                        <option value="">Select Grant...</option>
                        <?php if (isset($activeGrants)): ?>
                            <?php foreach ($activeGrants as $grant): ?>
                                <option value="<?= $grant['grantID'] ?>">
                                    <?= htmlspecialchars($grant['grantName']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold">Amount (EGP)</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="1" placeholder="0.00" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Reallocate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">All System Grants</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Grant Name</th>
                        <th>PI</th>
                        <th>Total Budget</th>
                        <th>Current Balance</th>
                        <th>Status</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grants)): ?>
                        <?php foreach ($grants as $grant): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($grant['grantName']) ?></td>
                                <td><?= htmlspecialchars($grant['piName'] ?? 'N/A') ?></td>
                                <td><?= number_format($grant['totalBudget'], 2) ?> EGP</td>
                                <td>
                                    <span class="fw-bold <?= $grant['currentBalance'] < ($grant['totalBudget'] * 0.1) ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format($grant['currentBalance'], 2) ?> EGP
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill 
                                        <?= $grant['grantStatus'] === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                        <?= ucfirst($grant['grantStatus']) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($grant['expirationDate'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No grants found in system.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>