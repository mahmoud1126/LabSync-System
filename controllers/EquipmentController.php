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
        // Using built-in BaseController methods to get user data
        $userID = $this->getCurrentUserID();
        $userRole = $this->getCurrentUserRole();
        
        $allEquipment = $this->equipmentModel->getAllEquipment();

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

        $this->view('equipment/index', [
            'equipment' => $allEquipment,
            'userRole' => $userRole
        ]);
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