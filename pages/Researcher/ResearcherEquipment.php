<?php
$pageTitle = "Equipment Gallery"; 
require_once __DIR__ . '/../../includes/header.php'; 
/** @var array $equipments */
?>

<div class="container-fluid py-4">
    <!-- Success Alert Section -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Booking Confirmed!</strong> Your reservation has been successfully recorded. 
                    <a href="/LabSync-System/bookings" class="alert-link text-decoration-underline">View My Bookings</a>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-alert="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Laboratory Equipment</h2>
            <p class="text-muted small">View and book available research tools</p>
        </div>
    </div>

    <div class="row">
        <?php if (empty($equipments)): ?>
            <div class="col-12 text-center py-5">
                <div class="card shadow-sm border-0 py-5">
                    <i class="bi bi-tools mb-3 text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted">No equipment currently available in the system.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($equipments as $item): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
                            <span class="id-badge">#<?= htmlspecialchars($item['equipmentID'] ?? '0') ?></span>
                            <?php 
                                $status = strtolower($item['equipmentStatus'] ?? 'unavailable');
                                $badgeClass = ($status === 'available') ? 'bg-success' : 'bg-secondary';
                            ?>
                            <span class="badge <?= $badgeClass ?> rounded-pill px-3"><?= ucfirst($status) ?></span>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-2">
                                <?= htmlspecialchars($item['equipmentName'] ?? 'Equipment Item') ?>
                            </h5>
                            
                            <p class="card-text text-muted small mb-4">
                                <?= htmlspecialchars(substr($item['description'] ?? 'No description available.', 0, 100)) ?>...
                            </p>

                            <div class="mt-auto border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="small">
                                        <span class="text-muted">Requirement:</span> 
                                        <span class="fw-bold">Lvl <?= htmlspecialchars($item['clearanceLevel'] ?? $item['clearance_level'] ?? '0') ?></span>
                                    </div>
                                    <div class="text-primary fw-bold">
                                        $<?= number_format((float)($item['hourlyRateExternal'] ?? $item['hourly_rate'] ?? 0), 2) ?>/hr
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm flex-grow-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#infoModal<?= $item['equipmentID'] ?>">
                                        <i class="bi bi-info-circle me-1"></i> Details
                                    </button>

                                    <button class="btn btn-primary btn-sm flex-grow-1 shadow-sm" 
                                            <?= ($item['canShowBookButton'] ?? false) ? '' : 'disabled' ?>
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bookModal<?= $item['equipmentID'] ?>">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        <?= ($item['canShowBookButton'] ?? false) ? 'Book Now' : 'Locked' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Modal (Existing) -->
                <div class="modal fade" id="infoModal<?= $item['equipmentID'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 bg-light">
                                <h5 class="modal-title fw-bold">Equipment Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <h3 class="text-primary fw-bold mb-3"><?= htmlspecialchars($item['equipmentName']) ?></h3>
                                <p class="fw-bold small text-uppercase text-muted mb-2">Full Description</p>
                                <div class="description-box mb-4 shadow-sm bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($item['description'] ?? 'No description provided.')) ?>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Updated Booking Modal -->
                <div class="modal fade" id="bookModal<?= $item['equipmentID'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <!-- A. Form ID added here -->
                            <form id="bookingForm<?= $item['equipmentID'] ?>">
                                <div class="modal-header bg-primary text-white border-0">
                                    <h5 class="modal-title fw-bold">Reserve <?= htmlspecialchars($item['equipmentName']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <!-- B. Message box added here -->
                                    <div id="msg<?= $item['equipmentID'] ?>" class="alert d-none"></div>

                                    <input type="hidden" name="equipmentID" value="<?= $item['equipmentID'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Reservation Date</label>
                                        <input type="date" name="bookingDate" class="form-control" required min="<?= date('Y-m-d') ?>">
                                    </div>

                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">Start Time</label>
                                            <input type="time" name="startTime" class="form-control" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">End Time</label>
                                            <input type="time" name="endTime" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <!-- C. Button changed to trigger JavaScript -->
                                    <button type="button" onclick="sendBooking(<?= $item['equipmentID'] ?>)" class="btn btn-primary px-4 shadow-sm">Confirm Booking</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function sendBooking(id) {
    const form = document.getElementById('bookingForm' + id);
    const msgBox = document.getElementById('msg' + id);
    const formData = new FormData(form);

    // Basic time validation check
    if (formData.get('startTime') >= formData.get('endTime')) {
        msgBox.classList.remove('d-none');
        msgBox.classList.add('alert-danger');
        msgBox.textContent = "End time must be after start time.";
        return; 
    }

    fetch('booking/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        msgBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        msgBox.classList.add(data.success ? 'alert-success' : 'alert-danger');
        msgBox.textContent = data.message;

        if (data.success) {
            form.reset();
            setTimeout(() => { location.reload(); }, 2000);
        }
    })
    .catch(err => {
        msgBox.classList.remove('d-none');
        msgBox.classList.add('alert-danger');
        msgBox.textContent = "Something went wrong. Check if you are logged in.";
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>