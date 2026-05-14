<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-person-plus-fill text-primary"></i> Create New User</h2>
                <p class="text-muted mb-0">Register a new user with any role in the system</p>
            </div>
            <a href="/LabSync-System/admin/users" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-pencil-square text-primary me-2"></i> User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/LabSync-System/admin/users/store" id="userForm">

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-3">Select Role *</label>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="role-card" for="role-researcher">
                                    <input type="radio" name="userType" value="researcher" id="role-researcher" class="role-radio" checked>
                                    <div class="role-card-inner">
                                        <i class="bi bi-person-badge-fill text-primary"></i>
                                        <h6 class="mb-1 mt-2">Researcher</h6>
                                        <small class="text-muted">Internal, permanent</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="role-card" for="role-guest">
                                    <input type="radio" name="userType" value="guest_researcher" id="role-guest" class="role-radio">
                                    <div class="role-card-inner">
                                        <i class="bi bi-person-vcard-fill text-warning"></i>
                                        <h6 class="mb-1 mt-2">Guest Researcher</h6>
                                        <small class="text-muted">External, time-limited</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="role-card" for="role-manager">
                                    <input type="radio" name="userType" value="lab_manager" id="role-manager" class="role-radio">
                                    <div class="role-card-inner">
                                        <i class="bi bi-shield-fill-check text-info"></i>
                                        <h6 class="mb-1 mt-2">Lab Manager</h6>
                                        <small class="text-muted">Admin privileges</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="role-card" for="role-pi">
                                    <input type="radio" name="userType" value="faculty_pi" id="role-pi" class="role-radio">
                                    <div class="role-card-inner">
                                        <i class="bi bi-mortarboard-fill text-success"></i>
                                        <h6 class="mb-1 mt-2">Faculty PI</h6>
                                        <small class="text-muted">Principal Investigator</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div id="roleInfoBanner" class="alert alert-info d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <small id="roleInfoText">
                            <strong>Researcher:</strong> Permanent internal account with standard lab access.
                        </small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="userName" class="form-label fw-semibold">Username *</label>
                            <input type="text" class="form-control" id="userName" name="userName" placeholder="e.g. john.doe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="userPassword" class="form-label fw-semibold">Password *</label>
                            <input type="password" class="form-control" id="userPassword" name="userPassword" placeholder="Strong password" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="clearanceLevel" class="form-label fw-semibold">Clearance Level *</label>
                            <select class="form-select" id="clearanceLevel" name="clearanceLevel" required>
                                <option value="0">Level 0 — None</option>
                                <option value="1">Level 1 — Basic</option>
                                <option value="2">Level 2 — Standard</option>
                                <option value="3">Level 3 — Advanced</option>
                                <option value="4">Level 4 — Restricted</option>
                                <option value="5">Level 5 — Dual-Use</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="maxBookingHoursPerWeek" class="form-label fw-semibold">Max Hours / Week</label>
                            <input type="number" class="form-control" id="maxBookingHoursPerWeek" name="maxBookingHoursPerWeek" value="20" min="1" max="168">
                        </div>
                    </div>

                    <div id="guestFields" class="conditional-fields" style="display: none;">
                        <hr>
                        <div class="alert alert-warning d-flex align-items-center mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <small><strong>Guest Researcher details required below.</strong></small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="institution" class="form-label fw-semibold">Institution</label>
                                <input type="text" class="form-control" id="institution" name="institution" placeholder="e.g. MIT">
                            </div>
                            <div class="col-md-6">
                                <label for="sponsorPIID" class="form-label fw-semibold">Sponsor PI</label>
                                <select class="form-select" id="sponsorPIID" name="sponsorPIID">
                                    <option value="">— Select Sponsor —</option>
                                    <?php if (!empty($piUsers)): foreach ($piUsers as $pi): ?>
                                        <option value="<?= $pi['userID'] ?>">#<?= $pi['userID'] ?> — <?= htmlspecialchars($pi['userName']) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expirationDate" class="form-label fw-semibold">Expiration Date</label>
                                <input type="date" class="form-control" id="expirationDate" name="expirationDate">
                            </div>
                            <div class="col-md-6">
                                <label for="taxRate" class="form-label fw-semibold text-danger">Tax Rate (Premium)</label>
                                <select class="form-select border-danger" id="taxRate" name="taxRate">
                                    <?php for($i = 100; $i <= 200; $i += 10): ?>
                                        <option value="<?= $i ?>"><?= $i ?>%</option>
                                    <?php endfor; ?>
                                </select>
                                <small class="text-danger">DEBUG: TAX RATE SHOULD BE HERE</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/LabSync-System/admin/users" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create User</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<style>
    .role-card { display: block; cursor: pointer; margin: 0; height: 100%; }
    .role-radio { display: none; }
    .role-card-inner {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 18px 12px;
        text-align: center;
        transition: all 0.2s ease;
        height: 100%;
    }
    .role-card-inner i { font-size: 2rem; }
    .role-radio:checked+.role-card-inner {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.08);
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="userType"]');
        const guestBlock = document.getElementById('guestFields');
        const infoBanner = document.getElementById('roleInfoBanner');
        const infoText = document.getElementById('roleInfoText');

        const roleInfo = {
            'researcher': { text: '<strong>Researcher:</strong> Internal account.', cssClass: 'alert-info' },
            'guest_researcher': { text: '<strong>Guest Researcher:</strong> External account.', cssClass: 'alert-warning' },
            'lab_manager': { text: '<strong>Lab Manager:</strong> Admin account.', cssClass: 'alert-info' },
            'faculty_pi': { text: '<strong>Faculty PI:</strong> PI account.', cssClass: 'alert-success' }
        };

        function applyRoleVisibility() {
            const selectedRole = document.querySelector('input[name="userType"]:checked').value;
            const isGuest = selectedRole === 'guest_researcher';
            guestBlock.style.display = isGuest ? 'block' : 'none';
            
            const info = roleInfo[selectedRole];
            infoBanner.className = 'alert ' + info.cssClass + ' d-flex align-items-center mb-3';
            infoText.innerHTML = info.text;

            // Update required fields
            document.getElementById('institution').required = isGuest;
            document.getElementById('sponsorPIID').required = isGuest;
            document.getElementById('expirationDate').required = isGuest;
        }

        radios.forEach(radio => radio.addEventListener('change', applyRoleVisibility));
        applyRoleVisibility();
    });
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>