<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/GrantTransaction.php';
require_once __DIR__ . '/../models/FacultyPI.php';
require_once __DIR__ . '/../modules/billing/GrantReallocation.php';

class GrantController extends BaseController {
    private $grantModel;
    private $transactionModel;
    private $piModel;

    public function __construct() {
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
        $this->piModel = new FacultyPI();
    }

    public function dashboard() {
        $this->requireRole(['faculty_pi']);
        $piID = $this->getCurrentUserID();
        
        $data = [
            'title' => 'Financial Overview',
            'pendingTransactions' => $this->piModel->getPendingTransactions($piID),
            'managedGrants'       => $this->piModel->getMyGrants($piID),
            'recentActivity'      => $this->transactionModel->getRecentByPI($piID) 
        ];
        
        // Updated path to 'pages'
        $this->view('grants/PI_Dashboard', $this->withSection('finance', $data));
    }

    public function reallocate() {
        $this->requireRole(['lab_manager']);
        if (!$this->isPost()) $this->redirect('/grants');

        $sourceID = $this->getPost('sourceGrantID');
        $destID = $this->getPost('destGrantID');
        $amount = (float)$this->getPost('amount');
        $userID = $this->getCurrentUserID();

        // Validation to prevent logic errors
        if ($sourceID == $destID) {
            $_SESSION['reallocation_error'] = "Source and Destination grants cannot be the same.";
            $this->redirect('/grants');
            return;
        }

        require_once __DIR__ . '/../modules/billing/GrantReallocation.php';
        $reallocationService = new GrantReallocation();
        
        if ($reallocationService->reallocate($sourceID, $destID, $amount, $userID)) {
            // Bypass setFlash and explicitly use standard session variables
            $_SESSION['reallocation_success'] = "Reallocation successfully executed: " . number_format($amount, 2) . " EGP.";
        } else {
            $_SESSION['reallocation_error'] = "Reallocation failed. Check balances and active status.";
        }

        $this->redirect('/grants');
    }

    public function index() {
        $this->requireLogin();
        $role = $this->getCurrentUserRole();
        $userID = $this->getCurrentUserID();

        if ($role === 'lab_manager') {
            $data['grants'] = $this->grantModel->getAllGrants();
            $data['activeGrants'] = $this->grantModel->getActiveGrants(); 
            return $this->view('grants/LabManagerGrantPage', $data);
        } 
        elseif ($role === 'faculty_pi') {
            $data['grants'] = $this->grantModel->getGrantsByPI($userID);
            return $this->view('grants/PIGrantPage', $data);
        } 
        else {
            $data['grants'] = $this->grantModel->getGrantsByResearcher($userID);
            return $this->view('grants/ResearcherAndGuestResearcherGrantPage', $data);
        }
    }
}