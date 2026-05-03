<?php

require_once __DIR__ . "/../core/Controller.php";

class AdminController extends BaseController
{


    public function index()
    {
        $this->requireRole(['lab_manager']);
        $this->view('admin/index');
    }


    public function users()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $users = $userModel->getAllUsers();

        $this->view('admin/users/index', ['users' => $users]);
    }

    public function showUser($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Researcher.php';
        require_once __DIR__ . '/../models/GuestResearcher.php';

        $userModel = new Researcher();
        $user = $userModel->getUserById($id);

        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }


        $guestData = null;
        if ($user['userType'] === 'guest_researcher') {
            $guestModel = new GuestResearcher();
            $guestData = $guestModel->getGuestResearcherById($id);
        }

        $this->view('admin/users/show', [
            'user' => $user,
            'guestData' => $guestData,
        ]);
    }

    public function createUser()
    {
        $this->requireRole(['lab_manager']);


        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $piUsers = $userModel->getUsersByType('faculty_pi');

        $this->view('admin/users/create', ['piUsers' => $piUsers]);
    }

    public function storeUser()
    {
        $this->requireRole(['lab_manager']);

        $userType = $this->getPost('userType');
        $userName = $this->getPost('userName');
        $userPassword = $this->getPost('userPassword');
        $clearanceLevel = (int) $this->getPost('clearanceLevel', 0);
        $maxBookingHoursPerWeek = (int) $this->getPost('maxBookingHoursPerWeek', 20);


        if (empty($userName) || empty($userPassword) || empty($userType)) {
            $this->setFlash('error', 'Username, password, and role are required.');
            $this->redirect('/admin/users/create');
            return;
        }

        $allowedRoles = ['researcher', 'guest_researcher', 'lab_manager', 'faculty_pi'];
        if (!in_array($userType, $allowedRoles, true)) {
            $this->setFlash('error', 'Invalid role selected.');
            $this->redirect('/admin/users/create');
            return;
        }


        switch ($userType) {

            case 'guest_researcher':
                require_once __DIR__ . '/../modules/module3/GuestOnboarding.php';

                $expirationDate = $this->getPost('expirationDate');
                $institution = $this->getPost('institution');
                $sponsorPIID = (int) $this->getPost('sponsorPIID');

                if (empty($expirationDate) || empty($institution) || empty($sponsorPIID)) {
                    $this->setFlash('error', 'Guest researchers require expiration date, institution, and sponsor PI.');
                    $this->redirect('/admin/users/create');
                    return;
                }

                $onboarding = new GuestOnboarding();
                $result = $onboarding->onboard([
                    'userName' => $userName,
                    'userPassword' => $userPassword,
                    'institution' => $institution,
                    'expirationDate' => $expirationDate,
                    'sponsorPIID' => $sponsorPIID,
                    'clearanceLevel' => $clearanceLevel,
                    'maxBookingHoursPerWeek' => $maxBookingHoursPerWeek,
                ]);

                $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);

                if ($result['success']) {
                    $this->logAction(
                        'USER_CREATED',
                        'Users',
                        "New guest researcher created: {$userName} (expires {$expirationDate})"
                    );
                    $this->redirect('/admin/users');
                } else {
                    $this->redirect('/admin/users/create');
                }
                return;


            case 'lab_manager':
                require_once __DIR__ . '/../models/LabManager.php';

                try {
                    $labManagerModel = new LabManager();
                    $newUserID = $labManagerModel->createLabManager(
                        $userName,
                        $userPassword,
                        $clearanceLevel,
                        $maxBookingHoursPerWeek
                    );

                    $this->logAction(
                        'USER_CREATED',
                        'Users',
                        "New lab manager created: {$userName}",
                        (int) $newUserID
                    );

                    $this->setFlash('success', "Lab Manager '{$userName}' created successfully.");
                    $this->redirect('/admin/users');
                } catch (PDOException $e) {
                    $this->setFlash('error', 'Failed to create lab manager: ' . $e->getMessage());
                    $this->redirect('/admin/users/create');
                }
                return;


            case 'faculty_pi':
                require_once __DIR__ . '/../models/FacultyPI.php';

                try {
                    $piModel = new FacultyPI();
                    $newUserID = $piModel->createFacultyPI(
                        $userName,
                        $userPassword,
                        $clearanceLevel,
                        $maxBookingHoursPerWeek
                    );

                    $this->logAction(
                        'USER_CREATED',
                        'Users',
                        "New faculty PI created: {$userName}",
                        (int) $newUserID
                    );

                    $this->setFlash('success', "Faculty PI '{$userName}' created successfully.");
                    $this->redirect('/admin/users');
                } catch (PDOException $e) {
                    $this->setFlash('error', 'Failed to create faculty PI: ' . $e->getMessage());
                    $this->redirect('/admin/users/create');
                }
                return;


            case 'researcher':
            default:
                require_once __DIR__ . '/../models/Researcher.php';

                try {
                    $userModel = new Researcher();
                    $newUserID = $userModel->createResearcher(
                        $userName,
                        $userPassword,
                        $clearanceLevel,
                        false,                          // Researchers via this branch are NOT external
                        $maxBookingHoursPerWeek
                    );

                    $this->logAction(
                        'USER_CREATED',
                        'Users',
                        "New internal researcher created: {$userName}",
                        (int) $newUserID
                    );

                    $this->setFlash('success', "Researcher '{$userName}' created successfully.");
                    $this->redirect('/admin/users');
                } catch (PDOException $e) {
                    $this->setFlash('error', 'Failed to create user: ' . $e->getMessage());
                    $this->redirect('/admin/users/create');
                }
                return;
        }
    }

    public function updateUserStatus($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $user = $userModel->getUserById($id);

        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $newStatus = $this->getPost('userStatus');
        $allowedStatus = ['active', 'inactive', 'suspended'];

        if (!in_array($newStatus, $allowedStatus, true)) {
            $this->setFlash('error', 'Invalid status value.');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $oldStatus = $user['userStatus'];
        $userModel->updateStatus($id, $newStatus);

        $this->logAction(
            'USER_STATUS_UPDATED',
            'Users',
            "User status changed from '{$oldStatus}' to '{$newStatus}'.",
            (int) $id,
            json_encode(['userStatus' => $oldStatus]),
            json_encode(['userStatus' => $newStatus])
        );

        $this->setFlash('success', 'User status updated.');
        $this->redirect('/admin/users/' . $id);
    }

    public function updateUserClearance($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $user = $userModel->getUserById($id);

        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $newClearance = (int) $this->getPost('clearanceLevel');

        if ($newClearance < 0 || $newClearance > 5) {
            $this->setFlash('error', 'Clearance level must be between 0 and 5.');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $oldClearance = $user['clearanceLevel'];
        $userModel->updateClearanceLevel($id, $newClearance);

        $this->logAction(
            'USER_CLEARANCE_UPDATED',
            'Users',
            "Clearance updated from level {$oldClearance} to level {$newClearance}.",
            (int) $id,
            json_encode(['clearanceLevel' => $oldClearance]),
            json_encode(['clearanceLevel' => $newClearance])
        );

        $this->setFlash('success', 'Clearance level updated.');
        $this->redirect('/admin/users/' . $id);
    }


    public function updateUserExpiration($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/GuestResearcher.php';
        require_once __DIR__ . '/../modules/module3/GuestOnboarding.php';

        $guestModel = new GuestResearcher();
        $guest = $guestModel->getGuestResearcherById($id);

        if (!$guest) {
            $this->setFlash('error', 'Guest researcher not found.');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $newExpirationDate = $this->getPost('expirationDate');
        if (empty($newExpirationDate)) {
            $this->setFlash('error', 'Expiration date is required.');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $onboarding = new GuestOnboarding();
        $result = $onboarding->extendExpiration(
            (int) $id,
            $newExpirationDate,
            (int) $this->getCurrentUserID()
        );

        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/admin/users/' . $id);
    }

    public function equipment()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Equipment.php';
        $equipmentModel = new Equipment();
        $equipment = $equipmentModel->getAllEquipment();

        $this->view('admin/equipment/index', ['equipment' => $equipment]);
    }

    public function showEquipment($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Equipment.php';
        $equipmentModel = new Equipment();
        $equipment = $equipmentModel->getEquipmentById($id);

        if (!$equipment) {
            $this->setFlash('error', 'Equipment not found.');
            $this->redirect('/admin/equipment');
            return;
        }

        $this->view('admin/equipment/show', ['equipment' => $equipment]);
    }

    public function updateEquipmentStatus($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Equipment.php';
        $equipmentModel = new Equipment();
        $equipment = $equipmentModel->getEquipmentById($id);

        if (!$equipment) {
            $this->setFlash('error', 'Equipment not found.');
            $this->redirect('/admin/equipment');
            return;
        }

        $newStatus = $this->getPost('equipmentStatus');
        $oldStatus = $equipment['equipmentStatus'];

        $success = $equipmentModel->updateEquipmentStatus($id, $newStatus);

        if (!$success) {
            $this->setFlash('error', 'Invalid equipment status.');
            $this->redirect('/admin/equipment/' . $id);
            return;
        }

        $this->logAction(
            'EQUIPMENT_STATUS_UPDATED',
            'Equipment',
            "Equipment status changed from '{$oldStatus}' to '{$newStatus}'.",
            (int) $id,
            json_encode(['equipmentStatus' => $oldStatus]),
            json_encode(['equipmentStatus' => $newStatus])
        );

        $this->setFlash('success', 'Equipment status updated.');
        $this->redirect('/admin/equipment/' . $id);
    }

    public function updateEquipmentRate($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Equipment.php';
        $equipmentModel = new Equipment();
        $equipment = $equipmentModel->getEquipmentById($id);

        if (!$equipment) {
            $this->setFlash('error', 'Equipment not found.');
            $this->redirect('/admin/equipment');
            return;
        }

        $newRate = (float) $this->getPost('hourlyRateExternal');
        $oldRate = (float) $equipment['hourlyRateExternal'];

        if (!$equipmentModel->updateHourlyRate($id, $newRate)) {
            $this->setFlash('error', 'Hourly rate must be greater than or equal to 0.');
            $this->redirect('/admin/equipment/' . $id);
            return;
        }

        $this->logAction(
            'EQUIPMENT_RATE_UPDATED',
            'Equipment',
            "Hourly rate changed from {$oldRate} to {$newRate}.",
            (int) $id,
            json_encode(['hourlyRateExternal' => $oldRate]),
            json_encode(['hourlyRateExternal' => $newRate])
        );

        $this->setFlash('success', 'Hourly rate updated.');
        $this->redirect('/admin/equipment/' . $id);
    }


    public function grants()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Grant.php';
        $grantModel = new Grant();
        $grants = $grantModel->getAllGrants();

        $this->view('admin/grants/index', ['grants' => $grants]);
    }

    public function showGrant($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Grant.php';
        require_once __DIR__ . '/../models/GrantTransaction.php';

        $grantModel = new Grant();
        $transactionModel = new GrantTransaction();

        $grant = $grantModel->getGrantById($id);
        if (!$grant) {
            $this->setFlash('error', 'Grant not found.');
            $this->redirect('/admin/grants');
            return;
        }

        $users = $grantModel->getUsersOnGrant($id);
        $transactions = $transactionModel->getByGrant($id);

        $this->view('admin/grants/show', [
            'grant' => $grant,
            'users' => $users,
            'transactions' => $transactions,
        ]);
    }


    public function auditLogs()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/AuditLog.php';
        $auditModel = new AuditLog();

        $actionType = $this->getQuery('actionType');
        $userID = $this->getQuery('userID');

        if (!empty($actionType)) {
            $logs = $auditModel->getLogsByActionType($actionType);
        } elseif (!empty($userID)) {
            $logs = $auditModel->getLogsByUserID((int) $userID);
        } else {
            $logs = $auditModel->getAllLogs();
        }

        $this->view('admin/logs/index', [
            'logs' => $logs,
            'filterAction' => $actionType,
            'filterUserID' => $userID,
        ]);
    }

    public function showAuditLog($id)
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/AuditLog.php';
        $auditModel = new AuditLog();
        $log = $auditModel->getLogByID($id);

        if (!$log) {
            $this->setFlash('error', 'Audit log entry not found.');
            $this->redirect('/admin/logs');
            return;
        }

        $this->view('admin/logs/show', ['log' => $log]);
    }



    public function briefings()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/SafetyBriefing.php';
        $briefingModel = new SafetyBriefing();
        $briefings = $briefingModel->getAllBriefings();

        $this->view('admin/briefings/index', ['briefings' => $briefings]);
    }

    public function createBriefing()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Equipment.php';
        $equipmentModel = new Equipment();
        $equipment = $equipmentModel->getAllEquipment();

        $this->view('admin/briefings/create', ['equipment' => $equipment]);
    }

    public function storeBriefing()
    {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/SafetyBriefing.php';

        $equipmentID = (int) $this->getPost('equipmentID');
        $briefingContent = $this->getPost('briefingContent');

        if (empty($equipmentID) || empty($briefingContent)) {
            $this->setFlash('error', 'Equipment and briefing content are required.');
            $this->redirect('/admin/briefings/create');
            return;
        }

        $briefingModel = new SafetyBriefing();
        $briefingID = $briefingModel->createBriefing($equipmentID, $briefingContent);

        $this->logAction(
            'SAFETY_BRIEFING_CREATED',
            'SafetyBriefings',
            "New safety briefing created for equipment {$equipmentID}.",
            (int) $briefingID
        );

        $this->setFlash('success', 'Safety briefing created.');
        $this->redirect('/admin/briefings');
    }
}