<?php 
/** @var array $booking */
require_once __DIR__ . '/../../includes/header.php'; 

// FIXED: Now pulling totalCost directly from the database!
$equipmentName = $booking['equipmentName'] ?? $booking['name'] ?? 'Equipment Name Not Found';
$cost = $booking['totalCost'] ?? $booking['cost'] ?? $booking['estimatedCost'] ?? 0;
$status = strtolower($booking['bookingStatus'] ?? $booking['status'] ?? 'pending');
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="/LabSync-System/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Booking Details</li>
                </ol>
            </nav>
            <h2 class="fw-bold">Booking #<?= $booking['bookingID'] ?></h2>
        </div>
        <a href="/LabSync-System/dashboard" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i> Session Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Equipment</div>
                        <div class="col-sm-8 fw-bold text-dark"><?= htmlspecialchars($equipmentName) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Time Slot</div>
                        <div class="col-sm-8">
                            <?= date('M d, Y', strtotime($booking['startTime'])) ?><br>
                            <span class="text-primary fw-bold">
                                <?= date('H:i', strtotime($booking['startTime'])) ?> - <?= date('H:i', strtotime($booking['endTime'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-sm-4 text-muted">Current Status</div>
                        <div class="col-sm-8">
                            <?php 
                                // Added the exact same badge logic from the Dashboard so it matches perfectly
                                $badgeConfig = match($status) {
                                    'pending' => ['bg-warning text-dark', 'bi-hourglass-split'],
                                    'waitlisted' => ['bg-warning text-dark', 'bi-hourglass-split'],
                                    'confirmed' => ['bg-info text-dark', 'bi-check-circle'],
                                    'approved' => ['bg-success', 'bi-check-all'],
                                    'rejected' => ['bg-danger', 'bi-x-circle'],
                                    'cancelled' => ['bg-danger', 'bi-slash-circle'],
                                    'completed' => ['bg-secondary', 'bi-flag'],
                                    default => ['bg-dark', 'bi-circle']
                                };
                            ?>
                            <span class="badge rounded-pill <?= $badgeConfig[0] ?> px-3 py-2 shadow-sm">
                                <i class="bi <?= $badgeConfig[1] ?> me-1"></i> <?= ucfirst($status) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm <?= ($cost > 0) ? 'bg-success' : 'bg-primary' ?> text-white h-100">
                <div class="card-body py-5 text-center d-flex flex-column justify-content-center">
                    <h6 class="text-white-50 text-uppercase small fw-bold">
                        <?= ($cost > 0) ? '<i class="bi bi-check-circle me-1"></i> Final Calculated Cost' : '<i class="bi bi-calculator me-1"></i> Estimated Cost' ?>
                    </h6>
                    <h2 class="mb-0 fw-bold display-5 my-3"><?= number_format((float)$cost, 2) ?> EGP</h2>
                    <hr class="text-white-50 w-75 mx-auto">
                    <p class="small mb-0 text-white-75">
                        <?php if ($cost > 0): ?>
                            Cost confirmed by operations.
                        <?php else: ?>
                            Note: Final calculation occurs after lab manager confirmation.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>