<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-tools text-primary"></i>
                    <?= htmlspecialchars($equipment['equipmentName']) ?>
                </h2>
                <p class="text-muted mb-0">
                    Equipment ID: <span class="id-badge">#<?= $equipment['equipmentID'] ?></span>
                </p>
            </div>
            <a href="/LabSync-System/admin/equipment" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Equipment
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">


            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Equipment Details
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Name</dt>
                            <dd class="col-sm-7"><?= htmlspecialchars($equipment['equipmentName']) ?></dd>

                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">
                                <?php
                                    $statusClass = match($equipment['equipmentStatus']) {
                                        'available'           => 'severity-low',
                                        'in_use'              => 'severity-medium',
                                        'calibration_needed'  => 'severity-medium',
                                        'under_maintenance'   => 'severity-high',
                                        'locked_out'          => 'severity-critical',
                                        default               => 'severity-medium'
                                    };
                                ?>
                                <span class="severity-badge <?= $statusClass ?>">
                                    <?= str_replace('_', ' ', $equipment['equipmentStatus']) ?>
                                </span>
                            </dd>

                            <dt class="col-sm-5">Hourly Rate</dt>
                            <dd class="col-sm-7">$<?= number_format((float)$equipment['hourlyRateExternal'], 2) ?></dd>

                            <dt class="col-sm-5">Required Clearance</dt>
                            <dd class="col-sm-7">Level <?= (int) $equipment['requiredClearanceLevel'] ?></dd>

                            <dt class="col-sm-5">Total Usage Hours</dt>
                            <dd class="col-sm-7"><?= (float) ($equipment['totalUsageHours'] ?? 0) ?>h</dd>

                            <dt class="col-sm-5">Calibration Hours</dt>
                            <dd class="col-sm-7">
                                <?= (float) ($equipment['currentCalibrationHours'] ?? 0) ?>h
                                / <?= (float) ($equipment['calibrationThresholdHours'] ?? 0) ?>h
                            </dd>

                            <dt class="col-sm-5">Power-Up Buffer</dt>
                            <dd class="col-sm-7"><?= (int) ($equipment['powerUpBufferMinutes'] ?? 0) ?> min</dd>

                            <dt class="col-sm-5">Cool-Down Buffer</dt>
                            <dd class="col-sm-7"><?= (int) ($equipment['coolDownBufferMinutes'] ?? 0) ?> min</dd>

                            <dt class="col-sm-5">Overhead %</dt>
                            <dd class="col-sm-7"><?= (float) ($equipment['overheadPercentage'] ?? 0) ?>%</dd>
                        </dl>
                    </div>
                </div>
            </div>


            <div class="col-md-6">

                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-arrow-repeat"></i> Update Status
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/LabSync-System/admin/equipment/<?= $equipment['equipmentID'] ?>/status">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Equipment Status</label>
                                <select class="form-select" name="equipmentStatus" required>
                                    <option value="available"           <?= $equipment['equipmentStatus'] === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="in_use"              <?= $equipment['equipmentStatus'] === 'in_use' ? 'selected' : '' ?>>In Use</option>
                                    <option value="calibration_needed"  <?= $equipment['equipmentStatus'] === 'calibration_needed' ? 'selected' : '' ?>>Calibration Needed</option>
                                    <option value="under_maintenance"   <?= $equipment['equipmentStatus'] === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                                    <option value="locked_out"          <?= $equipment['equipmentStatus'] === 'locked_out' ? 'selected' : '' ?>>Locked Out</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-currency-dollar"></i> Update Hourly Rate
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/LabSync-System/admin/equipment/<?= $equipment['equipmentID'] ?>/rate">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">External Hourly Rate ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                       name="hourlyRateExternal"
                                       value="<?= number_format((float)$equipment['hourlyRateExternal'], 2, '.', '') ?>"
                                       required>
                                <small class="text-muted">Rate charged to external researchers per hour.</small>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-cash"></i> Update Rate
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>