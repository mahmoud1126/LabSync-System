<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<?php

$roleIcons = [
    'researcher' => 'bi-person-badge-fill text-primary',
    'guest_researcher' => 'bi-person-vcard-fill text-warning',
    'lab_manager' => 'bi-shield-fill-check text-info',
    'faculty_pi' => 'bi-mortarboard-fill text-success',
];
$userRoleIcon = $roleIcons[$user['userType']] ?? 'bi-person-circle text-primary';
?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi <?= $userRoleIcon ?>"></i>
                    <?= htmlspecialchars($user['userName']) ?>
                    <?php if ($user['userType'] === 'guest_researcher'): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.5em; vertical-align: middle;">
                            <i class="bi bi-globe"></i> EXTERNAL
                        </span>
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-0">
                    User ID: <span class="id-badge">#<?= $user['userID'] ?></span>
                    &middot; Role: <strong><?= ucwords(str_replace('_', ' ', $user['userType'])) ?></strong>
                </p>
            </div>
            <a href="/LabSync-System/admin/users" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">


            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> User Details
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Username</dt>
                            <dd class="col-sm-7"><?= htmlspecialchars($user['userName']) ?></dd>

                            <dt class="col-sm-5">Role</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-info text-dark">
                                    <i class="bi <?= $userRoleIcon ?>"></i>
                                    <?= ucwords(str_replace('_', ' ', $user['userType'])) ?>
                                </span>
                            </dd>

                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">
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
                            </dd>

                            <dt class="col-sm-5">Clearance Level</dt>
                            <dd class="col-sm-7">Level <?= (int) $user['clearanceLevel'] ?></dd>

                            <dt class="col-sm-5">External</dt>
                            <dd class="col-sm-7">
                                <?php if ($user['isExternal']): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-check-circle-fill"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">No (Internal)</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-5">Max Hours / Week</dt>
                            <dd class="col-sm-7"><?= (int) $user['maxBookingHoursPerWeek'] ?>h</dd>

                            <dt class="col-sm-5">Current Booked</dt>
                            <dd class="col-sm-7"><?= (float) $user['currentWeeklyBookedHours'] ?>h</dd>
                        </dl>
                    </div>
                </div>
            </div>


            <div class="col-md-6">


                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-shield-check"></i> Update Status
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/LabSync-System/admin/users/<?= $user['userID'] ?>/status">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">User Status</label>
                                <select class="form-select" name="userStatus" required>
                                    <option value="active" <?= $user['userStatus'] === 'active' ? 'selected' : '' ?>>Active
                                    </option>
                                    <option value="inactive" <?= $user['userStatus'] === 'inactive' ? 'selected' : '' ?>>
                                        Inactive</option>
                                    <option value="suspended" <?= $user['userStatus'] === 'suspended' ? 'selected' : '' ?>>
                                        Suspended</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-key-fill"></i> Update Clearance
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/LabSync-System/admin/users/<?= $user['userID'] ?>/clearance">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Clearance Level (0-5)</label>
                                <select class="form-select" name="clearanceLevel" required>
                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= (int) $user['clearanceLevel'] === $i ? 'selected' : '' ?>>
                                            Level <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-arrow-repeat"></i> Update Clearance
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>


        <?php if ($user['userType'] === 'guest_researcher' && !empty($guestData)): ?>

            <div class="row g-4 mt-2">

                <div class="col-md-6">
                    <div class="card h-100 border-warning">
                        <div class="card-header bg-warning-subtle">
                            <i class="bi bi-person-vcard-fill text-warning"></i> Guest Account Info
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Institution</dt>
                                <dd class="col-sm-7"><?= htmlspecialchars($guestData['institution'] ?? '—') ?></dd>

                                <dt class="col-sm-5">Sponsor PI</dt>
                                <dd class="col-sm-7">
                                    <?= htmlspecialchars($guestData['sponsorName'] ?? '—') ?>
                                    <small class="text-muted">(ID: <?= (int) ($guestData['sponsorPIID'] ?? 0) ?>)</small>
                                </dd>

                                <dt class="col-sm-5">Expiration Date</dt>
                                <dd class="col-sm-7">
                                    <?php
                                    $expTs = strtotime($guestData['expirationDate']);
                                    $now = time();
                                    $expired = $expTs < $now;
                                    $daysLeft = ceil(($expTs - $now) / 86400);

                                    if ($expired) {
                                        $color = '#D32F2F';
                                        $note = '(EXPIRED)';
                                    } elseif ($daysLeft <= 7) {
                                        $color = '#FF7518';
                                        $note = "($daysLeft days left)";
                                    } else {
                                        $color = '#3FB618';
                                        $note = "($daysLeft days left)";
                                    }
                                    ?>
                                    <strong style="color: <?= $color ?>;">
                                        <?= date('Y-m-d', $expTs) ?>
                                    </strong>
                                    <small class="text-muted"><?= $note ?></small>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-warning">
                        <div class="card-header bg-warning-subtle">
                            <i class="bi bi-calendar-event"></i> Extend Expiration Date
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/LabSync-System/admin/users/<?= $user['userID'] ?>/expiration">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Current Expiration</label>
                                    <input type="text" class="form-control"
                                        value="<?= date('Y-m-d', strtotime($guestData['expirationDate'])) ?>" disabled
                                        readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="expirationDate" class="form-label fw-semibold">New Expiration Date *</label>
                                    <input type="date" class="form-control" id="expirationDate" name="expirationDate"
                                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                        max="<?= date('Y-m-d', strtotime('+365 days')) ?>" required>
                                    <small class="text-muted">
                                        Must be a future date within 365 days. Updating reactivates the account if inactive.
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-calendar-plus"></i> Extend Expiration
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>