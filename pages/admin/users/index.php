<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-people-fill text-primary"></i> User Management</h2>
                <p class="text-muted mb-0">Manage all registered system users</p>
            </div>
            <a href="/LabSync-System/admin/users/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New User
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>


        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> All Users</span>
                <span class="badge bg-secondary"><?= count($users) ?> total</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="bi bi-person-x"></i>
                        <p>No users found in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Clearance</th>
                                    <th>External</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr onclick="window.location='/LabSync-System/admin/users/<?= $user['userID'] ?>'">
                                        <td><span class="id-badge">#<?= $user['userID'] ?></span></td>
                                        <td><strong><?= htmlspecialchars($user['userName']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= ucwords(str_replace('_', ' ', $user['userType'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match ($user['userStatus']) {
                                                'active' => 'severity-low',
                                                'inactive' => 'severity-medium',
                                                'suspended' => 'severity-critical',
                                                default => 'severity-medium'
                                            };
                                            ?>
                                            <span class="severity-badge <?= $statusClass ?>">
                                                <?= htmlspecialchars($user['userStatus']) ?>
                                            </span>
                                        </td>
                                        <td>Level <?= (int) $user['clearanceLevel'] ?></td>
                                        <td>
                                            <?php if ($user['isExternal']): ?>
                                                <i class="bi bi-check-circle-fill text-warning"></i> Yes
                                            <?php else: ?>
                                                <i class="bi bi-dash-circle text-muted"></i> No
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <a href="/LabSync-System/admin/users/<?= $user['userID'] ?>"
                                                class="btn btn-sm btn-outline-primary">
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

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>