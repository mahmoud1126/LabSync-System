<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-check text-primary me-2"></i> Equipment Bookings</h2>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show shadow-sm">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['user']['userType'] === 'lab_manager'): ?>
        <div class="card shadow-sm border-0 mb-5 border-start border-warning border-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-dark"><i class="bi bi-hourglass-split text-warning me-2"></i> Phase 1: Pending Requests</h5>
                <small class="text-muted">Review availability and confirm to calculate costs and send to PI.</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Req ID</th>
                                <th>Researcher</th>
                                <th>Equipment</th>
                                <th>Requested Time</th>
                                <th class="text-center pe-4">Phase 1 Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($waitlistedBookings)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>
                                        No pending bookings require your confirmation.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($waitlistedBookings as $wb): ?>
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-light text-dark border">#<?= (int)$wb['bookingID'] ?></span></td>
                                        <td class="fw-medium text-dark"><?= htmlspecialchars($wb['userName'] ?? 'Unknown User') ?></td>
                                        <td>
                                            <i class="bi bi-pc-display-horizontal me-1 text-secondary"></i> 
                                            <?= htmlspecialchars($wb['equipmentName'] ?? 'Unknown Equipment') ?>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div><strong>Date:</strong> <?= date('M d, Y', strtotime($wb['startTime'])) ?></div>
                                                <div class="text-muted"><?= date('H:i', strtotime($wb['startTime'])) ?> - <?= date('H:i', strtotime($wb['endTime'])) ?></div>
                                            </div>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="/LabSync-System/booking/confirm/<?= (int)$wb['bookingID'] ?>" method="POST" class="m-0">
                                                    <button type="submit" class="btn btn-primary btn-sm fw-medium shadow-sm rounded-pill px-3">
                                                        <i class="bi bi-check2-circle me-1"></i> Confirm
                                                    </button>
                                                </form>
                                                <form action="/LabSync-System/booking/cancel/<?= (int)$wb['bookingID'] ?>" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm fw-medium shadow-sm rounded-pill px-3">
                                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark">
                <i class="bi bi-list-ul text-secondary me-2"></i> 
                <?= $_SESSION['user']['userType'] === 'lab_manager' ? 'All Active & Future Bookings' : 'My Schedule' ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Booking ID</th>
                            <th>Equipment</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <?php if ($_SESSION['user']['userType'] !== 'lab_manager'): ?>
                                <th class="text-center pe-4">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="<?= $_SESSION['user']['userType'] !== 'lab_manager' ? '5' : '4' ?>" class="text-center py-5 text-muted">
                                    No scheduled bookings found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="ps-4">#<?= (int)$booking['bookingID'] ?></td>
                                    <td class="fw-medium text-dark"><?= htmlspecialchars($booking['equipmentName'] ?? 'Unknown Equipment') ?></td>
                                    <td>
                                        <div class="small">
                                            <?= date('M d, Y', strtotime($booking['startTime'])) ?><br>
                                            <span class="text-muted"><?= date('H:i', strtotime($booking['startTime'])) ?> - <?= date('H:i', strtotime($booking['endTime'])) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            // FIXED: Status Colors updated to handle 'pending' correctly
                                            $statusClass = 'bg-secondary';
                                            if ($booking['bookingStatus'] === 'pending') $statusClass = 'bg-warning text-dark';
                                            if ($booking['bookingStatus'] === 'waitlisted') $statusClass = 'bg-warning text-dark';
                                            if ($booking['bookingStatus'] === 'confirmed') $statusClass = 'bg-info text-dark';
                                            if ($booking['bookingStatus'] === 'completed') $statusClass = 'bg-success';
                                            if ($booking['bookingStatus'] === 'cancelled' || $booking['bookingStatus'] === 'rejected') $statusClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($booking['bookingStatus'])) ?></span>
                                    </td>
                                    
                                    <?php if ($_SESSION['user']['userType'] !== 'lab_manager'): ?>
                                    <td class="text-center pe-4">
                                        <?php if (in_array($booking['bookingStatus'], ['pending', 'waitlisted', 'confirmed'])): ?>
                                            <form action="/LabSync-System/booking/cancel/<?= (int)$booking['bookingID'] ?>" method="POST" class="m-0" onsubmit="return confirm('Cancel this booking?');">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary btn-sm rounded-pill disabled" disabled>Locked</button>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
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