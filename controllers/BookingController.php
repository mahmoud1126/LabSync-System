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
    // 1. Get the logged-in user's ID
    $userID = $_SESSION['user']['userID'] ?? null;

    if (!$userID) {
        header("Location: /login");
        exit();
    }

    // 2. Call the model method you just showed me
    // This solves the 'Undefined property $db' error
    $bookings = $this->bookingModel->getBookingsByUserId($userID);

    // 3. Load the view
    require_once __DIR__ . '/../pages/booking/index.php';}

    public function store() {
    // 1. Set the response header so the browser knows JSON is coming
    header('Content-Type: application/json');

    // 2. Identify the user (Check all possible session keys)
    $userID = $_SESSION['user']['userID'] ?? null;

    if (!$userID) {
        echo json_encode([
            'success' => false, 
            'message' => 'Session expired. Please log in again.'
        ]);
        exit();
    }


    // 3. Prepare the data from the form
    $date = $_POST['bookingDate'];
    $fullStart = $date . ' ' . $_POST['startTime'] . ':00';
    $fullEnd = $date . ' ' . $_POST['endTime'] . ':00';
    $equipmentID = $_POST['equipmentID'];
    $grantID = $_POST['grantID'] ?? null;

    // 4. Call your service to handle the database logic
    try {
        $result = $this->sequentialService->bookWithDependencies(
            $userID, 
            $equipmentID, 
            $fullStart, 
            $fullEnd, 
            $grantID
        );

        // 5. Send the success/fail result back to the Modal
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
        $userID = $_SESSION['userID'];
        $role = $_SESSION['userType'];
        $booking = $this->bookingModel->getBookingById($bookingID);

        if ($role === 'lab_manager' || (isset($booking['userID']) && $booking['userID'] == $userID)) {
            $this->bookingModel->updateBookingStatus($bookingID, 'cancelled');
        }
        
        header("Location: /booking/index");
    }
}