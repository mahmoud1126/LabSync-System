<?php $pageTitle = "Add Equipment"; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-4" style="max-width: 800px;">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i> Add New Equipment</h4>
            <a href="/equipment" class="btn btn-sm btn-outline-secondary">Cancel</a>
        </div>
        <div class="card-body p-4">
            <form action="/equipment/store" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Equipment Name</label>
                    <input type="text" class="form-control" name="equipmentName" required>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="equipmentStatus" required>
                            <option value="available">Available</option>
                            <option value="under_maintenance">Under Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hourly Rate ($)</label>
                        <input type="number" step="0.01" class="form-control" name="hourlyRateExternal" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Required Clearance Level</label>
                    <input type="number" class="form-control" name="requiredClearanceLevel" min="0" max="5" value="1" required>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Save Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>