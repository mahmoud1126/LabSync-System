<?php
require_once __DIR__ . '/../core/BaseController.php';
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
        $userID = $_SESSION['userID'];
        $userRole = $_SESSION['userType'];
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
}