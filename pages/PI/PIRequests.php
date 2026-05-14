<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check text-primary me-2"></i> <?= htmlspecialchars($pageTitle ?? 'Financial Requests') ?></h2>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show shadow-sm">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark"><i class="bi bi-hourglass-split text-warning me-2"></i> Pending Booking Deductions (Phase 2)</h5>
            <small class="text-muted">Bookings confirmed by Lab Managers requiring final grant partitioning.</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Booking ID</th>
                            <th>Researcher</th>
                            <th>Equipment</th>
                            <th>Date / Time</th>
                            <th>Total Cost</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingBookings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No pending booking requests.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingBookings as $booking): ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-light text-dark border">#<?= (int)$booking['bookingID'] ?></span></td>
                                    <td class="fw-medium text-dark"><?= htmlspecialchars($booking['userName'] ?? 'Unknown User') ?></td>
                                    <td><i class="bi bi-pc-display-horizontal me-1 text-secondary"></i> <?= htmlspecialchars($booking['equipmentName'] ?? 'Unknown Equipment') ?></td>
                                    <td>
                                        <div class="small">
                                            <div><strong>Date:</strong> <?= date('M d, Y', strtotime($booking['startTime'])) ?></div>
                                            <div class="text-muted"><?= date('H:i', strtotime($booking['startTime'])) ?> - <?= date('H:i', strtotime($booking['endTime'])) ?></div>
                                        </div>
                                    </td>
                                    <td><span class="text-primary fw-bold fs-6">$<?= number_format((float)$booking['totalCost'], 2) ?></span></td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <form action="/LabSync-System/PI/approveBooking" method="POST" class="m-0">
                                                <input type="hidden" name="bookingID" value="<?= (int)$booking['bookingID'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm fw-medium shadow-sm rounded-pill px-3">Approve</button>
                                            </form>
                                            <form action="/LabSync-System/PI/rejectBooking" method="POST" class="m-0" onsubmit="return confirm('Are you sure? This cancels the booking completely.');">
                                                <input type="hidden" name="bookingID" value="<?= (int)$booking['bookingID'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm fw-medium shadow-sm rounded-pill px-3">Reject</button>
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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark"><i class="bi bi-arrow-left-right text-info me-2"></i> Pending Fund Reallocations</h5>
            <small class="text-muted">Grant-to-Grant transfer requests submitted by Lab Managers.</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Trans ID</th>
                            <th>Requested By</th>
                            <th>Source Grant</th>
                            <th>Details</th>
                            <th>Amount</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingTransactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No pending reallocation requests.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingTransactions as $tx): ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-light text-dark border">#<?= (int)$tx['transactionID'] ?></span></td>
                                    <td class="fw-medium text-dark"><?= htmlspecialchars($tx['userName']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($tx['grantName']) ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($tx['description']) ?></td>
                                    <td><span class="text-info fw-bold fs-6">$<?= number_format((float)$tx['amount'], 2) ?></span></td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <form action="/LabSync-System/PI/approve" method="POST" class="m-0">
                                                <input type="hidden" name="transactionID" value="<?= (int)$tx['transactionID'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm fw-medium shadow-sm rounded-pill px-3">Approve</button>
                                            </form>
                                            <form action="/LabSync-System/PI/rejectTransactionList" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to reject this reallocation?');">
                                                <input type="hidden" name="transactionID" value="<?= (int)$tx['transactionID'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm fw-medium shadow-sm rounded-pill px-3">Reject</button>
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

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>