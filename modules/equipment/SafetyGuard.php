<?php
require_once __DIR__ . '/../../models/SafetyBriefing.php';
require_once __DIR__ . '/../../models/AuditLog.php';

class SafetyGuardService {
    protected $briefingModel;
    protected $auditModel;

    public function __construct() {
        $this->briefingModel = new SafetyBriefing();
        $this->auditModel = new AuditLog();
    }

    public function verifyCompliance($userID, $equipmentID) {
        $briefing = $this->briefingModel->getBriefingByEquipmentId($equipmentID);

        if (!$briefing) {
            return ['success' => true, 'acknowledged' => true];
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT ackID FROM SafetyBriefingAcknowledgements
                               WHERE userID = :uid AND briefingID = :bid");
        $stmt->execute([':uid' => $userID, ':bid' => $briefing['briefingID']]);

        if ($stmt->fetch()) {
            return ['success' => true, 'acknowledged' => true];
        }

        return [
            'success' => false,
            'acknowledged' => false,
            'briefingID' => $briefing['briefingID'],
            'briefingContent' => $briefing['briefingContent']
        ];
    }

    public function confirmReceipt($userID, $briefingID) {
        $briefing = $this->briefingModel->getBriefingById($briefingID);
        if (!$briefing) {
            return ['success' => false, 'message' => 'Briefing not found.'];
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO SafetyBriefingAcknowledgements (userID, briefingID, acknowledgedAt)
                                   VALUES (:uid, :bid, NOW())
                                   ON DUPLICATE KEY UPDATE acknowledgedAt = NOW()");
            $stmt->execute([':uid' => $userID, ':bid' => $briefingID]);

            $this->auditModel->log(
                $userID,
                'SAFETY_BRIEFING_CONFIRMED',
                'SafetyBriefingAcknowledgements',
                $briefingID,
                null,
                'acknowledged',
                "User confirmed receipt of safety briefing ID {$briefingID}"
            );

            return ['success' => true, 'message' => 'Safety briefing confirmed.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to confirm briefing: ' . $e->getMessage()];
        }
    }
}
