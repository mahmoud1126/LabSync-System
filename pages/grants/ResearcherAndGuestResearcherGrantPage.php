<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-wallet2 text-primary me-2"></i> <?= htmlspecialchars($pageTitle ?? 'My Assigned Grants') ?></h2>
    </div>

    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
            <strong>Funding Overview:</strong> This page displays the grants your Principal Investigator (PI) has authorized for your use. Deductions will be automatically partitioned based on your assigned billing percentages.
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark"><i class="bi bi-card-list text-success me-2"></i> Authorized Grants</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Grant Name</th>
                            <th>Status</th>
                            <th>Current Balance</th>
                            <th>My Coverage (%)</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grants)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                    You currently have no active grants assigned to you.<br>
                                    <small>Please contact your PI if you believe this is a mistake.</small>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grants as $grant): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">
                                        <?= htmlspecialchars($grant['grantName']) ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = strtolower($grant['grantStatus']);
                                            $badgeClass = 'bg-secondary';
                                            
                                            if ($status === 'active') $badgeClass = 'bg-success';
                                            elseif ($status === 'depleted') $badgeClass = 'bg-warning text-dark';
                                            elseif ($status === 'expired') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst(htmlspecialchars($status)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">
                                            $<?= number_format((float)$grant['currentBalance'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-medium me-2"><?= (float)$grant['billingPercentage'] ?>%</span>
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= (float)$grant['billingPercentage'] ?>%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $expiryDate = strtotime($grant['expirationDate']);
                                            $isExpiringSoon = ($expiryDate - time()) < (30 * 24 * 60 * 60) && $status === 'active';
                                        ?>
                                        <span class="<?= $isExpiringSoon ? 'text-danger fw-medium' : 'text-muted' ?>">
                                            <?= date('M d, Y', $expiryDate) ?>
                                            <?php if($isExpiringSoon): ?>
                                                <i class="bi bi-exclamation-circle ms-1" title="Expiring soon!"></i>
                                            <?php endif; ?>
                                        </span>
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