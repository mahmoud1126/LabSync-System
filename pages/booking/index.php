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
                            <th class="py-3 text-uppercase small fw-bold text-muted">Start</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">End</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Status</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <p class="text-muted mb-0">No bookings found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="ps-4">#<?= $booking['bookingID'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($booking['equipmentName']) ?></td>
                                    <td><?= date('M d, h:i A', strtotime($booking['startTime'])) ?></td>
                                    <td><?= date('M d, h:i A', strtotime($booking['endTime'])) ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info rounded-pill px-3">
                                            <?= ucfirst($booking['bookingStatus']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm border-0" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal" 
                                                data-id="<?= $booking['bookingID'] ?>">
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

<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this booking? This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Booking</button>
                <form id="cancelForm" action="" method="POST">
                    <button type="submit" class="btn btn-danger px-4">Confirm Cancellation</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // JS to update the form action based on which trash icon was clicked
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const bookingID = button.getAttribute('data-id');
            const form = document.getElementById('cancelForm');
            form.action = '/LabSync-System/booking/cancel/' + bookingID;
        });
    }
</script>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>