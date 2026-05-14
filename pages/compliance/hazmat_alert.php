<?php $pageTitle = "Hazmat Warning"; 
 require_once dirname(__DIR__, 2) . '/includes/header.php'; 
?>
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Hazmat Warning — <?= htmlspecialchars($equipmentName ?? '') ?>
                </h4>
            </div>

            <div class="card-body p-4">

                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-shield-exclamation me-2 fs-5"></i>
                    <div>
                        You must <strong>read and acknowledge</strong> all warnings before proceeding.
                    </div>
                </div>

                <?php if (!empty($warnings)): ?>
                    <?php foreach ($warnings as $w): ?>
                        <div class="card mb-3 border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">
                                    <i class="bi bi-radioactive me-2"></i>
                                    <?= htmlspecialchars($w['hazardType']) ?>
                                </h5>
                                <p class="card-text">
                                    <?= htmlspecialchars($w['warningMessage']) ?>
                                </p>
                                <hr>
                                <h6 class="fw-semibold">Disposal Instructions:</h6>
                                <p class="card-text text-muted">
                                    <?= htmlspecialchars($w['disposalInstructions']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form action="/LabSync-System/compliance/acknowledgeWarning" method="POST">
                    <input type="hidden" name="equipmentID" value="<?= isset($equipmentID) ? (int) $equipmentID : 0 ?>">

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="acknowledgeCheck" required>
                        <label class="form-check-label fw-semibold" for="acknowledgeCheck">
                            I have read and understood all hazmat warnings and disposal instructions.
                        </label>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/LabSync-System/bookings" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-lg me-1"></i> I Acknowledge — Proceed
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>