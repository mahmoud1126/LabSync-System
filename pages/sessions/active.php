<?php $pageTitle = "Active Sessions"; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="row">
    <div class="col-12">

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-play-circle me-2 text-success"></i> Active Sessions
                </h4>
            </div>

            <div class="card-body p-0">
                <?php if (empty($activeSessions)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No active sessions.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Equipment</th>
                                    <th>Start Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeSessions as $s): ?>
                                    <tr>
                                        <td><?= (int) $s['sessionID'] ?></td>
                                        <td><?= htmlspecialchars($s['equipmentName']) ?></td>
                                        <td><?= htmlspecialchars($s['actualStartTime']) ?></td>
                                        <td>
                                            <form action="/LabSync-System/sessions/end" method="POST" class="d-inline">
                                                <input type="hidden" name="sessionID" value="<?= (int) $s['sessionID'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to end this session?')">
                                                    <i class="bi bi-stop-circle me-1"></i> End Session
                                                </button>
                                            </form>
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
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>