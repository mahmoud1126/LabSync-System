<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Researcher.php';
require_once __DIR__ . '/../models/Booking.php';

class ResearcherController extends BaseController {
    private $researcherModel;
    private $bookingModel;

    public function __construct() {
        $this->requireRole(['researcher', 'guest_researcher']);
        $this->researcherModel = new Researcher();
        $this->bookingModel = new Booking();
    }

    public function index() {
        $userID = $this->getCurrentUserID();
        $user = $this->getCurrentUser();
        $role = $this->getCurrentUserRole();

        $data = [
            'title' => 'Researcher Dashboard',
            'userName' => $user['userName'] ?? 'Researcher',
            'currentRole' => $role,
            'currentUser' => $user,
            'totalSpent' => $this->researcherModel->getResearcherTotalSpending($userID),
            'assignedGrants' => $this->researcherModel->getResearcherGrants($userID),
            'recentBookings' => $this->bookingModel->getBookingsByUserId($userID)
        ];

        $this->view('Researcher/ResearcherDashboard', $data);
    }
}