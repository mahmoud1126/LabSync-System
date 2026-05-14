<?php
require_once __DIR__ . '/../core/Controller.php'; 
require_once __DIR__ . '/../models/GrantTransaction.php';
require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/Booking.php';

class PIController extends BaseController {
    private $transactionModel;
    private $grantModel;
    private $bookingModel;

    public function __construct() {
        $this->transactionModel = new GrantTransaction();
        $this->grantModel = new Grant();
        $this->bookingModel = new Booking();
    }

    public function index() {
        $this->requireRole(['faculty_pi']);

        $pendingApprovals = $this->transactionModel->getAllPendingTransactions();
        $recentHistory = $this->transactionModel->getAllRecentTransactions();
        $managedGrants = $this->grantModel->getAllGrants();
        
        $allUsers = $this->grantModel->getUsersWithGrants();
        $totalUsers = count($allUsers);
        $totalGrants = count($managedGrants);

        $this->view('PI/PIDashboard', [
            'pendingApprovals' => $pendingApprovals,
            'recentHistory' => $recentHistory,
            'managedGrants' => $managedGrants,
            'totalUsers' => $totalUsers,
            'totalGrants' => $totalGrants,
            'pageTitle' => 'PI Financial Dashboard'
        ]);
    }

    public function requests() {
        $this->requireRole(['faculty_pi']);
        
        $pendingBookings = $this->bookingModel->getBookingsByStatus('confirmed');
        // This will grab the pending reallocations we just created in GrantController
        $pendingTransactions = $this->transactionModel->getAllPendingTransactions();

        $this->view('PI/PIRequests', [
            'pendingTransactions' => $pendingTransactions,
            'pendingBookings' => $pendingBookings,
            'pageTitle' => 'Financial Requests'
        ]);
    }

    // --- NEW: Handle standalone transactions (Reallocations) ---
    public function approveTransaction() {
        $this->requireRole(['faculty_pi']);
        
        if ($this->isPost()) {
            $transactionID = (int)$this->getPost('transactionID');
            $tx = $this->transactionModel->getTransactionById($transactionID);

            if (!$tx || $tx['approvalStatus'] !== 'pending') {
                $this->setFlash('error', 'Invalid or already processed transaction.');
                $this->redirect('/PI/requests');
                return;
            }

            // If this is a Reallocation, we need to move the money to the destination
            if ($tx['transactionType'] === 'reallocation_out') {
                // Find the Dest ID from the string "Pending Reallocation to Grant #5"
                preg_match('/Grant #(\d+)/', $tx['description'], $matches);
                
                if (isset($matches[1])) {
                    $destID = (int)$matches[1];
                    
                    // Deduct from Source
                    $this->grantModel->deductFromBalance($tx['grantID'], $tx['amount']);
                    // Add to Destination
                    $this->grantModel->refundToBalance($destID, $tx['amount']);
                    
                    // Create the receipt for the destination grant
                    $this->transactionModel->createTransaction(
                        $destID, $tx['userID'], $tx['amount'], 'reallocation_in', 
                        "Received Reallocation from Grant #{$tx['grantID']}", 
                        0, 0, 0, null, null, 'approved'
                    );
                }
            }

            // Mark the original out-bound request as approved
            $this->transactionModel->updateStatus($transactionID, 'approved');
            $this->setFlash('success', 'Reallocation request approved and funds transferred.');
            $this->redirect('/PI/requests');
        }
    }

    public function rejectTransactionList() {
        $this->requireRole(['faculty_pi']);
        if ($this->isPost()) {
            $transactionID = (int)$this->getPost('transactionID');
            $this->transactionModel->updateStatus($transactionID, 'rejected');
            $this->setFlash('success', 'Reallocation request rejected.');
            $this->redirect('/PI/requests');
        }
    }

    // --- Handle Bookings ---
    public function approveBooking() {
        $this->requireRole(['faculty_pi']);
        
        if ($this->isPost()) {
            $bookingID = (int)$this->getPost('bookingID');
            $booking = $this->bookingModel->getBookingById($bookingID);

            if (!$booking) {
                $this->setFlash('error', 'Booking not found.');
                $this->redirect('/PI/requests');
                return;
            }

            require_once __DIR__ . '/../modules/billing/GrantPartitioning.php';
            /** @var GrantPartitioning $partitioner */
            $partitioner = new GrantPartitioning();

            $db = Database::getInstance()->getConnection();

            try {
                $db->beginTransaction();

                $totalCost = (float)$booking['totalCost'];
                $userID = (int)$booking['userID'];

                $assignedGrants = $this->grantModel->getGrantsForUser($userID);
                
                if(empty($assignedGrants)) {
                    throw new Exception("This user has no active grants assigned to them.");
                }

                $formattedGrants = [];
                foreach ($assignedGrants as $ag) {
                    $formattedGrants[] = [
                        'grantID' => $ag['grantID'],
                        'percentage' => (float)$ag['billingPercentage']
                    ];
                }

                $anchorGrantID = $formattedGrants[0]['grantID'];

                $transactionID = $this->transactionModel->createTransaction(
                    $anchorGrantID,
                    $userID,
                    $totalCost,
                    'deduction',
                    "Master Billing for Booking #$bookingID",
                    $totalCost, 0, 0, null, $bookingID, 'approved'
                );
                
                $success = $partitioner->partitionAndDeduct($transactionID, $totalCost, $formattedGrants);

                if (!$success) {
                    throw new Exception("Partitioning failed. Check if grant percentages total 100%.");
                }

                $this->bookingModel->updateBookingStatus($bookingID, 'completed', $_SESSION['user']['userID']);

                $db->commit();
                $this->setFlash('success', 'Booking approved and grants partitioned successfully!');
                
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $this->setFlash('error', 'Financial Error: ' . $e->getMessage());
            }

            $this->redirect('/PI/requests');
        }
    }

    public function rejectBooking() {
        $this->requireRole(['faculty_pi']);
        if ($this->isPost()) {
            $bookingID = (int)$this->getPost('bookingID');
            $this->bookingModel->updateBookingStatus($bookingID, 'rejected', $_SESSION['user']['userID']);
            $this->setFlash('success', 'Booking has been rejected and cancelled.');
            $this->redirect('/PI/requests');
        }
    }

    public function users() {
        $this->requireRole(['faculty_pi']);
        $users = $this->grantModel->getUsersWithGrants();

        $this->view('PI/PIUsers', [
            'users' => $users,
            'pageTitle' => 'User Overview'
        ]);
    }
}   