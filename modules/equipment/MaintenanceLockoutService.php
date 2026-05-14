<?php

require_once __DIR__ . '/../../models/Equipment.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/AuditLog.php';

class MaintenanceLockoutService {

    protected $equipmentModel;
    protected $bookingModel;
    protected $auditModel;

    public function __construct() {
        $this->equipmentModel = new Equipment();
        $this->bookingModel = new Booking();
        $this->auditModel = new AuditLog();
    }

    public function triggerEmergencyLockout($equipmentID, $reason, $adminID) {

        $equipment = $this->equipmentModel->getEquipmentById($equipmentID);
        if (!$equipment) {
            return ['success' => false, 'message' => "Equipment record not found."];
        }

        try {
            $this->equipmentModel->updateEquipmentStatus($equipmentID, 'locked_out');

            $affectedBookings = $this->bookingModel->getBookingsByEquipment($equipmentID);
            $cancelledCount = 0;

            foreach ($affectedBookings as $booking) {
                if (in_array($booking['bookingStatus'], ['confirmed', 'pending'])) {

                    $cancellationNote = "EMERGENCY LOCKOUT: " . $reason;

                    $this->bookingModel->cancelBooking($booking['bookingID'], $cancellationNote);

                    $cancelledCount++;
                }
            }

            $this->auditModel->log(
                $adminID,
                'EMERGENCY_LOCKOUT',
                'Equipment',
                $equipmentID,
                $equipment['equipmentStatus'],
                'locked_out',
                "Manual lockout triggered. Reason: " . $reason
            );

            return [
                'success' => true,
                'message' => "Equipment locked successfully. {$cancelledCount} future reservations have been cancelled."
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Critical failure during lockout process: " . $e->getMessage()
            ];
        }
    }
}
