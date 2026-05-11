<?php
$pageTitle = "Equipment Gallery"; 
require_once __DIR__ . '/../../includes/header.php'; 
/** @var array $equipments */
?>

<div class="container-fluid py-4">
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
                            <h5 class="card-title fw-bold text-dark mb-4">
                                <?= htmlspecialchars($item['equipmentName'] ?? 'Equipment Item') ?>
                            </h5>

                            <div class="mt-auto border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="small d-flex align-items-center">
                                        <span class="text-muted me-1">Requirement:</span> 
                                        <span class="fw-bold me-2">Lvl <?= (int) ($item['requiredClearanceLevel'] ?? 0) ?></span>
                                        
                                        <?php if (!empty($item['hasBriefing'])): ?>
                                            <span class="badge bg-warning text-dark border" title="Safety Briefing Required">
                                                <i class="bi bi-shield-exclamation"></i> Briefing
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-primary fw-bold">
                                        $<?= number_format((float)($item['hourlyRateExternal'] ?? $item['hourly_rate'] ?? 0), 2) ?>/hr
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary btn-sm w-100 shadow-sm" 
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

                <div class="modal fade" id="bookModal<?= $item['equipmentID'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form id="bookingForm<?= $item['equipmentID'] ?>">
                                <div class="modal-header bg-primary text-white border-0">
                                    <h5 class="modal-title fw-bold">Reserve <?= htmlspecialchars($item['equipmentName']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
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
                                    <button type="button" onclick="sendBooking(<?= $item['equipmentID'] ?>, <?= !empty($item['hasBriefing']) ? 'true' : 'false' ?>)" class="btn btn-primary px-4 shadow-sm">Confirm Booking</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if (!empty($item['hasBriefing'])): ?>
                <div class="modal fade" id="briefingModal<?= $item['equipmentID'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg border-start border-warning border-5">
                            <div class="modal-header bg-light border-0">
                                <h5 class="modal-title fw-bold text-dark">
                                    <i class="bi bi-shield-exclamation text-warning me-2"></i> 
                                    Mandatory Safety Briefing
                                </h5>
                                </div>
                            <div class="modal-body p-4">
                                <p class="text-muted mb-4">Please read the following safety instructions carefully. You must acknowledge them to finalize your booking.</p>
                                <div class="bg-white p-4 border rounded shadow-sm text-dark" style="max-height: 400px; overflow-y: auto;">
                                    <?= nl2br(htmlspecialchars($item['briefingContent'] ?? '')) ?>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light">
                                <button type="button" onclick="acknowledgeSafety(<?= $item['equipmentID'] ?>)" class="btn btn-warning px-5 fw-bold text-dark shadow-sm">
                                    <i class="bi bi-check2-circle me-2"></i> I Acknowledge
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Updated to accept the hasBriefing boolean
function sendBooking(id, hasBriefing) {
    const form = document.getElementById('bookingForm' + id);
    const msgBox = document.getElementById('msg' + id);
    const formData = new FormData(form);

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
            
            // If the equipment has a briefing, trap them in the modal. Otherwise, reload instantly.
            if (hasBriefing) {
                setTimeout(() => {
                    // Hide the booking modal
                    var bookModalEl = document.getElementById('bookModal' + id);
                    var bookModal = bootstrap.Modal.getInstance(bookModalEl);
                    bookModal.hide();
                    
                    // Show the unbreakable briefing modal
                    var briefingModal = new bootstrap.Modal(document.getElementById('briefingModal' + id), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    briefingModal.show();
                }, 1000);
            } else {
                setTimeout(() => { location.reload(); }, 2000);
            }
        }
    })
    .catch(err => {
        msgBox.classList.remove('d-none');
        msgBox.classList.add('alert-danger');
        msgBox.textContent = "Something went wrong. Check if you are logged in.";
    });
}

// Function called when they hit "I Acknowledge"
function acknowledgeSafety(id) {
    const formData = new FormData();
    formData.append('equipmentID', id);

    // Hits the backend route you already built in EquipmentController to save the acknowledgment!
    fetch('/LabSync-System/equipment/acknowledgeSafety', {
        method: 'POST',
        body: formData
    }).finally(() => {
        // Force the page reload to clear the lock and show success
        location.reload();
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>