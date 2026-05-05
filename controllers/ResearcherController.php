<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Researcher.php';

class ResearcherController extends BaseController {
    private $researcherModel;

    public function __construct() {
        // Ensure only researchers or guest researchers can access
        $this->requireRole(['researcher', 'guest_researcher']); 
        $this->researcherModel = new Researcher();
    }

    public function myGrants() {
        $userID = $this->getCurrentUserID();
        
        // Using the methods you provided in Researcher.php
        $data = [
            'title' => 'My Funding & Spending',
            'assignedGrants' => $this->researcherModel->getResearcherGrants($userID),
            'myTransactions' => $this->researcherModel->getResearcherTransactions($userID),
            'totalSpent' => $this->researcherModel->getResearcherTotalSpending($userID)
        ];

        $this->view('researcher/my_grants', $data);
    }
}