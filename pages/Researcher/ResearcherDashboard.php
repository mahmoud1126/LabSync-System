<?php 
/** @var string $userName */
/** @var string $currentRole */
/** @var float $totalSpent */
/** @var array $assignedGrants */
/** @var array $recentBookings */

$pageTitle = "Researcher Dashboard"; 
require_once __DIR__ . '/../../includes/header.php'; 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="mb-0">Welcome back, <?= htmlspecialchars($userName ?? 'Researcher') ?>!</h2>
        <p class="text-muted">Role: <?= htmlspecialchars($currentRole ?? 'N/A') ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="/LabSync-System/equipment" class="btn btn-primary shadow-sm">
            <i class="bi bi-calendar-plus me-1"></i> New Booking
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-4">
                <h6 class="text-muted text-uppercase small fw-bold"><i class="bi bi-currency-dollar text-success me-1"></i> Total Grant Spending</h6>
                <h2 class="mb-0 text-dark">$<?= number_format($totalSpent ?? 0, 2) ?></h2>
                <p class="small text-muted mt-1">Approved deductions only</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-4">
                <h6 class="text-muted text-uppercase small fw-bold"><i class="bi bi-briefcase text-primary me-1"></i> Active Grants</h6>
                <h2 class="mb-0 text-primary"><?= count($assignedGrants ?? []) ?></h2>
                <p class="small text-muted mt-1">Available for billing</p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>My Recent Bookings</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentBookings)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-3 text-light"></i>
                <p>No recent activity found. Click 'New Booking' to get started!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Equipment</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Phase Status</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($booking['equipmentName'] ?? 'Unknown') ?></td>
                                <td>
                                    <div class="small">
                                        <?= date('M d, Y', strtotime($booking['startTime'] ?? 'now')) ?><br>
                                        <span class="text-muted"><?= date('H:i', strtotime($booking['startTime'] ?? 'now')) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <?= date('M d, Y', strtotime($booking['endTime'] ?? 'now')) ?><br>
                                        <span class="text-muted"><?= date('H:i', strtotime($booking['endTime'] ?? 'now')) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $status = strtolower($booking['bookingStatus'] ?? 'pending');
                                        
                                        // Dynamic mapping for 2-Phase pipeline
                                        $badgeConfig = match($status) {
                                            'pending' => ['bg-warning text-dark', 'bi-hourglass-split'],
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
                                </td>
                                <td class="text-center pe-4">
                                    <a href="/LabSync-System/booking/view?id=<?= $booking['bookingID'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-medium">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>