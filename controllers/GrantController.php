<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/GrantTransaction.php';
require_once __DIR__ . '/../models/FacultyPi.php';

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
        
        $this->view('grants/PI_Dashboard', $this->withSection('finance', $data));
    }

    // FIXED: Now creates a PENDING request for the PI instead of executing immediately
    public function reallocate() {
        $this->requireRole(['lab_manager']);
        if (!$this->isPost()) $this->redirect('/grants');

        $sourceID = $this->getPost('sourceGrantID');
        $destID = $this->getPost('destGrantID');
        $amount = (float)$this->getPost('amount');
        $userID = $this->getCurrentUserID();

        if ($sourceID == $destID) {
            $this->setFlash('error', "Source and Destination grants cannot be the same.");
            $this->redirect('/grants');
            return;
        }

        // Trick: Stash the destination ID in the description so the PI Controller can find it later
        $desc = "Pending Reallocation to Grant #$destID";
        
        $success = $this->transactionModel->createTransaction(
            $sourceID, $userID, $amount, 'reallocation_out', $desc, 0, 0, 0, null, null, 'pending'
        );

        if ($success) {
            $this->setFlash('success', "Reallocation request for $" . number_format($amount, 2) . " submitted to the PI for approval.");
        } else {
            $this->setFlash('error', "Failed to submit reallocation request.");
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
            $data['grants'] = $this->grantModel->getAllGrants();
            $data['users'] = $this->grantModel->getUsersWithGrants();
            $data['pageTitle'] = 'Grant Management';
            return $this->view('PI/PIGrantPage', $data);
        } 
        else {
            $data['grants'] = $this->grantModel->getGrantsByResearcher($userID);
            $data['pageTitle'] = 'My Assigned Grants';
            return $this->view('grants/ResearcherAndGuestResearcherGrantPage', $data);
        }
    }

    public function create() {
        $this->requireRole(['faculty_pi']);
        $this->view('PI/AddGrant', ['pageTitle' => 'Add New Grant']);
    }

    public function store() {
        $this->requireRole(['faculty_pi']);
        
        if ($this->isPost()) {
            $grantName = $this->getPost('grantName');
            $totalBudget = (float)$this->getPost('totalBudget');
            $expirationDate = $this->getPost('expirationDate');
            $piID = $this->getCurrentUserID();

            $grantID = $this->grantModel->createGrant($grantName, $piID, $totalBudget, $expirationDate);
            
            if ($grantID) {
                $this->setFlash('success', 'Grant successfully added to the system.');
            } else {
                $this->setFlash('error', 'Failed to add the grant.');
            }
            
            $this->redirect('/grants');
        }
    }

    public function assign() {
        $this->requireRole(['faculty_pi']);
        
        $users = $this->grantModel->getUsersWithGrants(); 
        $activeGrants = $this->grantModel->getActiveGrants();

        $this->view('PI/AssignGrant', [
            'pageTitle' => 'Assign Grants to Users',
            'users' => $users,
            'activeGrants' => $activeGrants
        ]);
    }

    public function processAssign() {
        $this->requireRole(['faculty_pi']);

        if ($this->isPost()) {
            $userID = (int)$this->getPost('userID');
            $grantIDs = $_POST['grantIDs'] ?? [];
            $postedPercentages = $_POST['billingPercentages'] ?? [];

            if (empty($grantIDs)) {
                $this->setFlash('error', 'Please select at least one grant to assign.');
                $this->redirect('/grants/assign');
                return;
            }

            $users = $this->grantModel->getUsersWithGrants();
            $targetRole = 'researcher';
            foreach ($users as $u) {
                if ($u['userID'] == $userID) {
                    $targetRole = $u['userType'];
                    break;
                }
            }

            $perGrantPercentages = [];

            if ($targetRole === 'lab_manager') {
                foreach ($grantIDs as $grantID) {
                    $perGrantPercentages[(int)$grantID] = 0.0;
                }
            } else {
                foreach ($grantIDs as $grantID) {
                    $gid = (int)$grantID;
                    if (!isset($postedPercentages[$gid]) || $postedPercentages[$gid] === '') {
                        $this->setFlash('error', "Please enter a billing percentage for every selected grant.");
                        $this->redirect('/grants/assign');
                        return;
                    }
                    $pct = (float)$postedPercentages[$gid];
                    if ($pct <= 0 || $pct > 100) {
                        $this->setFlash('error', "Each billing percentage must be greater than 0 and at most 100.");
                        $this->redirect('/grants/assign');
                        return;
                    }
                    $perGrantPercentages[$gid] = $pct;
                }

                $currentGrants = $this->grantModel->getGrantsForUser($userID);
                $simulatedPercentages = [];

                foreach ($currentGrants as $grant) {
                    $simulatedPercentages[(int)$grant['grantID']] = (float)$grant['billingPercentage'];
                }

                foreach ($perGrantPercentages as $gid => $pct) {
                    $simulatedPercentages[$gid] = $pct;
                }

                $newTotal = round(array_sum($simulatedPercentages), 2);

                if ($newTotal < 99.99 || $newTotal > 100.01) {
                    $this->setFlash('error', "Assignment blocked: The user's total billing would become {$newTotal}%. It must equal exactly 100%.");
                    $this->redirect('/grants/assign');
                    return;
                }
            }

            $successCount = 0;
            foreach ($perGrantPercentages as $grantID => $pct) {
                if ($this->grantModel->addUserToGrant($grantID, $userID, $pct)) {
                    $successCount++;
                }
            }

            if ($successCount > 0) {
                $this->setFlash('success', "Successfully assigned $successCount grant(s).");
            } else {
                $this->setFlash('error', 'Failed to assign grants.');
            }

            $this->redirect('/grants');
        }
    }

    public function delete() {
        $this->requireRole(['faculty_pi']);
        
        if ($this->isPost()) {
            $grantID = (int)$this->getPost('grantID');

            if ($this->grantModel->deleteGrant($grantID)) {
                $this->setFlash('success', 'Grant successfully and permanently deleted.');
            } else {
                $this->setFlash('error', 'Cannot delete grant: It is already linked to financial transactions or history. (Consider updating its status to inactive instead).');
            }
            
            $this->redirect('/grants');
        }
    }

    public function manage() {
        $this->requireRole(['faculty_pi']);
        
        $users = $this->grantModel->getUsersWithGrants(); 
        $activeGrants = $this->grantModel->getActiveGrants();

        $userMappings = [];
        foreach ($users as $u) {
            $userMappings[$u['userID']] = $this->grantModel->getGrantsForUser($u['userID']);
        }

        $this->view('PI/ManageAssignment', [
            'pageTitle' => 'Manage User Assignments',
            'users' => $users,
            'activeGrants' => $activeGrants,
            'userMappings' => json_encode($userMappings)
        ]);
    }

    public function updateAssignment() {
        $this->requireRole(['faculty_pi']);

        if ($this->isPost()) {
            $userID = (int)$this->getPost('userID');
            $grantIDs = $_POST['grantIDs'] ?? [];
            $postedPercentages = $_POST['billingPercentages'] ?? [];

            $users = $this->grantModel->getUsersWithGrants();
            $targetRole = 'researcher';
            foreach ($users as $u) {
                if ($u['userID'] == $userID) {
                    $targetRole = $u['userType'];
                    break;
                }
            }

            $perGrantPercentages = [];

            if ($targetRole === 'lab_manager') {
                foreach ($grantIDs as $grantID) {
                    $perGrantPercentages[(int)$grantID] = 0.0;
                }
            } else {
                foreach ($grantIDs as $grantID) {
                    $gid = (int)$grantID;
                    if (!isset($postedPercentages[$gid]) || $postedPercentages[$gid] === '') {
                        $this->setFlash('error', "Please enter a billing percentage for every checked grant.");
                        $this->redirect('/grants/manage');
                        return;
                    }
                    $pct = (float)$postedPercentages[$gid];
                    if ($pct <= 0 || $pct > 100) {
                        $this->setFlash('error', "Each billing percentage must be greater than 0 and at most 100.");
                        $this->redirect('/grants/manage');
                        return;
                    }
                    $perGrantPercentages[$gid] = $pct;
                }

                if (!empty($perGrantPercentages)) {
                    $newTotal = round(array_sum($perGrantPercentages), 2);

                    if ($newTotal < 99.99 || $newTotal > 100.01) {
                        $this->setFlash('error', "Update blocked: The total across the " . count($perGrantPercentages) . " checked grant(s) is {$newTotal}%. It must equal exactly 100%.");
                        $this->redirect('/grants/manage');
                        return;
                    }
                }
            }

            $this->grantModel->removeAllGrantsFromUser($userID);

            $successCount = 0;
            foreach ($perGrantPercentages as $grantID => $pct) {
                if ($this->grantModel->addUserToGrant($grantID, $userID, $pct)) {
                    $successCount++;
                }
            }

            $this->setFlash('success', "Assignment updated! The user is now assigned to $successCount grant(s).");
            $this->redirect('/grants');
        }
    }
}