<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Equipment.php'; // Required for Phase 1 Cost Calc
require_once __DIR__ . '/../modules/equipment/SequentialBooking.php';

class BookingController extends BaseController {
    private $bookingModel;
    private $equipmentModel;
    private $sequentialService;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->equipmentModel = new Equipment();
        $this->sequentialService = new SequentialBookingService();
    }

    public function index() {
        $this->requireLogin();
        $userID = $this->getCurrentUserID();
        $role = $this->getCurrentUserRole();

        $waitlistedBookings = [];

        if ($role === 'lab_manager') {
            $bookings = $this->bookingModel->getAllFutureBookings();
            // Fetch the requests specifically for the Lab Manager
            $waitlistedBookings = $this->bookingModel->getBookingsByStatus('waitlisted');
        } else {
            $bookings = $this->bookingModel->getBookingsByUserId($userID);
        }

        $this->view('booking/index', [
            'bookings' => $bookings,
            'waitlistedBookings' => $waitlistedBookings // Pass this to the view
        ]);
    }

    public function store() {
        header('Content-Type: application/json');

        $userID = $this->getCurrentUserID();

        if (!$userID) {
            echo json_encode([
                'success' => false, 
                'message' => 'Session expired. Please log in again.'
            ]);
            exit();
        }

        $date = $_POST['bookingDate'];
        $fullStart = $date . ' ' . $_POST['startTime'] . ':00';
        $fullEnd = $date . ' ' . $_POST['endTime'] . ':00';
        $equipmentID = $_POST['equipmentID'];
        $grantID = $_POST['grantID'] ?? null;

        try {
            // This will automatically create the booking as 'waitlisted' based on our model update!
            $result = $this->sequentialService->bookWithDependencies(
                $userID, 
                $equipmentID, 
                $fullStart, 
                $fullEnd, 
                $grantID
            );

            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Booking waitlisted! Awaiting Lab Manager approval.' : 'Conflict: This time slot is already taken.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    // NEW: Phase 1 Ops Approval
    public function confirm($bookingID) {
        $this->requireRole(['lab_manager']);
        $booking = $this->bookingModel->getBookingById($bookingID);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found.');
            $this->redirect('/booking/index');
            return;
        }

        // Calculate duration in hours
        $start = new DateTime($booking['startTime']);
        $end = new DateTime($booking['endTime']);
        $diff = $end->diff($start);
        $hours = $diff->h + ($diff->days * 24) + ($diff->i / 60);

        // Run the Equipment Model cost calculation
        $totalCost = $this->equipmentModel->calculateUsageCost($booking['equipmentID'], $hours);

        // Save cost and move to Phase 2 (Confirmed)
        $this->bookingModel->updateBookingWithCost($bookingID, 'confirmed', $totalCost, $this->getCurrentUserID());
        
        $this->setFlash('success', 'Booking ops confirmed. Total Cost ($' . number_format($totalCost, 2) . ') calculated and sent to PI.');
        $this->redirect('/booking/index');
    }

    public function cancel($bookingID) {
        $userID = $this->getCurrentUserID();
        $role = $this->getCurrentUserRole();
        $booking = $this->bookingModel->getBookingById($bookingID);

        if ($role === 'lab_manager' || (isset($booking['userID']) && $booking['userID'] == $userID)) {
            // Lab Manager uses this for Phase 1 rejection
            $this->bookingModel->updateBookingStatus($bookingID, 'cancelled');
            $this->setFlash('success', 'Booking cancelled successfully.');
        } else {
            $this->setFlash('error', 'Unauthorized to cancel this booking.');
        }
        
        $this->redirect('/booking/index');
    }

    public function details() {
        $this->requireLogin();
        $id = $_GET['id'] ?? null;
        
        if (!$id) $this->redirect('/dashboard');

        $booking = $this->bookingModel->getBookingById($id); 

        if ($this->getCurrentUserRole() === 'researcher' && $booking['userID'] !== $this->getCurrentUserID()) {
            $this->redirect('/dashboard');
        }

        $this->view('booking/view', ['booking' => $booking]);
    }
}
?>