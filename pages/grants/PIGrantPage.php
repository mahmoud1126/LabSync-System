<?php
/** @var array $pendingTransactions */
/** @var array $managedGrants */
/** @var array $recentActivity */
?>

<div class="container py-4">
    <!-- Rest of your HTML code -->


<div class="container py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Financial Overview</h2>
            <p class="text-muted">Manage grant transactions and researcher spending.</p>
        </div>
        <span class="badge bg-primary px-3 py-2">Faculty PI Portal</span>
    </div>

    <!-- Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Pending Approvals</h6>
                    <h3 class="mb-0 text-warning"><?= count($pendingTransactions) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Managed Grants</h6>
                    <h3 class="mb-0 text-info"><?= count($managedGrants) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Transactions Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Transactions Awaiting Approval</h5>
                    <i class="text-muted small">Action required for fund release</i>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Researcher</th>
                                    <th>Grant Used</th>
                                    <th>Amount</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingTransactions)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            No pending transactions found for your grants.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingTransactions as $tx): ?>
                                        <tr>
                                            <td>#<?= $tx['transactionID'] ?></td>
                                            <td><strong><?= htmlspecialchars($tx['userName']) ?></strong></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($tx['grantName']) ?></span></td>
                                            <td class="text-primary font-weight-bold">$<?= number_format($tx['amount'], 2) ?></td>
                                            <td class="text-end">
                                                <form action="/LabSync-System/grant/handleAction" method="POST" class="d-inline">
                                                    <input type="hidden" name="transactionID" value="<?= $tx['transactionID'] ?>">
                                                    <button name="action" value="approve" class="btn btn-sm btn-success px-3 me-1">Approve</button>
                                                    <button name="action" value="reject" class="btn btn-sm btn-outline-danger px-3">Reject</button>
                                                </form>
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

        <!-- Recent Activity Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($recentActivity)): ?>
                            <li class="list-group-item text-center py-4 text-muted">No recent history.</li>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $activity): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="small fw-bold text-dark"><?= htmlspecialchars($activity['userName']) ?></div>
                                            <div class="text-muted extra-small" style="font-size: 0.75rem;">
                                                <?= date('M d, H:i', strtotime($activity['createdAt'])) ?>
                                            </div>
                                        </div>
                                        <span class="badge <?= $activity['approvalStatus'] === 'approved' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' ?> rounded-pill">
                                            <?= ucfirst($activity['approvalStatus']) ?>
                                        </span>
                                    </div>
                                    <div class="mt-1 small text-secondary">
                                        $<?= number_format($activity['amount'], 2) ?> &bull; <?= htmlspecialchars($activity['grantName']) ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: #e6f4ea; }
    .bg-danger-soft { background-color: #fce8e6; }
    .extra-small { font-size: 0.75rem; }
</style>