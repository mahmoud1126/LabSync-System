<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Researcher.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/FacultyPi.php';

class DashboardController extends BaseController {

    public function index() {
        $this->requireLogin();
        
        $role = $this->getCurrentUserRole();
        $userID = $this->getCurrentUserID();
        $user = $this->getCurrentUser();

        $data = [
            'title' => 'LabSync Dashboard',
            'userName' => $user['userName'] ?? 'Researcher',
            'currentRole' => $role,
            'currentUser' => $user
        ];

        switch ($role) {
            case 'lab_manager':
                $this->redirect('/admin');
                break;

            case 'faculty_pi':
                // Redirect to the PIController's index method to properly load all dashboard data
                $this->redirect('/pi'); 
                break;

            case 'researcher':
            case 'guest_researcher':
                $researcherModel = new Researcher();
                $bookingModel = new Booking();

                $data['totalSpent'] = $researcherModel->getResearcherTotalSpending($userID);
                $data['assignedGrants'] = $researcherModel->getResearcherGrants($userID);
                $data['recentBookings'] = $bookingModel->getBookingsByUserId($userID);

                return $this->view('Researcher/ResearcherDashboard', $data); 

            default:
                $this->redirect('/login');
        }
    }
}