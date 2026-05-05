<?php
/** @var array $equipment Provided by EquipmentController via BaseController */
/** @var string $userRole Provided by EquipmentController */
?>



<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Available Equipment</h2>
    <div class="row" id="equipment-list">
        <?php foreach ($equipment as $item): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($item['equipmentName']) ?></h5>
                        <p class="text-muted small">Clearance: <?= $item['requiredClearanceLevel'] ?></p>
                        
                        <!-- The Book Button -->
                        <button class="btn btn-primary" onclick="openBookingModal(<?= $item['equipmentID'] ?>)">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Step 1: Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bookingForm">
                <div class="modal-header"><h5>Book Equipment</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="equipmentID" id="modalEquipID">
                    <label>Start Time</label>
                    <input type="datetime-local" name="startTime" class="form-control mb-2" required>
                    <label>End Time</label>
                    <input type="datetime-local" name="endTime" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Step 2: Safety Briefing Modal -->
<!-- Step 2: Safety Briefing Modal -->
<div class="modal fade" id="safetyModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning"><h5>⚠️ Mandatory Safety Briefing</h5></div>
            <div class="modal-body" id="safetyContent"></div>
            <div class="modal-footer">
                <!-- UPDATED BUTTON: Calls confirmSafety() instead of just reloading -->
                <button class="btn btn-dark" id="confirmSafetyBtn">I Acknowledge & Understand</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEquipmentID = null; // Track ID across modals

function openBookingModal(id) {
    currentEquipmentID = id; // Store the ID when opening the first modal
    document.getElementById('modalEquipID').value = id;
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
}

document.getElementById('bookingForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const response = await fetch('/LabSync-System/EquipmentController/book', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
        
        if (result.showBriefing) {
            document.getElementById('safetyContent').innerHTML = result.briefingContent;
            new bootstrap.Modal(document.getElementById('safetyModal')).show();
        } else {
            alert('Booking successful!');
            location.reload();
        }
    } else {
        alert(result.message);
    }
};

// NEW FUNCTION: Handles the Safety Acknowledgment
document.getElementById('confirmSafetyBtn').onclick = async () => {
    const response = await fetch('/LabSync-System/equipment/acknowledge', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `equipmentID=${currentEquipmentID}`
    });
    
    const result = await response.json();
    
    if (result.success) {
        alert('Safety acknowledgment saved. Your booking is confirmed!');
        location.reload();
    } else {
        alert('Error saving acknowledgment. Please try again.');
    }
};
</script>