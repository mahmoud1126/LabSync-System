<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="mb-4">
            <h2 class="mb-1"><i class="bi bi-shield-lock-fill text-primary"></i> Admin Panel</h2>
            <p class="text-muted mb-0">Manage system users, equipment, grants, and audit logs</p>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">


            <div class="col-md-6 col-lg-4">
                <a href="/LabSync-System/admin/users" class="text-decoration-none">
                    <div class="card h-100 admin-tile">
                        <div class="card-body text-center p-4">
                            <div class="admin-icon bg-primary-subtle text-primary mb-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h5 class="fw-bold text-dark">User Management</h5>
                            <p class="text-muted small mb-0">
                                Create users, update status, manage clearance levels
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Equipment Card -->
            <div class="col-md-6 col-lg-4">
                <a href="/LabSync-System/admin/equipment" class="text-decoration-none">
                    <div class="card h-100 admin-tile">
                        <div class="card-body text-center p-4">
                            <div class="admin-icon bg-warning-subtle text-warning mb-3">
                                <i class="bi bi-tools"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Equipment</h5>
                            <p class="text-muted small mb-0">
                                Manage status, hourly rates, and maintenance
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Grants Card -->
            <div class="col-md-6 col-lg-4">
                <a href="/LabSync-System/admin/grants" class="text-decoration-none">
                    <div class="card h-100 admin-tile">
                        <div class="card-body text-center p-4">
                            <div class="admin-icon bg-success-subtle text-success mb-3">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Grants Overview</h5>
                            <p class="text-muted small mb-0">
                                View grants, balances, and transaction history
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Audit Logs Card -->
            <div class="col-md-6 col-lg-4">
                <a href="/LabSync-System/admin/logs" class="text-decoration-none">
                    <div class="card h-100 admin-tile">
                        <div class="card-body text-center p-4">
                            <div class="admin-icon bg-info-subtle text-info mb-3">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Audit Logs</h5>
                            <p class="text-muted small mb-0">
                                Review the immutable system history (Append-Only)
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Safety Briefings Card -->
            <div class="col-md-6 col-lg-4">
                <a href="/LabSync-System/admin/briefings" class="text-decoration-none">
                    <div class="card h-100 admin-tile">
                        <div class="card-body text-center p-4">
                            <div class="admin-icon bg-danger-subtle text-danger mb-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Safety Briefings</h5>
                            <p class="text-muted small mb-0">
                                Create and manage equipment safety protocols
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Incidents Card (Quick link) -->
            <div class="col-md-6 col-lg-4">
                <a href="/LabSync-System/incidents" class="text-decoration-none">
                    <div class="card h-100 admin-tile">
                        <div class="card-body text-center p-4">
                            <div class="admin-icon bg-secondary-subtle text-secondary mb-3">
                                <i class="bi bi-clipboard2-pulse-fill"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Incident Reports</h5>
                            <p class="text-muted small mb-0">
                                Review and submit emergency incident reports
                            </p>
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </div>
</main>

<style>
    .admin-tile {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 1px solid var(--ls-border);
    }

    .admin-tile:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(39, 128, 227, 0.15);
        border-color: var(--ls-primary);
    }

    .admin-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto;
    }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>