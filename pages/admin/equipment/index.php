<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-tools text-primary"></i> Equipment Management</h2>
                <p class="text-muted mb-0">Monitor and manage all lab equipment</p>
            </div>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> All Equipment</span>
                <span class="badge bg-secondary"><?= count($equipment) ?> total</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($equipment)): ?>
                    <div class="empty-state">
                        <i class="bi bi-tools"></i>
                        <p>No equipment registered yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Hourly Rate</th>
                                    <th>Clearance Req.</th>
                                    <th>Usage Hours</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipment as $eq): ?>
                                    <tr onclick="window.location='/LabSync-System/admin/equipment/<?= $eq['equipmentID'] ?>'">
                                        <td><span class="id-badge">#<?= $eq['equipmentID'] ?></span></td>
                                        <td><strong><?= htmlspecialchars($eq['equipmentName']) ?></strong></td>
                                        <td>
                                            <?php
                                            $statusClass = match ($eq['equipmentStatus']) {
                                                'available' => 'severity-low',
                                                'in_use' => 'severity-medium',
                                                'calibration_needed' => 'severity-medium',
                                                'under_maintenance' => 'severity-high',
                                                'locked_out' => 'severity-critical',
                                                default => 'severity-medium'
                                            };
                                            ?>
                                            <span class="severity-badge <?= $statusClass ?>">
                                                <?= str_replace('_', ' ', $eq['equipmentStatus']) ?>
                                            </span>
                                        </td>
                                        <td>$<?= number_format((float) $eq['hourlyRateExternal'], 2) ?></td>
                                        <td>Level <?= (int) $eq['requiredClearanceLevel'] ?></td>
                                        <td><?= (float) ($eq['totalUsageHours'] ?? 0) ?>h</td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <a href="/LabSync-System/admin/equipment/<?= $eq['equipmentID'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-gear"></i> Manage
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

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>