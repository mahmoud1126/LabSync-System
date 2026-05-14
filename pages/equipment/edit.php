<?php

/** @var array $equipment */
/** @var array $dependencies */
/** @var array $availablePool */
require_once __DIR__ . '/../../includes/header.php';
?>

<main>
    <div class="container mt-4 mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-pencil-square text-primary"></i>
                    Edit Equipment: <?= htmlspecialchars($equipment['equipmentName']) ?>
                </h2>
                <p class="text-muted mb-0">
                    Equipment ID: <span class="badge bg-light text-dark border">#<?= $equipment['equipmentID'] ?></span>
                </p>
            </div>
            <a href="/LabSync-System/equipment" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left"></i> Back to Equipment
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="/LabSync-System/equipment/update/<?= $equipment['equipmentID'] ?>">

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            <i class="bi bi-sliders"></i> Core Configuration
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Equipment Name *</label>
                                <input type="text" class="form-control" name="equipmentName" value="<?= htmlspecialchars($equipment['equipmentName']) ?>" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Hourly Rate ($) *</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="hourlyRateExternal" value="<?= number_format((float)$equipment['hourlyRateExternal'], 2, '.', '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Clearance Level *</label>
                                    <select class="form-select" name="requiredClearanceLevel" required>
                                        <?php for ($i = 0; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>" <?= $equipment['requiredClearanceLevel'] == $i ? 'selected' : '' ?>>Level <?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Power-Up Buffer (mins)</label>
                                    <input type="number" min="0" class="form-control" name="powerUpBufferMinutes" value="<?= (int)($equipment['powerUpBufferMinutes'] ?? 0) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cool-Down Buffer (mins)</label>
                                    <input type="number" min="0" class="form-control" name="coolDownBufferMinutes" value="<?= (int)($equipment['coolDownBufferMinutes'] ?? 0) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Status</label>
                                <select class="form-select" name="equipmentStatus" required>
                                    <option value="available" <?= $equipment['equipmentStatus'] === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="in_use" <?= $equipment['equipmentStatus'] === 'in_use' ? 'selected' : '' ?>>In Use</option>
                                    <option value="calibration_needed" <?= $equipment['equipmentStatus'] === 'calibration_needed' ? 'selected' : '' ?>>Calibration Needed</option>
                                    <option value="under_maintenance" <?= $equipment['equipmentStatus'] === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                                    <option value="locked_out" <?= $equipment['equipmentStatus'] === 'locked_out' ? 'selected' : '' ?>>Locked Out</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-5">

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white fw-bold py-3 text-primary border-bottom-0">
                            <i class="bi bi-shield-check"></i> Safety Briefing
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-muted small">Mandatory Pre-Booking Instructions</label>
                                <textarea class="form-control bg-light" name="briefingContent" rows="7" placeholder="Enter safety guidelines, hazards, or cleaning instructions here. Users must acknowledge this before booking..."><?= htmlspecialchars($equipment['briefingContent'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white fw-bold py-3 text-muted border-bottom-0">
                            <i class="bi bi-bar-chart"></i> Usage Metrics (Read-Only)
                        </div>
                        <div class="card-body bg-light rounded-bottom">
                            <dl class="row mb-0 small">
                                <dt class="col-sm-6 text-muted">Total Usage Hours:</dt>
                                <dd class="col-sm-6 fw-bold"><?= (float) ($equipment['totalUsageHours'] ?? 0) ?> h</dd>

                                <dt class="col-sm-6 text-muted">Calibration Status:</dt>
                                <dd class="col-sm-6 fw-bold">
                                    <?= (float) ($equipment['currentCalibrationHours'] ?? 0) ?> h / <?= (float) ($equipment['calibrationThresholdHours'] ?? 0) ?> h
                                </dd>

                                <dt class="col-sm-6 text-muted">Overhead Rate:</dt>
                                <dd class="col-sm-6 fw-bold"><?= (float) ($equipment['overheadPercentage'] ?? 0) ?>%</dd>
                            </dl>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5 rounded-pill">
                        <i class="bi bi-save me-1"></i> Save Configuration
                    </button>
                </div>
            </div>

        </form>

        <div class="card shadow-sm border-0 mt-4 border-start border-info border-4">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-diagram-3 text-info"></i>
                    Secondary Equipment
                    <span class="badge bg-light text-dark border ms-2"><?= count($dependencies ?? []) ?></span>
                </div>
                <small class="text-muted">Auto-booked alongside this primary equipment</small>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    When a researcher books <strong><?= htmlspecialchars($equipment['equipmentName']) ?></strong>,
                    every secondary equipment listed below will be reserved for the exact same time slot.
                    If any one of them is unavailable, the booking is blocked to prevent incomplete setups.
                </p>

                <?php if (empty($dependencies)): ?>
                    <div class="alert alert-light border text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        No secondary equipment linked yet. Add one below.
                    </div>
                <?php else: ?>
                    <ul class="list-group mb-3">
                        <?php foreach ($dependencies as $dep): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-light text-dark border me-2">#<?= (int) $dep['equipmentID'] ?></span>
                                    <strong><?= htmlspecialchars($dep['equipmentName']) ?></strong>
                                    <?php
                                    $statusClass = match ($dep['equipmentStatus']) {
                                        'available'          => 'bg-success',
                                        'in_use'             => 'bg-info text-dark',
                                        'calibration_needed' => 'bg-warning text-dark',
                                        'under_maintenance'  => 'bg-danger',
                                        'locked_out'         => 'bg-dark',
                                        default              => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?> ms-2">
                                        <?= ucfirst(str_replace('_', ' ', $dep['equipmentStatus'])) ?>
                                    </span>
                                </div>
                                <form method="POST"
                                    action="/LabSync-System/equipment/dependency/remove/<?= (int) $equipment['equipmentID'] ?>"
                                    onsubmit="return confirm('Unlink this secondary equipment?');"
                                    class="m-0">
                                    <input type="hidden" name="secondaryEquipmentID" value="<?= (int) $dep['equipmentID'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="bi bi-x-circle"></i> Unlink
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($availablePool)): ?>
                    <form method="POST"
                        action="/LabSync-System/equipment/dependency/add/<?= (int) $equipment['equipmentID'] ?>"
                        class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Link new secondary equipment</label>
                            <select class="form-select" name="secondaryEquipmentID" required>
                                <option value="">-- Select equipment --</option>
                                <?php foreach ($availablePool as $opt): ?>
                                    <option value="<?= (int) $opt['equipmentID'] ?>">
                                        #<?= (int) $opt['equipmentID'] ?> — <?= htmlspecialchars($opt['equipmentName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-plus-circle me-1"></i> Link Secondary
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary small mb-0">
                        Every other piece of equipment is already linked as secondary.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>