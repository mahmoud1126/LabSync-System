<?php $pageTitle = "Pending Supervisions"; 
 require_once dirname(__DIR__, 2) . '/includes/header.php'; 
?>

<div class="row">
    <div class="col-12">

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-clock-history me-2 text-warning"></i> Pending Supervision Requests
                </h4>
            </div>

            <div class="card-body p-0">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No pending supervision requests.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Researcher</th>
                                    <th>Equipment</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td><?= (int) $b['bookingID'] ?></td>
                                        <td><?= htmlspecialchars($b['UserName']) ?></td>
                                        <td><?= htmlspecialchars($b['equipmentName']) ?></td>
                                        <td><?= htmlspecialchars($b['startTime']) ?></td>
                                        <td><?= htmlspecialchars($b['endTime']) ?></td>
                                        <td>
                                            <form action="/LabSync-System/compliance/approveSupervision" method="POST" class="d-inline">
                                                <input type="hidden" name="bookingID" value="<?= (int) $b['bookingID'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal"
                                                data-booking-id="<?= (int) $b['bookingID'] ?>">
                                                <i class="bi bi-x-lg me-1"></i> Reject
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
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/LabSync-System/compliance/rejectSupervision" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle me-2 text-danger"></i> Reject Supervision
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="bookingID" id="rejectBookingID">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason for Rejection</label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="Explain why..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('rejectModal').addEventListener('show.bs.modal', function (e) {
        const bookingID = e.relatedTarget.getAttribute('data-booking-id');
        document.getElementById('rejectBookingID').value = bookingID;
    });
</script>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>