<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/GrantTransaction.php';
require_once __DIR__ . '/../models/FacultyPI.php';

class GrantController extends BaseController {
    private $grantModel;
    private $transactionModel;
    private $piModel;

    public function __construct() {
        $this->requireRole(['faculty_pi']);
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
        $this->piModel = new FacultyPI();
    }

    public function dashboard() {
        $piID = $this->getCurrentUserID();
        $data = [
            'title' => 'Financial Overview',
            'pendingTransactions' => $this->transactionModel->getPendingByPI($piID),
            'recentActivity'      => $this->transactionModel->getRecentByPI($piID),
            'managedGrants'       => $this->grantModel->getGrantsByPI($piID)
        ];
        $this->view('pi/grant_dashboard', $this->withSection('finance', $data));
    }

    public function handleAction() {
        if (!$this->isPost()) $this->redirect('/grant/dashboard');

        $transactionID = $this->getPost('transactionID');
        $action        = $this->getPost('action');
        $piID          = $this->getCurrentUserID();

        if (!$this->piModel->isAssignedToTransaction($piID, $transactionID)) {
            $this->setFlash('error', 'Unauthorized: Management rights missing.');
            $this->redirect('/grant/dashboard');
        }

        $transaction = $this->transactionModel->getTransactionById($transactionID);

        if ($action === 'approve') {
            $this->approve($transaction);
        } elseif ($action === 'reject') {
            $this->reject($transaction);
        }

        $this->redirect('/grant/dashboard');
    }

    private function approve($transaction) {
        $id = $transaction['transactionID'];
        
        if ($this->transactionModel->updateStatus($id, 'approved')) {
            $this->logAction('UPDATE', 'GrantTransactions', "Manual PI Approval", $id, 'pending', 'approved');
            $this->setFlash('success', "Transaction #$id approved.");
        }
    }

    private function reject($transaction) {
        $id = $transaction['transactionID'];
        $this->grantModel->refundToBalance($transaction['grantID'], $transaction['amount']);
        
        if ($this->transactionModel->updateStatus($id, 'rejected')) {
            $this->logAction('UPDATE', 'GrantTransactions', "PI Rejected: Funds restored to Grant ID: " . $transaction['grantID'], $id, 'pending', 'rejected');
            $this->setFlash('warning', "Transaction rejected. Funds returned to grant.");
        }
    }
}