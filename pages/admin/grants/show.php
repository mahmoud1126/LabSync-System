<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-cash-stack text-primary"></i>
                    <?= htmlspecialchars($grant['grantName']) ?>
                </h2>
                <p class="text-muted mb-0">
                    Grant ID: <span class="id-badge">#<?= $grant['grantID'] ?></span>
                </p>
            </div>
            <a href="/LabSync-System/admin/grants" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Grants
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>


        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Grant Summary
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Budget</small>
                        <h4 class="fw-bold mb-0">$<?= number_format((float) $grant['totalBudget'], 2) ?></h4>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">Current Balance</small>
                        <?php
                        $balance = (float) $grant['currentBalance'];
                        $total = (float) $grant['totalBudget'];
                        $isLow = $total > 0 && ($balance / $total) < 0.2;
                        $color = $isLow ? '#D32F2F' : 'inherit';
                        ?>
                        <h4 class="fw-bold mb-0" style="color: <?= $color ?>;">
                            $<?= number_format($balance, 2) ?>
                        </h4>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
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
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">Expires</small>
                        <h6 class="fw-semibold mb-0">
                            <?= date('Y-m-d', strtotime($grant['expirationDate'])) ?>
                        </h6>
                    </div>

                </div>


                <?php if ($total > 0): ?>
                    <?php $usedPct = (($total - $balance) / $total) * 100; ?>
                    <div class="mt-4">
                        <small class="text-muted">Funds Used: <?= number_format($usedPct, 1) ?>%</small>
                        <div class="progress mt-1" style="height: 10px;">
                            <div class="progress-bar <?= $isLow ? 'bg-danger' : 'bg-success' ?>" role="progressbar"
                                style="width: <?= $usedPct ?>%;"></div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Authorized Users</span>
                <span class="badge bg-secondary"><?= count($users) ?> users</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="bi bi-person-x"></i>
                        <p>No users have access to this grant yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Billing %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><span class="id-badge">#<?= $u['userID'] ?></span></td>
                                        <td><strong><?= htmlspecialchars($u['userName']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= ucwords(str_replace('_', ' ', $u['userType'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $uStatusClass = match ($u['userStatus']) {
                                                'active' => 'severity-low',
                                                'inactive' => 'severity-medium',
                                                'suspended' => 'severity-critical',
                                                default => 'severity-medium'
                                            };
                                            ?>
                                            <span class="severity-badge <?= $uStatusClass ?>">
                                                <?= htmlspecialchars($u['userStatus']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= number_format((float) $u['billingPercentage'], 2) ?>%</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Transaction History</span>
                <span class="badge bg-secondary"><?= count($transactions) ?> transactions</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <i class="bi bi-receipt"></i>
                        <p>No transactions recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Approval</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><span class="id-badge">#<?= $t['transactionID'] ?></span></td>
                                        <td>
                                            <small><?= ucwords(str_replace('_', ' ', $t['transactionType'])) ?></small>
                                        </td>
                                        <td><strong>$<?= number_format((float) $t['amount'], 2) ?></strong></td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($t['description'] ?? '', 0, 60)) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $approvalClass = match ($t['approvalStatus']) {
                                                'approved' => 'severity-low',
                                                'pending' => 'severity-medium',
                                                'rejected' => 'severity-critical',
                                                default => 'severity-medium'
                                            };
                                            ?>
                                            <span class="severity-badge <?= $approvalClass ?>">
                                                <?= htmlspecialchars($t['approvalStatus']) ?>
                                            </span>
                                        </td>
                                        <td><small><?= date('Y-m-d H:i', strtotime($t['createdAt'])) ?></small></td>
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