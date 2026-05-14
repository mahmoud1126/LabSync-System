<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Equipment.php';
require_once __DIR__ . '/../modules/equipment/SequentialBooking.php';
require_once __DIR__ . '/../models/GuestResearcher.php'; 

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
            $waitlistedBookings = $this->bookingModel->getBookingsByStatus('pending');
        } else {
            $bookings = $this->bookingModel->getBookingsByUserId($userID);
        }

        $this->view('booking/index', [
            'bookings' => $bookings,
            'waitlistedBookings' => $waitlistedBookings
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
            $result = $this->sequentialService->bookWithDependencies(
                $userID, 
                $equipmentID, 
                $fullStart, 
                $fullEnd, 
                $grantID
            );

            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success']
                    ? 'Booking requested! Awaiting Lab Manager approval.'
                    : ($result['message'] ?? 'Conflict: This time slot is already taken.')
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function confirm($bookingID) {
        $this->requireRole(['lab_manager']);
        $booking = $this->bookingModel->getBookingById($bookingID);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found.');
            $this->redirect('/booking/index');
            return;
        }

        $start = new DateTime($booking['startTime']);
        $end = new DateTime($booking['endTime']);
        $diff = $end->diff($start);
        $hours = $diff->h + ($diff->days * 24) + ($diff->i / 60);

        $baseCost = $this->equipmentModel->calculateUsageCost($booking['equipmentID'], $hours);

        $guestModel = new GuestResearcher();
        $guestInfo = $guestModel->getGuestResearcherById($booking['userID']);
        
        $taxMultiplier = 1.0; 
        
        if ($guestInfo) {
            $taxMultiplier = (float)$guestInfo['taxRate'] / 100;
        }

        $finalCost = round($baseCost * $taxMultiplier, 2);

        $this->bookingModel->updateBookingWithCost($bookingID, 'confirmed', $finalCost, $this->getCurrentUserID());
        
        $taxMsg = ($taxMultiplier > 1.0) ? " (Includes " . ($taxMultiplier * 100) . "% external rate tax)" : "";
        
        $this->setFlash('success', 'Booking ops confirmed. Total Cost ($' . number_format($finalCost, 2) . ')' . $taxMsg . ' calculated and sent to PI.');
        $this->redirect('/booking/index');
    }

    public function cancel($bookingID) {
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

        $this->view('booking/view', ['booking' => $booking]);
    }
}
?>