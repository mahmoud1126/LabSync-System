<?php

require_once __DIR__ . '/../../models/IncidentLog.php';
require_once __DIR__ . '/../../models/Researcher.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/AuditLog.php';

class IncidentLockout
{

    private IncidentLog $incidentModel;
    private Researcher  $userModel;    
    private Booking     $bookingModel;
    private AuditLog    $auditLog;

    public function __construct(){
        $this->incidentModel = new IncidentLog();
        $this->userModel = new Researcher();
        $this->bookingModel = new Booking();
        $this->auditLog = new AuditLog();
    }

    public function submitAndLockout(array $data): array
    {

        $required = ['userID', 'equipmentID', 'reportedByID', 'incidentType', 'description', 'severity', 'timeOfIncident'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}" ];
            }
        }

        $allowedSeverities = ['low', 'medium', 'high', 'critical'];
        if (!in_array($data['severity'], $allowedSeverities, true)) {
            return [ 'success' => false, 'message' => 'Invalid severity'];
        }

  
        $userID = (int) $data['userID'];
        $equipmentID = (int) $data['equipmentID'];
        $reportedByID = (int) $data['reportedByID'];

        $user = $this->userModel->getUserById($userID);
        if (!$user) {
            return ['success' => false, 'message' => 'Involved user not found.'];
        }

        $this->incidentModel->createIncident(
            $userID,
            $equipmentID,
            $reportedByID,
            $data['incidentType'],
            $data['description'],
            $data['severity'],
            $data['timeOfIncident']
        );


        $previousStatus = $user['userStatus'];
        $this->userModel->updateStatus($userID, 'suspended');


        $cancelledCount = $this->cancelFutureBookings(
            $userID,
            'Account suspended due to incident report: ' . $data['incidentType']
        );


        $this->auditLog->log(
            $reportedByID,
            'INCIDENT_AUTO_LOCKOUT',
            'Users',
            $userID,
            json_encode(['userStatus' => $previousStatus]),       
            json_encode(['userStatus' => 'suspended']),           
            "Incident reported (severity={$data['severity']}). "
          . "User auto-suspended. Cancelled bookings: {$cancelledCount}."
        );


        return [
            'success' => true,
            'message' => "Incident recorded. The user has been suspended and $cancelledCount future booking(s) were cancelled.",
            'data'    => [ 'userSuspended' => true, 'previousStatus' => $previousStatus, 'bookingsCancelled' => $cancelledCount,
            ],
        ];
    }


    private function cancelFutureBookings(int $userID, string $reason): int
    {
        $bookings = $this->bookingModel->getBookingsByUser($userID);
        $count    = 0;
        $now      = time();

        foreach ($bookings as $b) {
            $isFuture = strtotime($b['startTime']) > $now;
            $isActive = ($b['bookingStatus'] === 'pending' || $b['bookingStatus'] === 'confirmed');

            if ($isFuture && $isActive) {
                $this->bookingModel->cancelBooking(
                    (int) $b['bookingID'],
                    $reason
                );
                $count++;
            }
        }

        return $count;
    }
}