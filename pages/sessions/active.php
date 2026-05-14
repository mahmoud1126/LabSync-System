<?php $pageTitle = "Active Sessions"; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/header.php'; ?>

<?php
/** @var array $activeSessions */
/** @var array $startableBookings */
/** @var string|null $userRole */
$startableBookings = $startableBookings ?? [];
$activeSessions    = $activeSessions ?? [];
$userRole          = $userRole ?? null;
$isResearcher      = in_array($userRole, ['researcher', 'guest_researcher'], true);
?>

<div class="row">
    <div class="col-12">

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($isResearcher): ?>
            <div class="card mb-4 border-success">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-broadcast text-success me-2"></i>
                        Sessions Ready to Start
                    </h4>
                    <span class="badge bg-success"><?= count($startableBookings) ?></span>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($startableBookings)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-1"></i>
                            <p class="mt-2 mb-0">No bookings are currently within their reserved time window.</p>
                            <small>This list will populate when one of your confirmed bookings reaches its start time.</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Equipment</th>
                                        <th>Reserved Window</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($startableBookings as $b): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    #<?= (int) $b['bookingID'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($b['equipmentName']) ?></strong>
                                                <?php if (!empty($b['isAutoBooked'])): ?>
                                                    <span class="badge bg-info text-dark ms-1" title="Auto-booked as secondary equipment">
                                                        <i class="bi bi-diagram-3"></i> Secondary
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <?= date('M d, Y', strtotime($b['startTime'])) ?><br>
                                                    <span class="text-muted">
                                                        <?= date('H:i', strtotime($b['startTime'])) ?>
                                                        –
                                                        <?= date('H:i', strtotime($b['endTime'])) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <form action="/LabSync-System/sessions/start" method="POST" class="d-inline">
                                                    <input type="hidden" name="bookingID"   value="<?= (int) $b['bookingID'] ?>">
                                                    <input type="hidden" name="equipmentID" value="<?= (int) $b['equipmentID'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-play-fill me-1"></i> Start Session
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
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-play-circle me-2 text-success"></i> Active Sessions
                </h4>
                <span class="badge bg-secondary"><?= count($activeSessions) ?></span>
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
