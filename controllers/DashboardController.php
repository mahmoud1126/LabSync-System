<?php
require_once __DIR__ . '/../core/BaseController.php';
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
        $userID = $_SESSION['userID'];
        $role = $_SESSION['userType'];
        
        $data = [
            'role' => $role,
            'safetyBriefing' => $this->userModel->hasSafetyBriefingAcknowledged($userID),
            'userInfo' => $this->userModel->getUserById($userID)
        ];

        if ($role === 'lab_manager') {
            $data['totalUsers'] = count($this->userModel->getAllUsers());
            $data['allIncidents'] = $this->incidentModel->getAllIncidents();
        } else {
            $hoursData = $this->userModel->getCurrentWeeklyBookedHours($userID);
            $data['bookedHours'] = $hoursData['currentWeeklyBookedHours'] ?? 0;
            $data['maxHours'] = $hoursData['maxBookingHoursPerWeek'] ?? 20;
            $data['myIncidents'] = $this->incidentModel->getIncidentsByUserID($userID);
        }

        $this->view('dashboard/index', $data);
    }
}