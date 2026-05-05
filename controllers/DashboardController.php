<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Users.php';
require_once __DIR__ . '/../models/IncidentLog.php';


class UserModel extends User { public function getRole() { return $_SESSION['userType']; } }

class DashboardController extends BaseController {
    private $userModel;
    private $incidentModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->incidentModel = new IncidentLog();
    }

    public function index() {
        $this->requireLogin();
        $role = $this->getCurrentUserRole();
        
        $data = [
            'title' => 'LabSync Dashboard',
            'user' => $this->getCurrentUser()
        ];

        switch ($role) {
            case 'lab_manager':
                return $this->view('admin/index', $data);
            case 'faculty_pi':
                return $this->view('pi/dashboard', $data); 
            case 'researcher':
            case 'guest_researcher':
                return $this->view('researcher/dashboard', $data); 
            default:
                $this->redirect('/login');
        }
    }

}