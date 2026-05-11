<?php 
/** @var array $equipment */
require_once __DIR__ . '/../../includes/header.php'; 
?>

<main>
    <div class="container mt-4 mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-tools text-primary"></i> Equipment Management</h2>
                <p class="text-muted mb-0">Monitor and manage all lab equipment</p>
            </div>
            <a href="/LabSync-System/equipment/create" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg"></i> Create New Equipment
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                <span class="fw-bold"><i class="bi bi-list-ul"></i> Equipment List</span>
                <span class="badge bg-secondary"><?= count($equipment) ?> total</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($equipment)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-tools text-muted" style="font-size: 2.5rem;"></i>
                        <p class="mt-2">No equipment registered yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Equipment Name</th>
                                    <th>Status</th>
                                    <th>Hourly Rate</th>
                                    <th>Clearance Level</th>
                                    <th>Briefing</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipment as $eq): ?>
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-light text-primary border">#<?= $eq['equipmentID'] ?></span></td>
                                        <td><strong><?= htmlspecialchars($eq['equipmentName']) ?></strong></td>
                                        <td>
                                            <?php
                                            $statusClass = match ($eq['equipmentStatus']) {
                                                'available' => 'bg-success',
                                                'in_use' => 'bg-info text-dark',
                                                'calibration_needed' => 'bg-warning text-dark',
                                                'under_maintenance' => 'bg-danger',
                                                'locked_out' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= ucfirst(str_replace('_', ' ', $eq['equipmentStatus'])) ?>
                                            </span>
                                        </td>
                                        <td>$<?= number_format((float) $eq['hourlyRateExternal'], 2) ?> / hr</td>
                                        
                                        <td>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-shield-check text-success me-1"></i> Level <?= (int) $eq['requiredClearanceLevel'] ?></span>
                                        </td>
                                        
                                        <td>
                                            <?php if (isset($eq['hasBriefing']) && $eq['hasBriefing'] > 0): ?>
                                                <span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-text text-primary me-1"></i> Yes</span>
                                            <?php else: ?>
                                                <span class="text-muted small">None</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="text-center pe-4" onclick="event.stopPropagation();">
                                            <a href="/LabSync-System/equipment/edit/<?= $eq['equipmentID'] ?>"
                                                class="btn btn-sm btn-outline-warning rounded px-3 me-2">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm rounded px-3" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteEquipmentModal" 
                                                    data-id="<?= $eq['equipmentID'] ?>"
                                                    data-name="<?= htmlspecialchars($eq['equipmentName']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<div class="modal fade" id="deleteEquipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Remove Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-muted">
                Are you sure you want to remove <strong id="eqNameDisplay" class="text-dark"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteEquipmentForm" method="POST" action="">
                    <button type="submit" class="btn btn-danger px-4">Confirm Removal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteEquipmentModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            document.getElementById('eqNameDisplay').textContent = name;
            document.getElementById('deleteEquipmentForm').action = '/LabSync-System/equipment/delete/' + id;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>