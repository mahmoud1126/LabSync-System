<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/GrantTransaction.php';
require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/Booking.php';

class PIController extends BaseController {
    private $transactionModel;
    private $grantModel;
    private $bookingModel;

    public function __construct() {
        // Ensuring names match your existing model classes
        $this->transactionModel = new GrantTransaction();
        $this->grantModel = new Grant();
        $this->bookingModel = new Booking();
    }

    /**
     * Display the PI Dashboard
     */
    public function index() {
        $this->requireRole('pi');
        $piID = $_SESSION['user']['userID'];

        // Get pending approvals for this PI's grants
        $pendingApprovals = $this->transactionModel->getPendingByPI($piID);
        
        // Get transaction history for context
        $recentHistory = $this->transactionModel->getRecentByPI($piID);
        
        // Get list of grants this PI manages
        $managedGrants = $this->grantModel->getGrantsByPI($piID);

        $this->view('pi/dashboard', [
            'pendingApprovals' => $pendingApprovals,
            'recentHistory' => $recentHistory,
            'managedGrants' => $managedGrants,
            'pageTitle' => 'PI Financial Dashboard'
        ]);
    }

    /**
     * Single-phase approval logic
     */
    public function approveTransaction() {
        $this->requireRole('pi');
        
        if (!$this->isPost()) {
            $this->redirect('/pi/dashboard');
        }

        $transactionID = $this->getPost('transactionID');
        $action = $this->getPost('action'); // 'approved' or 'rejected'
        $piID = $_SESSION['user']['userID'];

        // 1. Fetch transaction details to find the related Booking
        $transaction = $this->transactionModel->getTransactionById($transactionID);
        
        if (!$transaction) {
            $this->setFlash('error', 'Transaction not found.');
            $this->redirect('/pi/dashboard');
        }

        // 2. Process the financial status
        $success = $this->transactionModel->updateStatus($transactionID, $action);

        if ($success) {
            // 3. Synchronize Booking status
            // If approved, booking becomes 'confirmed'. If rejected, 'cancelled'.
            if ($transaction['bookingID']) {
                $newBookingStatus = ($action === 'approved') ? 'confirmed' : 'cancelled';
                $this->bookingModel->updateBookingStatus($transaction['bookingID'], $newBookingStatus);
            }

            $msg = ($action === 'approved') ? "Expense approved and booking confirmed." : "Expense rejected.";
            $this->setFlash('success', $msg);
        } else {
            $this->setFlash('error', 'Failed to update transaction status.');
        }

        $this->redirect('/pi/dashboard');
    }
}