<?php
$pageTitle = "My Bookings"; 
require_once __DIR__ . '/../../includes/header.php'; 
/** @var array $bookings */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">My Booked Sessions</h2>
            <p class="text-muted small">Manage your upcoming research reservations</p>
        </div>
        <a href="/LabSync-System/equipment" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> New Booking
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">ID</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Equipment</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Date</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Time Slot</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Status</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-calendar-x mb-3 text-muted" style="font-size: 2.5rem;"></i>
                                        <p class="text-muted mb-0">You don't have any upcoming bookings.</p>
                                        <small><a href="/LabSync-System/equipment">Browse equipment to get started.</a></small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="id-badge">#<?= htmlspecialchars($booking['bookingID']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($booking['equipmentName']) ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar3 me-2 text-primary small"></i>
                                            <?= date('D, M d, Y', strtotime($booking['bookingDate'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock me-2 text-primary small"></i>
                                            <span>
                                                <?= date('h:i A', strtotime($booking['startTime'])) ?> - 
                                                <?= date('h:i A', strtotime($booking['endTime'])) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small">
                                            Confirmed
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-outline-danger btn-sm border-0" title="Cancel Booking">
                                            <i class="bi bi-trash3"></i>
                                        </button>
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