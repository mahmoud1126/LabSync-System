<?php

require_once __DIR__ . '/../core/Controller.php';

class ComplianceController extends BaseController
{
    // ═══ Views ═══

    public function showRequestSupervision() {
        $this->requireRole(['researcher', 'guest_researcher']);

        require_once __DIR__ . '/../models/LabManager.php';

        $userModel   = new Labmanager();
        $bookingID   = (int) $this->getQuery('bookingID');
        $labManagers = $userModel->getUsersByType('lab_manager');

        $this->view('compliance/supervised_request', [
            'bookingID'   => $bookingID,
            'labManagers' => $labManagers
        ]);
    }

    public function showPendingSupervisions() {
        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/Booking.php';

        $bookingModel = new Booking();
        $bookings     = $bookingModel->getBookingsByStatus('pending');

        $this->view('compliance/supervised_pending', [
            'bookings' => $bookings
        ]);
    }

    // ═══ Actions ═══

    public function requestSupervision() {
        $this->requireRole(['researcher', 'guest_researcher']);

        $data = [
            'bookingID'    => (int) $this->getPost('bookingID'),
            'userID'       => $this->getCurrentUserID(),
            'labManagerID' => (int) $this->getPost('labManagerID')
        ];

        require_once __DIR__ . '/../modules/module3/SupervisedSession.php';
        $supervisedSession = new SupervisedSession();
        $result = $supervisedSession->requestSupervision($data);

        if ($result['success']) {
            $this->logAction(
                'SUPERVISED_SESSION_REQUESTED',
                'Bookings',
                $result['message'],
                $data['bookingID']
            );
        }

        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );

        if ($result['success']) {
            $this->redirect('/bookings');
        } else {
            $this->redirect('/compliance/request-supervision?bookingID=' . $data['bookingID']);
        }
    }

    public function approveSupervision() {
        $this->requireRole(['lab_manager']);

        $data = [
            'bookingID'    => (int) $this->getPost('bookingID'),
            'labManagerID' => $this->getCurrentUserID()
        ];

        require_once __DIR__ . '/../modules/module3/SupervisedSession.php';
        $supervisedSession = new SupervisedSession();
        $result = $supervisedSession->approveSupervision($data);

        if ($result['success']) {
            $this->logAction(
                'SUPERVISED_SESSION_APPROVED',
                'Bookings',
                $result['message'],
                $data['bookingID']
            );
        }

        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );

        $this->redirect('/compliance/pending-supervisions');
    }

    public function rejectSupervision() {
        $this->requireRole(['lab_manager']);

        $data = [
            'bookingID'    => (int) $this->getPost('bookingID'),
            'labManagerID' => $this->getCurrentUserID(),
            'reason'       => $this->getPost('reason')
        ];

        require_once __DIR__ . '/../modules/module3/SupervisedSession.php';
        $supervisedSession = new SupervisedSession();
        $result = $supervisedSession->rejectSupervision($data);

        if ($result['success']) {
            $this->logAction(
                'SUPERVISED_SESSION_REJECTED',
                'Bookings',
                $result['message'],
                $data['bookingID']
            );
        }

        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );

        $this->redirect('/compliance/pending-supervisions');
    }

    public function displayAlert() {
        $this->requireLogin();

        $equipmentID = (int) $this->getQuery('equipmentID');

        require_once __DIR__ . '/../modules/module3/HazmatAlert.php';
        $hazmatAlert = new HazmatAlert();
        $result = $hazmatAlert->displayAlert(['equipmentID' => $equipmentID]);

        if ($result['success']) {
            $this->view('compliance/hazmat_alert', [
                'warnings'      => $result['data']['warnings'],
                'equipmentName' => $result['data']['equipmentName'],
                'equipmentID'   => $equipmentID
            ]);
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/bookings');
        }
    }

    public function acknowledgeWarning() {
        $this->requireLogin();

        $data = [
            'userID'      => $this->getCurrentUserID(),
            'equipmentID' => (int) $this->getPost('equipmentID')
        ];

        require_once __DIR__ . '/../modules/module3/HazmatAlert.php';
        $hazmatAlert = new HazmatAlert();
        $result = $hazmatAlert->acknowledgeWarning($data);

        if ($result['success']) {
            $this->logAction(
                'HAZMAT_WARNING_ACKNOWLEDGED',
                'HazmatWarnings',
                $result['message'],
                $data['equipmentID']
            );
        }

        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );

        $this->redirect('/bookings');
    }
}