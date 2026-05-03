<?php

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Equipment.php';

class OverProvisioningGuardService {

    protected $bookingModel;
    protected $equipmentModel;

    public function __construct() {

        $this->bookingModel = new Booking();
        $this->equipmentModel = new Equipment();
    }


    public function processBookingRequest($userID, $equipmentID, $startTime, $endTime, $grantID = null) {
        
        // 1. Calculate the duration of the requested new booking in hours
        $newBookingHours = $this->calculateHours($startTime, $endTime);

        if ($newBookingHours <= 0) {
            return [
                'success' => false,
                'message' => "Invalid booking times provided. End time must be after start time."
            ];
        }

        $equipmentData = $this->equipmentModel->getEquipmentById($equipmentID);
        
        if (!$equipmentData) {
            return [
                'success' => false,
                'message' => "Equipment not found."
            ];
        }

       
        $maxUserQuota = (float) $equipmentData['userTotalHours'];

        
        if ($maxUserQuota > 0) {
            
            $userBookings = $this->bookingModel->getBookingsByUser($userID);
            $totalBookedHours = 0;

            foreach ($userBookings as $booking) {
                
                if ($booking['equipmentID'] == $equipmentID && 
                    in_array($booking['bookingStatus'], ['pending', 'confirmed', 'completed'])) {
                    
                    $totalBookedHours += $this->calculateHours($booking['startTime'], $booking['endTime']);
                }
            }

            $projectedTotalHours = $totalBookedHours + $newBookingHours;

            if ($projectedTotalHours > $maxUserQuota) {
                return [
                    'success' => false,
                    'message' => "Booking Blocked: Quota exceeded. You have requested {$projectedTotalHours} total hours, but your maximum allowed usage for this equipment is {$maxUserQuota} hours."
                ];
            }
        }

        $newBookingID = $this->bookingModel->createBooking(
            $userID, 
            $equipmentID, 
            $startTime, 
            $endTime,
            'pending',
            false,
            null,
            $grantID
        );

        return [
            'success' => true,
            'message' => "Booking request validated. Quota is within limits and booking has been created.",
            'bookingID' => $newBookingID
        ];
    }


    protected function calculateHours($start, $end) {
        $startTime = new DateTime($start);
        $endTime = new DateTime($end);
        
        $interval = $startTime->diff($endTime);
        
        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);
        
        return round($hours, 2);
    }
}
?>