<?php

require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Equipment.php';
require_once __DIR__ . '/../../models/Researcher.php';
require_once __DIR__ . '/../../models/Grant.php';
require_once __DIR__ . '/../../models/SafetyBriefing.php';

class HardwareInterlock {

    protected $bookingModel;
    protected $equipmentModel;
    protected $researcherModel;
    protected $grantModel;
    protected $safetyModel;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->equipmentModel = new Equipment();
        $this->researcherModel = new Researcher();
        $this->grantModel = new Grant();
        $this->safetyModel = new SafetyBriefing();
    }

    public function verifyInterlock($userID, $equipmentID, $bookingID) {

        $user = $this->researcherModel->getUserById($userID);
        if (!$user || $user['userStatus'] !== 'active') {
            return ['success' => false, 'message' => "User account is suspended or inactive."];
        }

        $booking = $this->bookingModel->getBookingById($bookingID);
        if (!$booking || $booking['bookingStatus'] !== 'confirmed') {
            return ['success' => false, 'message' => "No confirmed booking found for this session."];
        }

        $currentTime = new DateTime();
        $startTime = new DateTime($booking['startTime']);
        $endTime = new DateTime($booking['endTime']);

        if ($currentTime < $startTime || $currentTime > $endTime) {
            return ['success' => false, 'message' => "Current time is outside the valid booking window."];
        }

        if (!$user['safetyBriefingAcknowledged']) {
            return ['success' => false, 'message' => "General safety briefing not acknowledged."];
        }

        $requiredBriefings = $this->safetyModel->getBriefingByEquipmentId($equipmentID);
        if (!empty($requiredBriefings)) {
            $userAcks = $this->researcherModel->getResearcherSafetyAcknowledgements($userID);
            $ackIds = array_column($userAcks, 'briefingID');

            foreach ($requiredBriefings as $briefing) {
                if (!in_array($briefing['briefingID'], $ackIds)) {
                    return ['success' => false, 'message' => "Missing equipment-specific safety briefing acknowledgement."];
                }
            }
        }

        $equipment = $this->equipmentModel->getEquipmentById($equipmentID);
        if ($equipment['equipmentStatus'] !== 'available') {
            return ['success' => false, 'message' => "Primary equipment is currently " . $equipment['equipmentStatus'] . "."];
        }

        $dependencies = $this->equipmentModel->getDependencies($equipmentID);
        foreach ($dependencies as $dep) {
            $depID = $dep['secondaryEquipmentID'] ?? $dep['equipmentID'];
            $depEq = $this->equipmentModel->getEquipmentById($depID);
            if ($depEq['equipmentStatus'] !== 'available') {
                return ['success' => false, 'message' => "Required dependency (" . $depEq['equipmentName'] . ") is unavailable."];
            }
        }

        if (!empty($booking['grantID'])) {
            $grant = $this->grantModel->getGrantById($booking['grantID']);
            if (!$grant || $grant['grantStatus'] !== 'active') {
                return ['success' => false, 'message' => "Associated grant is inactive or expired."];
            }

            if ((float)$grant['currentBalance'] <= 0) {
                return ['success' => false, 'message' => "Insufficient grant funds to start session."];
            }

            $hasAccess = $this->grantModel->verifyUserGrantAccess($booking['grantID'], $userID);
            if (!$hasAccess) {
                return ['success' => false, 'message' => "User does not have permission to charge this grant."];
            }
        } else if ($user['userType'] !== 'faculty_pi' && $user['userType'] !== 'lab_manager') {
            return ['success' => false, 'message' => "A valid grant must be selected to start a session."];
        }

        return ['success' => true, 'message' => "Interlock disengaged. Ready to start session."];
    }
}
?>
