<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Equipment.php';
require_once __DIR__ . '/../modules/module3/SecurityClearance.php';

class EquipmentController extends BaseController {
    private $equipmentModel;
    private $clearanceModule;

    public function __construct() {
        $this->equipmentModel = new Equipment();
        $this->clearanceModule = new SecurityClearance();
    }


    public function index() {
    $this->requireLogin(); 
    $userID = $this->getCurrentUserID(); 
    $userRole = $this->getCurrentUserRole();
    $allEquipment = $this->equipmentModel->getAllEquipment();

    if ($allEquipment) {
        foreach ($allEquipment as &$eq) {
            $clearanceCheck = $this->clearanceModule->verifyAccess($userID, $eq['equipmentID']);
            $eq['hasAccess'] = $clearanceCheck['success'];
            $eq['accessMessage'] = $clearanceCheck['message'];
            
            $eq['depsReady'] = $this->equipmentModel->areDependenciesAvailable($eq['equipmentID']);
            
            $eq['canShowBookButton'] = ($userRole === 'researcher' || $userRole === 'guest_researcher') && 
                                       $eq['hasAccess'] && 
                                       ($eq['equipmentStatus'] === 'available') && 
                                       $eq['depsReady'];
        }
    } else {
        $allEquipment = [];
    }

    $this->view('researcher/equipment', [
        'equipment' => $allEquipment,
        'userRole' => $userRole
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

        // Call the model method to handle the DB transaction
        $briefing = $this->equipmentModel->createBooking($userID, $equipmentID, $startTime, $endTime);

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
}