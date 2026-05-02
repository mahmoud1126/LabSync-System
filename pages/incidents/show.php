<?php $pageTitle = "Incident #" . (int) $incident['incidentID']; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">
                <i class="bi bi-clipboard-data me-2 text-primary"></i>
                Incident Report
                <span class="text-muted">#<?= (int) $incident['incidentID'] ?></span>
            </h3>
            <a href="/LabSync-System/incidents" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><?= htmlspecialchars($incident['incidentType']) ?></span>
                    <span class="severity-badge severity-<?= htmlspecialchars($incident['severity']) ?>">
                        <?= htmlspecialchars($incident['severity']) ?>
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="ps-4 text-muted" style="width: 30%;">Time of Incident</th>
                            <td><?= htmlspecialchars($incident['timeOfIncident']) ?></td>
                        </tr>
                        <tr class="bg-light">
                            <th class="ps-4 text-muted">Involved User</th>
                            <td>
                                <span class="id-badge me-2">#<?= (int) $incident['userID'] ?></span>
                                <?= htmlspecialchars($incident['involvedUserName'] ?? 'N/A') ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 text-muted">Equipment</th>
                            <td>
                                <span class="id-badge me-2">#<?= (int) $incident['equipmentID'] ?></span>
                                <?= htmlspecialchars($incident['equipmentName'] ?? 'N/A') ?>
                            </td>
                        </tr>
                        <tr class="bg-light">
                            <th class="ps-4 text-muted">Reported By</th>
                            <td>
                                <span class="id-badge me-2">#<?= (int) $incident['reportedByID'] ?></span>
                                <?= htmlspecialchars($incident['reporterName'] ?? 'N/A') ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 text-muted align-top pt-3">Description</th>
                            <td class="pb-3 pe-4">
                                <div class="description-box">
                                    <?= htmlspecialchars($incident['description']) ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>