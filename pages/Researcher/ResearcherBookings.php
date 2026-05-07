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
                                        <button type="button" 
                                                class="btn btn-link text-danger text-decoration-none btn-sm p-0 border-0 fw-bold" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal" 
                                                data-id="<?= $booking['bookingID'] ?>">
                                            Remove
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
                <h5 class="modal-title fw-bold text-dark">Confirm Removal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-muted">
                Are you sure you want to remove this booking? This action will set the status to <span class="text-danger fw-bold">Cancelled</span> and cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep It</button>
                <form id="cancelForm" action="" method="POST">
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Confirm Removal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cancelModal = document.getElementById('cancelModal');
        if (cancelModal) {
            cancelModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const bookingID = button.getAttribute('data-id');
                const form = document.getElementById('cancelForm');
                form.action = '/LabSync-System/booking/cancel/' + bookingID;
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>