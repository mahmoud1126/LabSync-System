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
        $userID = $_SESSION['userID'];
        $role = $_SESSION['userType'];

        if ($role === 'lab_manager') {
            $bookings = $this->bookingModel->getAllFutureBookings(); 
        } else {
            $bookings = $this->bookingModel->getBookingsByUserId($userID);
        }

        $this->view('booking/index', [
            'bookings' => $bookings, 
            'role' => $role,
            'currentUserID' => $userID
        ]);
    }

    public function store() {
        $result = $this->sequentialService->bookWithDependencies(
            $_SESSION['userID'], 
            $_POST['equipmentID'], 
            $_POST['startTime'], 
            $_POST['endTime'], 
            $_POST['grantID'] ?? null
        );

        header("Location: /booking/index?success=" . ($result['success'] ? '1' : '0'));
    }

    public function cancel($bookingID) {
        $userID = $_SESSION['userID'];
        $role = $_SESSION['userType'];
        $booking = $this->bookingModel->getBookingById($bookingID);

        if ($role === 'lab_manager' || (isset($booking['userID']) && $booking['userID'] == $userID)) {
            $this->bookingModel->updateBookingStatus($bookingID, 'cancelled');
        }
        
        header("Location: /booking/index");
    }
}