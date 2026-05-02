<?php $pageTitle = "Report Incident"; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i> Report Incident
                </h4>
            </div>

            <div class="card-body p-4">

                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        Submitting this form will <strong>auto-suspend</strong> the involved user
                        and cancel all their future bookings.
                    </div>
                </div>

                <form action="/LabSync-System/incidents/store" method="POST">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Involved User</label>
                            <select name="userID" class="form-select" required>
                                <option value="">-- Select a user --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= (int) $u['userID'] ?>">
                                        #<?= (int) $u['userID'] ?> — <?= htmlspecialchars($u['userName']) ?> (<?= htmlspecialchars($u['userType']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Equipment</label>
                            <select name="equipmentID" class="form-select" required>
                                <option value="">-- Select equipment --</option>
                                <?php foreach ($equipment as $e): ?>
                                    <option value="<?= (int) $e['equipmentID'] ?>">
                                        #<?= (int) $e['equipmentID'] ?> — <?= htmlspecialchars($e['equipmentName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Severity</label>
                            <select name="severity" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time of Incident</label>
                            <input type="datetime-local" name="timeOfIncident" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Incident Type</label>
                            <input type="text" name="incidentType" class="form-control"
                                   placeholder="e.g. Chemical Spill, Equipment Malfunction" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                      placeholder="Describe what happened..." required></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/LabSync-System/incidents" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-send me-1"></i> Submit Report
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>