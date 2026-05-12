<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            
            <div class="text-center mb-4">
                <h2 class="fw-bold"> LabSync</h2>   
            </div>

            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show shadow-sm">
                    <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill' ?> me-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-dark text-center">
                        <i class="bi bi-person-circle text-secondary me-2"></i> Sign In
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/LabSync-System/login" autocomplete="on">
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small text-uppercase" style="letter-spacing: 0.5px;">Username</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </span>
                                <input
                                    type="text"
                                    name="userName"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Enter your username"
                                    autocomplete="username"
                                    required
                                    autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium text-dark small text-uppercase" style="letter-spacing: 0.5px;">Password</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock-fill text-primary"></i>
                                </span>
                                <input
                                    type="password"
                                    name="userPassword"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-medium shadow-sm rounded-pill">
                            Sign In
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>