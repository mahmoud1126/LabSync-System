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
        // Use BaseController's role protection
        $this->requireRole('lab_manager');
        $this->view('equipment/create');
    }

    public function edit($id) {
        $this->requireRole('lab_manager');
        $equipmentInfo = $this->equipmentModel->getEquipmentById($id);
        $this->view('equipment/edit', ['equipment' => $equipmentInfo]);
    }

    public function delete($id) {
        $this->requireRole('lab_manager');
        
        if ($this->equipmentModel->deleteEquipment($id)) {
            $this->setFlash('success', 'Equipment deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete equipment.');
        }

        // Use BaseController's redirect method
        $this->redirect('/equipment');
    }
// Save new equipment
 public function store() {
        $this->requireRole('lab_manager');
        $imageName = $this->handleUpload();

        $data = [
            'equipmentName' => $this->getPost('equipmentName'),
            'equipmentStatus' => $this->getPost('equipmentStatus'),
            'hourlyRateExternal' => $this->getPost('hourlyRateExternal'),
            'requiredClearanceLevel' => $this->getPost('requiredClearanceLevel')
        ];

        $this->equipmentModel->createEquipment($data) ? 
            $this->setFlash('success', 'Equipment added!') : $this->setFlash('error', 'Failed to add.');
        
        $this->redirect('/equipment');
    }

    public function update($id) {
        $this->requireRole('lab_manager');
        $existing = $this->equipmentModel->getEquipmentById($id);

        $data = [
            'equipmentName' => $this->getPost('equipmentName'),
            'equipmentStatus' => $this->getPost('equipmentStatus'),
            'hourlyRateExternal' => $this->getPost('hourlyRateExternal'),
            'requiredClearanceLevel' => $this->getPost('requiredClearanceLevel')
        ];

        $this->equipmentModel->updateEquipment($id, $data) ? 
            $this->setFlash('success', 'Updated!') : $this->setFlash('error', 'Update failed.');
        
        $this->redirect('/equipment');
    }

    private function handleUpload() {
        if (!empty($_FILES['equipmentImage']['name'])) {
            $name = time() . '_' . $_FILES['equipmentImage']['name'];
            $target = __DIR__ . '/../../public/uploads/equipment/' . $name;
            if (move_uploaded_file($_FILES['equipmentImage']['tmp_name'], $target)) return $name;
        }
        return null;
    }
    
}