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

    /**
     * Main Index Router:
     * Redirects users to the correct view based on their role
     */
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

    /**
     * Lab Manager Equipment View
     */
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

    /**
     * Researcher & Guest Researcher Booking View
     */
    public function researcherIndex() {
        $this->requireRole(['researcher', 'guest_researcher']);
        
        $userID = $this->getCurrentUserID();
        $userRole = $this->getCurrentUserRole();
        $allEquipment = $this->equipmentModel->getAllEquipment();

        foreach ($allEquipment as &$eq) {
            $clearanceCheck = $this->clearanceModule->verifyAccess($userID, $eq['equipmentID']);
            $eq['hasAccess'] = $clearanceCheck['success'];
            $eq['accessMessage'] = $clearanceCheck['message'];
            
            $eq['depsReady'] = $this->equipmentModel->areDependenciesAvailable($eq['equipmentID']);
            
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
        // Basic security check for researcher roles
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
        $this->view('equipment/edit', ['equipment' => $equipmentInfo]);
    }

    /**
     * Processes creation including Safety Briefings and Buffer Minutes
     */
    public function store() { 
        $this->requireRole('lab_manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Passes $_POST containing briefingContent, powerUpBufferMinutes, and coolDownBufferMinutes
            $success = $this->equipmentModel->createEquipment($_POST);
            
            if ($success) {
                $this->redirect('/equipment');
            } else {
                echo "Failed to create equipment and safety briefing.";
            }
        }
    }

    /**
     * Processes updates including Safety Briefings and Buffer Minutes
     */
    public function update($id) { 
        $this->requireRole('lab_manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->equipmentModel->updateEquipment($id, $_POST);
            
            if ($success) {
                $this->redirect('/equipment');
            } else {
                echo "Failed to update equipment details.";
            }
        }
    }

    /**
     * Processes deletion
     * (SafetyBriefings are removed automatically via SQL ON DELETE CASCADE)
     */
    public function delete($id) { 
        $this->requireRole('lab_manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->equipmentModel->deleteEquipment($id);
            
            if ($success) {
                $this->redirect('/equipment');
            } else {
                echo "Failed to delete equipment.";
            }
        }
    }
}