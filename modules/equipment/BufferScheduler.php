<?php

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Equipment.php';

class BufferSchedulerService {

    protected $bookingModel;
    protected $equipmentModel;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->equipmentModel = new Equipment();
    }

    public function scheduleBuffers($bookingID, $equipmentID, $startTime, $endTime) {
        
     
        $equipmentData = $this->equipmentModel->getEquipmentById($equipmentID);
        
        $powerUpMinutes = (int) ($equipmentData['powerUpBufferMinutes'] ?? 0);
        $coolDownMinutes = (int) ($equipmentData['coolDownBufferMinutes'] ?? 0);

        if ($powerUpMinutes === 0 && $coolDownMinutes === 0) {
            return [
                'success' => true,
                'message' => "No buffers required for this equipment."
            ];
        }

 
        $originalBooking = $this->bookingModel->getBookingById($bookingID);
        if (!$originalBooking) {
             return [
                'success' => false,
                'message' => "Original booking not found."
             ];
        }
        
        $userID = $originalBooking['userID'];
        $buffersCreated = 0;


        if ($powerUpMinutes > 0) {
            $powerUpStart = new DateTime($startTime);
            $powerUpStart->modify("-{$powerUpMinutes} minutes");
            

            $this->bookingModel->createBooking(
                $userID, 
                $equipmentID, 
                $powerUpStart->format('Y-m-d H:i:s'), 
                $startTime, 
                'unavailable',      
                true,               
                $bookingID         
            );
            $buffersCreated++;
        }

        
        if ($coolDownMinutes > 0) {
            $coolDownEnd = new DateTime($endTime);
            $coolDownEnd->modify("+{$coolDownMinutes} minutes");
            
          
            $this->bookingModel->createBooking(
                $userID, 
                $equipmentID, 
                $endTime, 
                $coolDownEnd->format('Y-m-d H:i:s'), 
                'unavailable',      
                true,               
                $bookingID          
            );
            $buffersCreated++;
        }

        return [
            'success' => true,
            'message' => "Equipment schedule updated. {$buffersCreated} buffer period(s) automatically blocked to prevent overlapping usage."
        ];
    }
}
?>