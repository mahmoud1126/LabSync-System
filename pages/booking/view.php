<?php 
/** @var array $booking */
require_once __DIR__ . '/../../includes/header.php'; 

// Fallback logic in case the join was missed in the controller
$equipmentName = $booking['equipmentName'] ?? $booking['name'] ?? 'Equipment Name Not Found';
$cost = $booking['cost'] ?? $booking['estimatedCost'] ?? 0;
$status = $booking['status'] ?? $booking['bookingStatus'] ?? 'pending';
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
                    <h5 class="mb-0">Session Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Equipment</div>
                        <div class="col-sm-8 fw-bold text-primary"><?= htmlspecialchars($equipmentName) ?></div>
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
                            <span class="badge rounded-pill 
                                <?= $status === 'pending' ? 'bg-warning text-dark' : 
                                    ($status === 'approved' ? 'bg-success' : 'bg-danger') ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body py-4">
                    <h6 class="text-white-50 text-uppercase small fw-bold">Estimated Cost</h6>
                    <h2 class="mb-0"><?= number_format($cost, 2) ?> EGP</h2>
                    <hr class="text-white-50">
                    <p class="small mb-0">Note: Final deduction occurs after lab manager approval.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>