<?php
/** @var array $assignedGrants */
/** @var array $myTransactions */
/** @var float $totalSpent */
?>

<div class="container py-4">
    <!-- Header with Total Spending -->
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="h3 mb-1">Financial Overview</h2>
            <p class="text-muted">Managed grants and transaction history.</p>
        </div>
        <div class="col-auto">
            <div class="bg-light p-3 rounded-3 border">
                <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem;">Total Lifetime Spending</small>
                <span class="h4 mb-0 text-primary">$<?= number_format($totalSpent, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Active Grants Section -->
    <h5 class="mb-3 text-secondary">Assigned Grants</h5>
    <div class="row g-4 mb-5">
        <?php if (empty($assignedGrants)): ?>
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm">
                    No active grants found. You may need to be assigned to a grant by a PI to start sessions.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($assignedGrants as $grant): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm border-top border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($grant['grantName']) ?></h6>
                                <span class="badge bg-soft-success text-success">Active</span>
                            </div>
                            
                            <h3 class="my-3">$<?= number_format($grant['currentBalance'], 2) ?></h3>
                            
                            <div class="mt-4">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Billing Weight</span>
                                    <span class="fw-bold"><?= (float)$grant['billingPercentage'] ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?= $grant['billingPercentage'] ?>%"></div>
                                </div>
                                <p class="mt-2 mb-0 text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    Expires: <?= date('M d, Y', strtotime($grant['expirationDate'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Transaction Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Recent Transactions</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Grant Source</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myTransactions)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No transactions recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myTransactions as $tx): ?>
                            <tr>
                                <td class="ps-4 small"><?= date('M d, Y H:i', strtotime($tx['createdAt'])) ?></td>
                                <td><span class="badge border text-dark fw-normal"><?= htmlspecialchars($tx['grantName']) ?></span></td>
                                <td class="small text-secondary"><?= htmlspecialchars($tx['description']) ?></td>
                                <td class="fw-bold <?= $tx['transactionType'] === 'deduction' ? 'text-danger' : 'text-success' ?>">
                                    <?= $tx['transactionType'] === 'deduction' ? '-' : '+' ?>$<?= number_format($tx['amount'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $badgeClass = match($tx['approvalStatus']) {
                                            'approved' => 'bg-success',
                                            'pending'  => 'bg-warning text-dark',
                                            'rejected' => 'bg-danger',
                                            default    => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3"><?= ucfirst($tx['approvalStatus']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-soft-success { background-color: #e8f5e9; }
    .card { transition: transform 0.15s ease-in-out; }
    .card:hover { transform: translateY(-2px); }
</style>