<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION['user']['userName'] ;
$userRole = $_SESSION['user']['userType'] ;
$currentPage = $_GET['url'] ;
$currentPage = trim(str_replace('LabSync-System', '', $currentPage), '/');
$currentPage = explode('/', $currentPage)[0] ;

function isActive($page, $current) {
    return $page === $current ? 'nav-link-active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LabSync<?php echo isset($pageTitle) ? " | $pageTitle" : ''; ?></title>

    <link href="/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg topbar sticky-top">
    <div class="container-fluid px-4">

        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none"
           href="/LabSync-System/dashboard">
            <div class="logo-mark">LS</div>
            <span class="logo-text">Lab<span>Sync</span></span>
        </a>

        <div class="collapse navbar-collapse" id="mainNav">

            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('dashboard', $currentPage) ?>"
                       href="/LabSync-System/dashboard">
                       Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('equipment', $currentPage) ?>"
                       href="/LabSync-System/equipment">
                       Equipment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('bookings', $currentPage) ?>"
                       href="/LabSync-System/bookings">
                       Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('sessions', $currentPage) ?>"
                       href="/LabSync-System/sessions/active">
                       Sessions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('grants', $currentPage) ?>"
                       href="/LabSync-System/grants">
                       Grants
                    </a>
                </li>
                <?php if ($userRole === 'lab_manager'): ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('incidents', $currentPage) ?>"
                       href="/LabSync-System/incidents">
                       Incidents
                    </a>
                </li>

                <?php if (in_array($userRole, ['lab_manager'])): ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= isActive('compliance', $currentPage) ?>"
                       href="/LabSync-System/compliance/pending-supervisions">
                       Supervisions
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">

                <?php if ($userName): ?>
                <div class="dropdown">
                    <a href="#"
                       class="d-flex align-items-center gap-2 text-decoration-none"
                       data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?= strtoupper(substr($userName, 0, 2)) ?>
                        </div>
                        <div class="lh-1">
                            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                            <div class="user-role"><?= htmlspecialchars(str_replace('_', ' ', $userRole)) ?></div>
                        </div>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <a class="dropdown-item text-danger" href="/LabSync-System/logout">
                                Logout
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