<?php
/** @var array $users */
/** @var string $pageTitle */
?>


<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">User Directory</h2>
        <button class="btn btn-primary btn-sm">+ Add External User</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Active Grant Access</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <?= strtoupper(substr($user['userName'], 0, 2)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($user['userName']) ?></div>
                                    <div class="small text-muted">ID: #<?= $user['userID'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars(str_replace('_', ' ', $user['userType'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['assignedGrants']): ?>
                                <?php $grants = explode(', ', $user['assignedGrants']); ?>
                                <?php foreach ($grants as $g): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1"><?= htmlspecialchars($g) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted small italic">No grants assigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical">Options</i>
                                </button>
                                <ul class="dropdown-menu shadow-sm">
                                    <li><a class="dropdown-item" href="#">Manage Access</a></li>
                                    <li><a class="dropdown-item" href="#">View Usage Report</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#">Deactivate</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>  