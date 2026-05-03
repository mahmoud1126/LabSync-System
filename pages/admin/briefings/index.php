<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-exclamation-triangle-fill text-primary"></i>
                    Safety Briefings
                </h2>
            </div>
            <a href="/LabSync-System/admin/briefings/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Briefing
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> All Safety Briefings</span>
                <span class="badge bg-secondary"><?= count($briefings) ?> total</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($briefings)): ?>
                    <div class="empty-state">
                        <i class="bi bi-clipboard-x"></i>
                        <p>No safety briefings created yet.</p>
                        <a href="/LabSync-System/admin/briefings/create" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus-lg"></i> Create First Briefing
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Briefing ID</th>
                                    <th>Equipment ID</th>
                                    <th>Content Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($briefings as $b): ?>
                                    <tr>
                                        <td><span class="id-badge">#<?= $b['briefingID'] ?></span></td>
                                        <td><span class="id-badge">#<?= $b['equipmentID'] ?></span></td>
                                        <td>
                                            <small>
                                                <?= htmlspecialchars(substr($b['briefingContent'] ?? '', 0, 100)) ?>
                                                <?= strlen($b['briefingContent'] ?? '') > 100 ? '…' : '' ?>
                                            </small>
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