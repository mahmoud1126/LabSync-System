<?php $pageTitle = "Equipment Management"; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-pc-display me-2 text-primary"></i> Equipment List
        </h4>
        <?php if ($userRole === 'lab_manager'): ?>
            <a href="/equipment/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create New Equipment
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body p-0">
        <?php if (empty($equipment)): ?>
            <div class="empty-state text-center p-5">
                <i class="bi bi-inboxes display-4 text-muted"></i>
                <h5 class="mt-3">No equipment available yet</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Equipment Name</th>
                            <th>Status</th>
                            <th>Hourly Rate</th>
                            <th>Access Level</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipment as $eq): ?>
                            <tr>
                                <td><span class="id-badge fw-semibold text-primary">#<?= (int)$eq['equipmentID'] ?></span></td>
                                <td class="fw-medium"><?= htmlspecialchars($eq['name']) ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        if ($eq['equipmentStatus'] === 'available') $badgeClass = 'bg-success';
                                        elseif ($eq['equipmentStatus'] === 'in_use') $badgeClass = 'bg-warning text-dark';
                                        elseif ($eq['equipmentStatus'] === 'under_maintenance') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars(str_replace('_', ' ', ucfirst($eq['equipmentStatus']))) ?>
                                    </span>
                                </td>
                                <td class="text-muted">$<?= number_format($eq['hourlyRateExternal'] ?? 0, 2) ?> / hr</td>

                                <!-- Fixed Clearance/Access Column[cite: 2] -->
                                <td>
                                    <?php if (isset($eq['hasAccess']) && $eq['hasAccess']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Cleared
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" 
                                              title="<?= htmlspecialchars($eq['accessMessage'] ?? 'Restricted Access') ?>">
                                            <i class="bi bi-lock me-1"></i> Restricted
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-end">
                                    <?php if ($userRole === 'lab_manager'): ?>
                                        <a href="/equipment/edit/<?= $eq['equipmentID'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i> Edit</a>
                                        <form action="/equipment/delete/<?= $eq['equipmentID'] ?>" method="POST" style="display:inline;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirm delete?');"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
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