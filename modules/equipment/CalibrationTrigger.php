
<?php

require_once __DIR__ . '/../models/Session.php';
require_once __DIR__ . '/../models/Equipment.php';
require_once __DIR__ . '/../models/Booking.php';

class CalibrationTriggerService {

    protected $sessionModel;
    protected $equipmentModel;
    protected $bookingModel;

    public function __construct() {
        $this->sessionModel = new Session();
        $this->equipmentModel = new Equipment();
        $this->bookingModel = new Booking();
    }

    public function processSessionCompletion($sessionID) {
        
        $sessionEnded = $this->sessionModel->endSession($sessionID);
        
        if (!$sessionEnded) {
            return false;
        }

        $sessionData = $this->sessionModel->getSessionById($sessionID);
        $equipmentID = $sessionData['equipmentID'];
        $sessionDuration = (float) $sessionData['durationHours'];

        $equipmentData = $this->equipmentModel->getEquipmentById($equipmentID);
        $currentTotalHours = (float) $equipmentData['currentCalibrationHours'];
        $threshold = (float) $equipmentData['calibrationThresholdHours'];

        $this->equipmentModel->logUsageHours($equipmentID, $sessionDuration);

        $newTotalHours = $currentTotalHours + $sessionDuration;

        if ($newTotalHours >= $threshold) {
           
            $this->equipmentModel->updateEquipmentStatus($equipmentID, 'locked_out');
            $this->preventFutureBookings($equipmentID);

            return "Equipment locked pending calibration.";
        }
    }

    
    protected function preventFutureBookings($equipmentID) {

        $futureBookings = $this->bookingModel->getBookingsByEquipment($equipmentID);
        
        foreach ($futureBookings as $booking) {
            if ($booking['bookingStatus'] === 'pending' || $booking['bookingStatus'] === 'confirmed') {

                $this->bookingModel->cancelBooking(
                    $booking['bookingID'], 
                    'System Auto-Cancellation: Equipment locked for required maintenance.'
                );
            }
        }
    }
  
  
}
?>