<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-cash-stack text-primary"></i> Grants Overview</h2>
                <p class="text-muted mb-0">Read-only view of all research grants</p>
            </div>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> All Grants</span>
                <span class="badge bg-secondary"><?= count($grants) ?> total</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($grants)): ?>
                    <div class="empty-state">
                        <i class="bi bi-cash-stack"></i>
                        <p>No grants found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Grant Name</th>
                                    <th>PI</th>
                                    <th>Total Budget</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Expires</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grants as $grant): ?>
                                    <tr onclick="window.location='/LabSync-System/admin/grants/<?= $grant['grantID'] ?>'">
                                        <td><span class="id-badge">#<?= $grant['grantID'] ?></span></td>
                                        <td><strong><?= htmlspecialchars($grant['grantName']) ?></strong></td>
                                        <td><?= htmlspecialchars($grant['piName']) ?></td>
                                        <td>$<?= number_format((float) $grant['totalBudget'], 2) ?></td>
                                        <td>
                                            <?php
                                            $balance = (float) $grant['currentBalance'];
                                            $total = (float) $grant['totalBudget'];
                                            $isLow = $total > 0 && ($balance / $total) < 0.2;
                                            $color = $isLow ? '#D32F2F' : 'inherit';
                                            ?>
                                            <strong style="color: <?= $color ?>;">
                                                $<?= number_format($balance, 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match ($grant['grantStatus']) {
                                                'active' => 'severity-low',
                                                'expired' => 'severity-high',
                                                'depleted' => 'severity-critical',
                                                default => 'severity-medium'
                                            };
                                            ?>
                                            <span class="severity-badge <?= $statusClass ?>">
                                                <?= htmlspecialchars($grant['grantStatus']) ?>
                                            </span>
                                        </td>
                                        <td><small><?= date('Y-m-d', strtotime($grant['expirationDate'])) ?></small></td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <a href="/LabSync-System/admin/grants/<?= $grant['grantID'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>