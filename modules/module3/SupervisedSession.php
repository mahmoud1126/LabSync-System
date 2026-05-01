<?php

require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Session.php';
require_once __DIR__ . '/../../models/AuditLog.php';

class SupervisedSession
{
    private Booking  $bookingModel;
    private Session  $sessionModel;
    private AuditLog $auditLog;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->sessionModel = new Session();
        $this->auditLog     = new AuditLog();
    }

    public function requestSupervision(array $data): array
    {
        $required = ['bookingID', 'userID', 'labManagerID'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        $bookingID    = (int) $data['bookingID'];
        $userID       = (int) $data['userID'];
        $labManagerID = (int) $data['labManagerID'];
        
        $booking = $this->bookingModel->getBookingById($bookingID);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found.'];
        }

        if ($booking['bookingStatus'] !== 'pending') {
            return ['success' => false, 'message' => 'Booking is not in pending status.'];
        }

        if ($this->sessionModel->hasActiveSession($userID)) {
            return ['success' => false, 'message' => 'User already has an active session.'];
        }

        $this->bookingModel->updateBookingStatus($bookingID, 'pending', $labManagerID);

        $this->auditLog->log(
            $userID,
            'SUPERVISED_SESSION_REQUESTED',
            'Bookings',
            $bookingID,
            null,
            null,
            "User {$userID} requested supervised session for booking {$bookingID}. Lab Manager: {$labManagerID}."
        );

        return [
            'success' => true,
            'message' => 'Supervision request submitted. Awaiting lab manager approval.',
            'data'    => [
                'bookingID'    => $bookingID,
                'labManagerID' => $labManagerID,
                'status'       => 'pending'
            ]
        ];
    }

    public function approveSupervision(array $data): array
    {
        $required = ['bookingID', 'labManagerID'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        $bookingID    = (int) $data['bookingID'];
        $labManagerID = (int) $data['labManagerID'];

        $booking = $this->bookingModel->getBookingById($bookingID);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found.'];
        }

        if ((int)$booking['labManagerID'] !== $labManagerID) {
            return ['success' => false, 'message' => 'Unauthorized lab manager.'];
        }

        $this->bookingModel->updateBookingStatus($bookingID, 'confirmed', $labManagerID);

        $sessionID = $this->sessionModel->startSession(
            $bookingID,
            $booking['userID'],
            $booking['equipmentID']
        );

        $this->auditLog->log(
            $labManagerID,
            'SUPERVISED_SESSION_APPROVED',
            'Bookings',
            $bookingID,
            json_encode(['bookingStatus' => 'pending']),
            json_encode(['bookingStatus' => 'confirmed']),
            "Lab Manager {$labManagerID} approved booking {$bookingID}. Session {$sessionID} started."
        );

        return [
            'success' => true,
            'message' => 'Supervision approved. Session started.',
            'data'    => [
                'bookingID' => $bookingID,
                'sessionID' => $sessionID,
                'status'    => 'confirmed'
            ]
        ];
    }

    public function rejectSupervision(array $data): array
    {
        $required = ['bookingID', 'labManagerID', 'reason'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        $bookingID    = (int) $data['bookingID'];
        $labManagerID = (int) $data['labManagerID'];

        $booking = $this->bookingModel->getBookingById($bookingID);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found.'];
        }

        if ((int)$booking['labManagerID'] !== $labManagerID) {
            return ['success' => false, 'message' => 'Unauthorized lab manager.'];
        }

        $this->bookingModel->cancelBooking($bookingID, $data['reason']);

        $this->auditLog->log(
            $labManagerID,
            'SUPERVISED_SESSION_REJECTED',
            'Bookings',
            $bookingID,
            json_encode(['bookingStatus' => 'pending']),
            json_encode(['bookingStatus' => 'cancelled']),
            "Lab Manager {$labManagerID} rejected booking {$bookingID}. Reason: {$data['reason']}."
        );

        return [
            'success' => true,
            'message' => 'Supervision rejected.',
            'data'    => [
                'bookingID' => $bookingID,
                'status'    => 'cancelled',
                'reason'    => $data['reason']
            ]
        ];
    }
}