<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2 text-primary me-2"></i> <?= htmlspecialchars($pageTitle ?? 'PI Dashboard') ?></h2>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-white border border-primary border-2 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                    <h5 class="card-title text-uppercase fw-bold text-primary opacity-75"><i class="bi bi-people-fill me-2"></i> Total System Users</h5>
                    <p class="card-text display-3 fw-bold text-primary mb-0"><?= (int)($totalUsers ?? 0) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-white border border-primary border-2 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-4" style="color: #084298;">
                    <h5 class="card-title text-uppercase fw-bold opacity-75"><i class="bi bi-bank me-2"></i> Total System Grants</h5>
                    <p class="card-text display-3 fw-bold mb-0"><?= (int)($totalGrants ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark"><i class="bi bi-card-list me-2"></i> Contributing Grants Overview</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Grant ID</th>
                            <th>Grant Name</th>
                            <th>PI Name</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($managedGrants)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No grants currently exist in the system.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($managedGrants as $grant): ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-light text-dark border">#<?= (int)$grant['grantID'] ?></span></td>
                                    <td class="fw-semibold text-primary"><?= htmlspecialchars($grant['grantName']) ?></td>
                                    <td><?= htmlspecialchars($grant['piName'] ?? 'Unknown PI') ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($grant['grantStatus'] === 'active') $badgeClass = 'bg-success';
                                            if ($grant['grantStatus'] === 'depleted') $badgeClass = 'bg-warning text-dark';
                                            if ($grant['grantStatus'] === 'expired') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?> px-2 py-1">
                                            <?= ucfirst(htmlspecialchars($grant['grantStatus'])) ?>
                                        </span>
                                    </td>
                                    <td class="fw-medium text-dark">$<?= number_format($grant['currentBalance'], 2) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="/grants" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-info-circle me-1"></i> Details
                                        </a>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>