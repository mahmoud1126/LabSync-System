<?php $pageTitle = "Incident Reports"; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-clipboard-data me-2 text-primary"></i> Incident Reports
        </h4>
        <a href="/LabSync-System/incidents/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Report Incident
        </a>
    </div>

    <div class="card-body p-0">

        <?php if (empty($incidents)): ?>
            <div class="empty-state">
                <i class="bi bi-emoji-smile"></i>
                <h5>No incidents reported yet</h5>
                <p class="text-muted">Click "Report Incident" above to submit a new report.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Time</th>
                            <th>Severity</th>
                            <th>Type</th>
                            <th>User ID</th>
                            <th>Involved User</th>
                            <th>Equip. ID</th>
                            <th>Equipment</th>
                            <th>Reported By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidents as $i): ?>
                            <?php $url = '/LabSync-System/incidents/' . (int) $i['incidentID']; ?>
                            <tr onclick="window.location='<?= $url ?>'">
                                <td>
                                    <a href="<?= $url ?>" class="fw-semibold text-primary text-decoration-none"
                                       onclick="event.stopPropagation()">
                                        #<?= (int) $i['incidentID'] ?>
                                    </a>
                                </td>
                                <td class="text-muted small"><?= htmlspecialchars($i['timeOfIncident']) ?></td>
                                <td>
                                    <span class="severity-badge severity-<?= htmlspecialchars($i['severity']) ?>">
                                        <?= htmlspecialchars($i['severity']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($i['incidentType']) ?></td>
                                <td><span class="id-badge">#<?= (int) $i['userID'] ?></span></td>
                                <td><?= htmlspecialchars($i['involvedUserName'] ?? 'N/A') ?></td>
                                <td><span class="id-badge">#<?= (int) $i['equipmentID'] ?></span></td>
                                <td><?= htmlspecialchars($i['equipmentName'] ?? 'N/A') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($i['reporterName'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="<?= $url ?>" class="btn btn-sm btn-outline-primary"
                                       onclick="event.stopPropagation()">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>