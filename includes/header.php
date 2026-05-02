<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION['user']['userName'] ?? '';
$userRole = $_SESSION['user']['userType'] ?? '';
$currentPage = $_GET['url'] ?? '';
$currentPage = trim(str_replace('LabSync-System', '', $currentPage), '/');
$currentPage = explode('/', $currentPage)[0] ?? '';

function isActive($page, $current) {
    return $page === $current ? 'nav-link-active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabSync<?php echo isset($pageTitle) ? " | $pageTitle" : ''; ?></title>

    <!-- Bootswatch Cosmo + Bootstrap Icons + Custom CSS -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg topbar sticky-top">
    <div class="container-fluid px-4">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none"
           href="/LabSync-System/dashboard">
            <div class="logo-mark">LS</div>
            <span class="logo-text">Lab<span>Sync</span></span>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('dashboard', $currentPage) ?>"
                       href="/LabSync-System/dashboard">
                       <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('equipment', $currentPage) ?>"
                       href="/LabSync-System/equipment">
                       <i class="bi bi-tools me-1"></i> Equipment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('bookings', $currentPage) ?>"
                       href="/LabSync-System/bookings">
                       <i class="bi bi-calendar-check me-1"></i> Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('grants', $currentPage) ?>"
                       href="/LabSync-System/grants">
                       <i class="bi bi-cash-stack me-1"></i> Grants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('incidents', $currentPage) ?>"
                       href="/LabSync-System/incidents">
                       <i class="bi bi-exclamation-triangle me-1"></i> Incidents
                    </a>
                </li>

                <?php if (in_array($userRole, ['lab_manager'])): ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('admin', $currentPage) ?>"
                       href="/LabSync-System/admin">
                       <i class="bi bi-shield-lock me-1"></i> Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right side: notifications + user -->
            <div class="d-flex align-items-center gap-3">

                <a href="/LabSync-System/notifications" class="notif-btn position-relative text-decoration-none">
                    <i class="bi bi-bell"></i>
                    <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= (int) $unreadCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if ($userName): ?>
                <div class="dropdown">
                    <a href="#"
                       class="d-flex align-items-center gap-2 text-decoration-none"
                       data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?= strtoupper(substr($userName, 0, 2)) ?>
                        </div>
                        <div class="lh-1 d-none d-md-block">
                            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                            <div class="user-role"><?= htmlspecialchars(str_replace('_', ' ', $userRole)) ?></div>
                        </div>
                        <i class="bi bi-chevron-down text-muted small"></i>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <a class="dropdown-item" href="/LabSync-System/profile">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/LabSync-System/certifications">
                                <i class="bi bi-shield-check me-2"></i> Certifications
                            </a>
                        </li>

                        <?php if ($userRole === 'faculty_pi'): ?>
                        <li>
                            <a class="dropdown-item" href="/LabSync-System/approvals">
                                <i class="bi bi-check2-circle me-2"></i> Approvals
                            </a>
                        </li>
                        <?php endif; ?>

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/LabSync-System/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>

<main>
    <div class="container">