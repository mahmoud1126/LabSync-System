<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_type'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

?>


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LabSync<?php echo isset($pageTitle) ? " | $pageTitle" : ''; ?></title>
    
    <!-- css files links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/LabSync-System/css/style.css" rel="stylesheet">

</head>


<body>

<nav class="navbar navbar-expand-lg topbar sticky-top">


    <div class="container-fluid px-4">

        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="dashboard.php">

            <div class="logo-mark">LS</div>
            <span class="logo-text">Lab<span>Sync</span></span>

        </a>


        <div class="d-flex align-items-center gap-4">

            <ul class="navbar-nav gap-1">

                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo $currentPage === 'dashboard' ? 'nav-link-active' : ''; ?>"
                       href="dashboard.php">
                        Dashboard
                    </a>    
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo $currentPage === 'equipment' ? 'nav-link-active' : ''; ?>"
                       href="equipment.php">
                        Equipment
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo $currentPage === 'booking' ? 'nav-link-active' : ''; ?>"
                       href="booking.php">
                        Bookings
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo $currentPage === 'grants' ? 'nav-link-active' : ''; ?>"
                       href="grants.php">
                        Grants
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo $currentPage === 'certifications' ? 'nav-link-active' : ''; ?>"
                       href="certifications.php">
                        Safety
                    </a>
                </li>


                <?php if (in_array($userRole, ['lab_manager', 'admin'])): ?>

                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo $currentPage === 'admin_dashboard' ? 'nav-link-active' : ''; ?>"
                       href="admin_dashboard.php">
                        Admin
                    </a>
                </li>
                <?php endif; ?>

            </ul>






            <div class="d-flex align-items-center gap-3">

                <a href="notifications.php" class="notif-btn position-relative text-decoration-none">
                    
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>

                </a>


                <div class="dropdown">

                    <a href="#"
                       class="d-flex align-items-center gap-2 text-decoration-none"
                       data-bs-toggle="dropdown">

                        <div class="user-avatar">
                            <?php echo strtoupper(substr($userName ?: 'U', 0, 2)); ?>
                        </div>


                        <div class="lh-1">
                            <div class="user-name">
                                <?php echo htmlspecialchars($userName); ?>
                            </div>
                        </div>

                        <i class="bi bi-chevron-down text-muted small"></i>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                    
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="bi bi-person me-2"></i>Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="certifications.php">
                                <i class="bi bi-shield-check me-2"></i>Certifications
                            </a>
                        </li>

                        <?php if ($userRole === 'faculty_pi'): ?>
                        <li>
                            <a class="dropdown-item" href="pi_approval.php">
                                <i class="bi bi-check2-circle me-2"></i>Approvals
                            </a>
                        </li>
                        <?php endif; ?>

                        <li>
                            <a class="dropdown-item text-danger"
                               href="../controllers/AuthController.php?action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

</nav>
<main>
