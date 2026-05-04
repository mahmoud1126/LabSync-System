<?php

require_once __DIR__ . '/../core/Controller.php';

class SessionController extends BaseController
{
    public function active()
    {
        $this->requireLogin();
        $this->checkGuestExpiry();

        require_once __DIR__ . '/../models/Session.php';
        $sessionModel = new Session();

        $userID = $this->getCurrentUserID();
        $activeSessions = $sessionModel->getActiveSessionsByUser($userID);

        $this->view('sessions/active', [
            'activeSessions' => $activeSessions,
        ]);
    }

    public function start()
    {
        $this->requireLogin();
        $this->checkGuestExpiry();
        $this->requireSafetyBriefing();

        $bookingID = (int) $this->getPost('bookingID');
        $equipmentID = (int) $this->getPost('equipmentID');
        $userID = $this->getCurrentUserID();

        if (!$bookingID || !$equipmentID) {
            $this->setFlash('error', 'Missing booking or equipment information.');
            $this->redirect('/sessions/active');
            return;
        }

        require_once __DIR__ . '/../models/Booking.php';
        $bookingModel = new Booking();
        $booking = $bookingModel->getBookingById($bookingID);

        if (!$booking || (int) $booking['userID'] !== (int) $userID) {
            $this->setFlash('error', 'Booking not found or does not belong to you.');
            $this->redirect('/sessions/active');
            return;
        }

        require_once __DIR__ . '/../models/Session.php';
        $sessionModel = new Session();

        if ($sessionModel->hasActiveSession($userID)) {
            $this->setFlash('error', 'You already have an active session. Please end it before starting a new one.');
            $this->redirect('/sessions/active');
            return;
        }

 
        require_once __DIR__ . '/../modules/equipment/HardwareInterlock.php';
        $interlock = new HardwareInterlock();
        $check = $interlock->verifyInterlock($userID, $equipmentID, $bookingID);

        if (!$check['success']) {
            $this->setFlash('error', $check['message']);
            $this->redirect('/sessions/active');
            return;
        }

        $sessionID = $sessionModel->startSession($bookingID, $userID, $equipmentID);

        if (!$sessionID) {
            $this->setFlash('error', 'Failed to start session. Please try again.');
            $this->redirect('/sessions/active');
            return;
        }

        $this->logAction(
            'SESSION_STARTED',
            'Sessions',
            "Session started for equipment #{$equipmentID} on booking #{$bookingID}.",
            (int) $sessionID
        );

        $this->setFlash('success', 'Session started. Equipment is now in use.');
        $this->redirect('/sessions/active');
    }


    public function end()
    {
        $this->requireLogin();
        $this->checkGuestExpiry();

        $sessionID = (int) $this->getPost('sessionID');
        $userID = $this->getCurrentUserID();

        if (!$sessionID) {
            $this->setFlash('error', 'No session ID provided.');
            $this->redirect('/sessions/active');
            return;
        }

        require_once __DIR__ . '/../models/Session.php';
        $sessionModel = new Session();
        $session = $sessionModel->getSessionById($sessionID);

        if (!$session) {
            $this->setFlash('error', 'Session not found.');
            $this->redirect('/sessions/active');
            return;
        }

        if ((int) $session['userID'] !== (int) $userID) {
            $this->setFlash('error', 'You are not authorised to end this session.');
            $this->redirect('/sessions/active');
            return;
        }

        if ($session['sessionStatus'] !== 'active') {
            $this->setFlash('error', 'This session is already closed.');
            $this->redirect('/sessions/active');
            return;
        }

        $ended = $sessionModel->endSession($sessionID);

        if (!$ended) {
            $this->setFlash('error', 'Could not end session. It may have already been closed.');
            $this->redirect('/sessions/active');
            return;
        }

        $closedSession = $sessionModel->getSessionById($sessionID);
        $totalCost = (float) ($closedSession['totalCost']    ?? 0);
        $durationHours = (float) ($closedSession['durationHours'] ?? 0);

        require_once __DIR__ . '/../models/Booking.php';
        $bookingModel = new Booking();
        $booking = $bookingModel->getBookingById($session['bookingID']);

        if ($booking && !empty($booking['grantID']) && $totalCost > 0) {

            require_once __DIR__ . '/../modules/billing/GrantHardCap.php';
            $hardCap = new GrantHardCap();

            $result = $hardCap->enforce(
                grantID: (int) $booking['grantID'],
                userID: (int) $userID,
                totalCost: $totalCost,
                sessionID: (int) $sessionID,
                bookingID: (int) $session['bookingID'],
                baseCost: $totalCost,
                consumableCost: 0.0,
                overheadCost: 0.0
            );

            if (!$result['success']) {
                $this->setFlash('warning',
                    'Session ended, but grant charge failed: ' . $result['message']
                    . ' Please contact the Lab Manager.'
                );

                $this->logAction(
                    'GRANT_CHARGE_FAILED',
                    'Sessions',
                    "Grant charge failed after session #{$sessionID} ended. Reason: {$result['message']}",
                    (int) $sessionID
                );

                $this->redirect('/sessions/active');
                return;
            }
        }

        $this->logAction(
            'SESSION_ENDED',
            'Sessions',
            "Session #{$sessionID} ended. Duration: {$durationHours}h. Charged: \${$totalCost}.",
            (int) $sessionID
        );

        $this->setFlash('success',
            "Session ended. Duration: {$durationHours} hours. Total charged: \${$totalCost}."
        );

        $this->redirect('/sessions/active');
    }
}