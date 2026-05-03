<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    Log Entry <span class="id-badge">#<?= $log['logID'] ?></span>
                </h2>
                <p class="text-muted mb-0">Immutable audit record</p>
            </div>
            <a href="/LabSync-System/admin/logs" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Logs
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-info-circle"></i> Log Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">User</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($log['userName'] ?? 'System') ?> (ID:
                        <?= (int) $log['userID'] ?>)</dd>

                    <dt class="col-sm-3">Action Type</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-info text-dark" style="font-family:monospace;">
                            <?= htmlspecialchars($log['actionType']) ?>
                        </span>
                    </dd>

                    <dt class="col-sm-3">Table Affected</dt>
                    <dd class="col-sm-9"><code><?= htmlspecialchars($log['tableAffected']) ?></code></dd>

                    <dt class="col-sm-3">Record ID</dt>
                    <dd class="col-sm-9"><?= $log['recordID'] ?? '—' ?></dd>

                    <dt class="col-sm-3">Timestamp</dt>
                    <dd class="col-sm-9"><?= date('Y-m-d H:i:s', strtotime($log['createdAt'])) ?></dd>
                </dl>
            </div>
        </div>

        <?php if (!empty($log['description'])): ?>
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-card-text"></i> Description</div>
                <div class="card-body">
                    <div class="description-box"><?= htmlspecialchars($log['description']) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($log['oldValue']) || !empty($log['newValue'])): ?>
            <div class="row g-4">
                <?php if (!empty($log['oldValue'])): ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><i class="bi bi-clock-history text-danger"></i> Before</div>
                            <div class="card-body">
                                <pre class="description-box mb-0"
                                    style="border-left-color: var(--ls-danger);"><?= htmlspecialchars(json_encode(json_decode($log['oldValue']), JSON_PRETTY_PRINT) ?: $log['oldValue']) ?></pre>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($log['newValue'])): ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><i class="bi bi-arrow-right-circle text-success"></i> After</div>
                            <div class="card-body">
                                <pre class="description-box mb-0"
                                    style="border-left-color: var(--ls-success);"><?= htmlspecialchars(json_encode(json_decode($log['newValue']), JSON_PRETTY_PRINT) ?: $log['newValue']) ?></pre>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>