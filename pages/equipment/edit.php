<?php $pageTitle = "Edit Equipment"; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php /** @var array $equipment */ ?>

<div class="container mt-4" style="max-width: 800px;">
    <div class="card shadow-sm border-warning">
        <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i> Edit: #<?= (int)$equipment['equipmentID'] ?></h4>
            <a href="/equipment" class="btn btn-sm btn-outline-secondary">Cancel</a>
        </div>
        <div class="card-body p-4">
            <form action="/equipment/update/<?= (int)$equipment['equipmentID'] ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Equipment Name</label>
                    <input type="text" class="form-control" name="equipmentName" value="<?= htmlspecialchars($equipment['name'] ?? '') ?>" required>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="equipmentStatus" required>
                            <option value="available" <?= $equipment['equipmentStatus'] === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="under_maintenance" <?= $equipment['equipmentStatus'] === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hourly Rate ($)</label>
                        <input type="number" step="0.01" class="form-control" name="hourlyRateExternal" value="<?= htmlspecialchars($equipment['hourlyRateExternal'] ?? '0.00') ?>" required>
                    </div>
                </div>

                <!-- Updated Buffer Minutes Fields -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Power-Up Buffer (mins)</label>
                        <input type="number" class="form-control" name="powerUpBufferMinutes" min="0" value="<?= (int)($equipment['powerUpBufferMinutes'] ?? 0) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cool-Down Buffer (mins)</label>
                        <input type="number" class="form-control" name="coolDownBufferMinutes" min="0" value="<?= (int)($equipment['coolDownBufferMinutes'] ?? 0) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Required Clearance Level</label>
                    <input type="number" class="form-control" name="requiredClearanceLevel" min="0" max="5" value="<?= (int)($equipment['requiredClearanceLevel'] ?? 1) ?>" required>
                </div>

                <!-- Updated Safety Briefing Field -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Safety Briefing Content</label>
                    <textarea class="form-control" name="briefingContent" rows="4" required><?= htmlspecialchars($equipment['briefingContent'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check2-circle me-1"></i> Update Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>