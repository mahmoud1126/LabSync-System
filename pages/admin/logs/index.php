<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-shield-lock-fill text-primary"></i> Audit Logs</h2>
                <p class="text-muted mb-0">
                    <span class="badge bg-success ms-1">Append-Only</span>
                </p>
            </div>
            <a href="/LabSync-System/admin/logs" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Clear Filters
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>


        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filter Logs
            </div>
            <div class="card-body">
                <form method="GET" action="/LabSync-System/admin/logs" class="row g-3">

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Action Type</label>
                        <select class="form-select" name="actionType">
                            <option value="">— All Actions —</option>
                            <?php
                            $actions = [
                                'INCIDENT_REPORTED',
                                'INCIDENT_AUTO_LOCKOUT',
                                'GUEST_ONBOARDED',
                                'GUEST_EXTENDED',
                                'GUEST_EXPIRED',
                                'SUPERVISED_SESSION_REQUESTED',
                                'SUPERVISED_SESSION_APPROVED',
                                'SUPERVISED_SESSION_REJECTED',
                                'USER_CREATED',
                                'USER_STATUS_UPDATED',
                                'USER_CLEARANCE_UPDATED',
                                'EQUIPMENT_STATUS_UPDATED',
                                'EQUIPMENT_RATE_UPDATED',
                                'GRANT_REALLOCATION',
                                'HAZMAT_ACKNOWLEDGED',
                                'SAFETY_BRIEFING_CREATED'
                            ];
                            foreach ($actions as $a):
                                ?>
                                <option value="<?= $a ?>" <?= $filterAction === $a ? 'selected' : '' ?>>
                                    <?= $a ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">User ID</label>
                        <input type="number" class="form-control" name="userID"
                            value="<?= htmlspecialchars($filterUserID ?? '') ?>" placeholder="e.g. 5">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Log Entries</span>
                <span class="badge bg-secondary"><?= count($logs) ?> entries</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="empty-state">
                        <i class="bi bi-search"></i>
                        <p>No logs match your filter.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Log ID</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>Description</th>
                                    <th>Timestamp</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr onclick="window.location='/LabSync-System/admin/logs/<?= $log['logID'] ?>'">
                                        <td><span class="id-badge">#<?= $log['logID'] ?></span></td>
                                        <td><?= htmlspecialchars($log['userName'] ?? 'System') ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark" style="font-family:monospace;">
                                                <?= htmlspecialchars($log['actionType']) ?>
                                            </span>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($log['tableAffected']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($log['description'] ?? '', 0, 60)) ?>
                                                <?= strlen($log['description'] ?? '') > 60 ? '…' : '' ?></small>
                                        </td>
                                        <td><small><?= date('Y-m-d H:i', strtotime($log['createdAt'])) ?></small></td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <a href="/LabSync-System/admin/logs/<?= $log['logID'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
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