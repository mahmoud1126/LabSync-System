<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Researcher.php';
require_once __DIR__ . '/../models/Booking.php';

class DashboardController extends BaseController {

    public function index() {
        $this->requireLogin();
        
        $role = $this->getCurrentUserRole();
        $userID = $this->getCurrentUserID();
        $user = $this->getCurrentUser();

        // These keys MUST match the variables used in the view exactly
        $data = [
            'title' => 'LabSync Dashboard',
            'userName' => $user['userName'] ?? 'Researcher',
            'currentRole' => $role,
            'currentUser' => $user
        ];

        switch ($role) {
            case 'lab_manager':
                return $this->view('admin/index', $data);
            case 'faculty_pi':
                return $this->view('pi/dashboard', $data); 
            case 'researcher':
            case 'guest_researcher':
                $researcherModel = new Researcher();
                $bookingModel = new Booking();

                // Fetching data from your specific Researcher model methods
                $data['totalSpent'] = $researcherModel->getResearcherTotalSpending($userID);
                $data['assignedGrants'] = $researcherModel->getResearcherGrants($userID);
                $data['recentBookings'] = $bookingModel->getBookingsByUserId($userID);

                return $this->view('Researcher/ResearcherDashboard', $data); 
            default:
                $this->redirect('/login');
        }
    }
}