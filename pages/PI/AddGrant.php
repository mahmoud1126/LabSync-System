<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-4 mb-5" style="max-width: 800px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Add New System Grant</h5>
            <a href="/LabSync-System/grants" class="btn btn-sm btn-light text-primary fw-bold">Cancel</a>
        </div>
        <div class="card-body p-4">
            
            <?php if (isset($flash)): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="/LabSync-System/grants/store" method="POST">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Grant Name</label>
                    <input type="text" class="form-control" name="grantName" placeholder="e.g., NSF Research Funding 2026" required>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Total Funds Amount ($)</label>
                        <input type="number" step="0.01" min="1" class="form-control" name="totalBudget" placeholder="0.00" required>
                        <small class="text-muted">Initial balance will be set to this amount.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Expiration Date</label>
                        <input type="date" class="form-control" name="expirationDate" required>
                        <small class="text-muted">Status is automatically set based on this date.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save me-1"></i> Confirm & Add Grant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>