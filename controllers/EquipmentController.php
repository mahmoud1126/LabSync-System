<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Equipment.php';
require_once __DIR__ . '/../modules/module3/SecurityClearance.php';

class EquipmentController extends BaseController {
    private $equipmentModel;
    private $clearanceModule;
    private $userModel;

    public function __construct() {
        $this->equipmentModel = new Equipment();
        $this->clearanceModule = new SecurityClearance();
        $this->userModel = new Researcher();
    }

    public function index() {
        $this->requireLogin();
        $userRole = $this->getCurrentUserRole();

        if ($userRole === 'lab_manager') {
            $this->managerIndex();
        } else if ($userRole === 'researcher' || $userRole === 'guest_researcher') {
            $this->researcherIndex();
        } else {
            $this->redirect('/dashboard');
        }
    }

    private function managerIndex() {
        $this->requireRole('lab_manager');
        $allEquipment = $this->equipmentModel->getAllEquipment();

        foreach ($allEquipment as &$eq) {
            $eq['hasAccess'] = true;
            $eq['accessMessage'] = 'Full Administrative Access';
            $eq['canShowBookButton'] = false;
        }

        $this->view('equipment/index', [
            'equipment' => $allEquipment,
            'userRole' => 'lab_manager'
        ]);
    }

    public function researcherIndex() {
        $this->requireRole(['researcher', 'guest_researcher']);
        
        $userID = $this->getCurrentUserID();
        $userRole = $this->getCurrentUserRole();
        $allEquipment = $this->equipmentModel->getAllEquipment();

        foreach ($allEquipment as &$eq) {
            $clearanceCheck = $this->clearanceModule->verifyAccess($userID, $eq['equipmentID']);
            $eq['hasAccess'] = $clearanceCheck['success'];
            $eq['accessMessage'] = $clearanceCheck['message'];

            // Sequential Booking Dependency: expose the linked secondary equipment
            // so the researcher can see what will be auto-booked alongside.
            $eq['dependencies'] = $this->equipmentModel->getDependencies($eq['equipmentID']);
            $eq['depsReady']    = $this->equipmentModel->areDependenciesAvailable($eq['equipmentID']);

            $eq['canShowBookButton'] = $eq['hasAccess'] &&
                                       ($eq['equipmentStatus'] === 'available') &&
                                       $eq['depsReady'];
        }

         $this->view('Researcher/ResearcherEquipment', [
        'equipments' => $allEquipment,
        'userRole' => $userRole,
        'user' => $this->userModel->getUserByID($userID)
    ]);
}

    public function book() {
        if (!in_array($_SESSION['userType'], ['researcher', 'guest_researcher'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            return;
        }

        $equipmentID = $_POST['equipmentID'] ?? null;
        $userID = $_SESSION['userID'];
        $startTime = $_POST['startTime'] ?? null;
        $endTime = $_POST['endTime'] ?? null;

        if (!$equipmentID || !$startTime || !$endTime) {
            echo json_encode(['success' => false, 'message' => 'Please provide all booking details.']);
            return;
        }

        $bookingModel = new Booking();
        $briefing = $bookingModel->createBooking($userID, $equipmentID, $startTime, $endTime); 

        if ($briefing === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to process booking.']);
        } else {
            echo json_encode([
                'success' => true,
                'showBriefing' => (bool)$briefing,
                'briefingContent' => $briefing['briefingContent'] ?? ''
            ]);
        }
    }

    public function acknowledgeSafety() {
        if (!$this->isPost()) return;

        $userID = $this->getCurrentUserID();
        $equipmentID = $this->getPost('equipmentID');

        require_once __DIR__ . '/../models/Researcher.php';
        $researcherModel = new Researcher();
        
        $success = $researcherModel->saveSafetyAcknowledgment($userID, $equipmentID);

        echo json_encode(['success' => $success]);
    }

    public function info($id) {
        $equipmentInfo = $this->equipmentModel->getEquipmentById($id);
        $this->view('equipment/info', ['equipment' => $equipmentInfo]);
    }

    public function create() {
        $this->requireRole('lab_manager');
        $this->view('equipment/create');
    }

    public function edit($id) {
        $this->requireRole('lab_manager');
        $equipmentInfo = $this->equipmentModel->getEquipmentById($id);

        // Secondary Equipment feature: load existing dependencies + selectable pool
        $dependencies   = $this->equipmentModel->getDependencies($id);
        $availablePool  = $this->equipmentModel->getAllEquipmentExcept($id);

        // Filter out already-linked secondary equipment from the dropdown pool
        $linkedIDs = array_column($dependencies, 'equipmentID');
        $availablePool = array_values(array_filter($availablePool, function ($eq) use ($linkedIDs) {
            return !in_array($eq['equipmentID'], $linkedIDs);
        }));

        $this->view('equipment/edit', [
            'equipment'     => $equipmentInfo,
            'dependencies'  => $dependencies,
            'availablePool' => $availablePool
        ]);
    }

    public function addDependency($id) {
        $this->requireRole('lab_manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/equipment/edit/' . $id);
            return;
        }

        $secondaryID = (int) ($_POST['secondaryEquipmentID'] ?? 0);

        if ($secondaryID <= 0 || $secondaryID == $id) {
            $this->setFlash('error', 'Invalid secondary equipment selection.');
            $this->redirect('/equipment/edit/' . $id);
            return;
        }

        $success = $this->equipmentModel->addDependency($id, $secondaryID);
        $this->setFlash(
            $success ? 'success' : 'error',
            $success ? 'Secondary equipment linked successfully.' : 'Failed to link secondary equipment.'
        );
        $this->redirect('/equipment/edit/' . $id);
    }

    public function removeDependency($id) {
        $this->requireRole('lab_manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/equipment/edit/' . $id);
            return;
        }

        $secondaryID = (int) ($_POST['secondaryEquipmentID'] ?? 0);

        if ($secondaryID <= 0) {
            $this->setFlash('error', 'Invalid secondary equipment.');
            $this->redirect('/equipment/edit/' . $id);
            return;
        }

        $success = $this->equipmentModel->removeDependency($id, $secondaryID);
        $this->setFlash(
            $success ? 'success' : 'error',
            $success ? 'Secondary equipment unlinked.' : 'Failed to unlink secondary equipment.'
        );
        $this->redirect('/equipment/edit/' . $id);
    }

    public function store() { 
        $this->requireRole('lab_manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->equipmentModel->createEquipment($_POST);
            if ($success) {
                $this->setFlash('success', 'Equipment successfully created!');
                $this->redirect('/equipment');
            } else {
                $this->setFlash('error', 'Failed to create equipment.');
                $this->redirect('/equipment');
            }
        }
    }

    public function update($id) { 
        $this->requireRole('lab_manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->equipmentModel->updateEquipment($id, $_POST);
            
            if ($success) {
                $this->setFlash('success', 'Equipment updated successfully!');
                $this->redirect('/equipment');
            } else {
                $this->setFlash('error', 'Failed to update equipment details.');
                $this->redirect('/equipment');
            }
        }
    }

    public function delete($id) { 
        $this->requireRole('lab_manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->equipmentModel->deleteEquipment($id);
            
            if ($success) {
                $this->setFlash('success', 'Equipment successfully deleted.');
                $this->redirect('/equipment');
            } else {
                $this->setFlash('error', 'Failed to delete equipment.');
                $this->redirect('/equipment');
            }
        }
    }
}