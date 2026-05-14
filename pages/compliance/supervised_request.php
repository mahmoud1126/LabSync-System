<?php $pageTitle = "Request Supervision"; 
 require_once dirname(__DIR__, 2) . '/includes/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-person-check me-2 text-primary"></i> Request Supervision
                </h4>
            </div>

            <div class="card-body p-4">

                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        This booking requires a <strong>Lab Manager</strong> to supervise your session.
                    </div>
                </div>

                <form action="/LabSync-System/compliance/requestSupervision" method="POST">

                    <input type="hidden" name="bookingID" value="<?= isset($bookingID) ? (int) $bookingID : 0 ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Select Lab Manager</label>
                            <select name="labManagerID" class="form-select" required>
                                <option value="">-- Select a Lab Manager --</option>
                                <?php if (!empty($labManagers)): ?>
                                    <?php foreach ($labManagers as $lm): ?>
                                        <option value="<?= (int) $lm['userID'] ?>">
                                            #<?= (int) $lm['userID'] ?> — <?= htmlspecialchars($lm['userName']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/LabSync-System/bookings" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Submit Request
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>