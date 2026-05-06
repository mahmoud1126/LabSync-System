<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../modules/equipment/SequentialBooking.php';

class BookingController extends BaseController {
    private $bookingModel;
    private $sequentialService;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->sequentialService = new SequentialBookingService();
    }

    public function index() {
    $this->requireLogin();
    $userID = $this->getCurrentUserID();
    $role = $this->getCurrentUserRole();

    if ($role === 'lab_manager') {
        $bookings = $this->bookingModel->getAllFutureBookings();
    } else {
        $bookings = $this->bookingModel->getBookingsByUserId($userID);
    }

    $this->view('booking/index', ['bookings' => $bookings]);
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
            $result = $this->sequentialService->bookWithDependencies(
                $userID, 
                $equipmentID, 
                $fullStart, 
                $fullEnd, 
                $grantID
            );

            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Booking successful!' : 'Conflict: This time slot is already taken.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function cancel($bookingID) {
        // Fixed: Using getCurrentUserID() and getCurrentUserRole() for consistency
        $userID = $this->getCurrentUserID();
        $role = $this->getCurrentUserRole();
        $booking = $this->bookingModel->getBookingById($bookingID);

        if ($role === 'lab_manager' || (isset($booking['userID']) && $booking['userID'] == $userID)) {
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

        // This calls the BaseController's view() method to render the page
        $this->view('booking/view', ['booking' => $booking]);
    }
}