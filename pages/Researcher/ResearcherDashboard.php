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
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Total Grant Spending</h6>
                <h2 class="mb-0 text-danger">$<?= number_format($totalSpent ?? 0, 2) ?></h2>
                <p class="small text-muted mt-1">Approved deductions only</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Active Grants</h6>
                <h2 class="mb-0 text-primary"><?= count($assignedGrants ?? []) ?></h2>
                <p class="small text-muted mt-1">Available for billing</p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>My Recent Bookings</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentBookings)): ?>
            <div class="p-5 text-center text-muted">
                <p>No recent activity found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Equipment</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($booking['equipmentName'] ?? 'Unknown') ?></td>
                                <td><?= date('M d, H:i', strtotime($booking['startTime'] ?? 'now')) ?></td>
                                <td><?= date('M d, H:i', strtotime($booking['endTime'] ?? 'now')) ?></td>
                                <td>
                                    <?php 
                                        $status = $booking['bookingStatus'] ?? 'pending';
                                        $statusClass = match($status) {
                                            'confirmed' => 'success',
                                            'pending' => 'warning',
                                            'cancelled' => 'secondary',
                                            'completed' => 'info',
                                            default => 'dark'
                                        };
                                    ?>
                                    <span class="badge rounded-pill bg-<?= $statusClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/LabSync-System/bookings/view?id=<?= $booking['bookingID'] ?? 0 ?>" class="btn btn-sm btn-outline-primary">Details</a>
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