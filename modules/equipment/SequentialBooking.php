<?php

require_once __DIR__ . '/../../models/Booking.php';   // Line 3
require_once __DIR__ . '/../../models/Equipment.php'; // Line 4
require_once __DIR__ . '/../../config/Database.php';  // Line 5

class SequentialBookingService {

    protected $bookingModel;
    protected $equipmentModel;
    protected $db;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->equipmentModel = new Equipment();
        $this->db = Database::getInstance()->getConnection();
    }

    
    public function bookWithDependencies($userID, $primaryEquipmentID, $startTime, $endTime, $grantID = null) {
        

        if ($this->bookingModel->hasTimeConflict($primaryEquipmentID, $startTime, $endTime)) {
            return [
                'success' => false,
                'message' => "Primary equipment is unavailable during the requested time."
            ];
        }

       
        $dependencies = $this->equipmentModel->getDependencies($primaryEquipmentID);

       
        foreach ($dependencies as $dep) {
            if ($this->bookingModel->hasTimeConflict($dep['equipmentID'], $startTime, $endTime)) {
                return [
                    'success' => false,
                    'message' => "Required secondary equipment ({$dep['equipmentName']}) is unavailable during this time. The booking was prevented to avoid an incomplete setup."
                ];
            }
        }

        
        try {
            $this->db->beginTransaction();
          
            $primaryBookingID = $this->bookingModel->createBooking(
                $userID, 
                $primaryEquipmentID, 
                $startTime, 
                $endTime, 
                'pending', 
                false, 
                null, 
                $grantID
            );

            foreach ($dependencies as $dep) {
                $this->bookingModel->createBooking(
                    $userID, 
                    $dep['equipmentID'], 
                    $startTime, 
                    $endTime, 
                    'pending', 
                    true,               
                    $primaryBookingID,  
                    $grantID
                );
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Primary equipment and required secondary modules successfully booked.",
                'primaryBookingID' => $primaryBookingID
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            
            return [
                'success' => false,
                'message' => "An unexpected error occurred while processing the booking: " . $e->getMessage()
            ];
        }
    }
}
?>