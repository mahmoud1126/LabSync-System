<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/HazmatWarning.php';
require_once __DIR__ . '/../../models/Equipment.php';
require_once __DIR__ . '/../../models/AuditLog.php';

class HazmatAlert
{
    private HazmatWarning $hazmatModel;
    private Equipment     $equipmentModel;
    private AuditLog      $auditLog;

    public function __construct() {
        $this->hazmatModel    = new HazmatWarning();
        $this->equipmentModel = new Equipment();
        $this->auditLog       = new AuditLog();
    }

    public function displayAlert(array $data): array
    {
        $required = ['equipmentID'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        $equipmentID = (int) $data['equipmentID'];

        $equipment = $this->equipmentModel->getEquipmentById($equipmentID);
        if (!$equipment) {
            return ['success' => false, 'message' => 'Equipment not found.'];
        }

        $warnings = HazmatWarning::getWarningsByEquipment(
            $equipmentID,
            Database::getInstance()->getConnection()
        );

        if (empty($warnings)) {
            return ['success' => false, 'message' => 'No hazmat warnings found for this equipment.'];
        }

        return [
            'success' => true,
            'message' => 'Hazmat warnings retrieved.',
            'data'    => [
                'equipmentID'   => $equipmentID,
                'equipmentName' => $equipment['equipmentName'],
                'warnings'      => $warnings
            ]
        ];
    }

    public function acknowledgeWarning(array $data): array
    {
        $required = ['userID', 'equipmentID'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }

        $userID      = (int) $data['userID'];
        $equipmentID = (int) $data['equipmentID'];

        $warnings = HazmatWarning::getWarningsByEquipment(
            $equipmentID,
            Database::getInstance()->getConnection()
        );

        if (empty($warnings)) {
            return ['success' => false, 'message' => 'No warnings found for this equipment.'];
        }

        $result = $this->hazmatModel->acknowledgeWarning($userID, $warnings[0]['warningID']);
        if (!$result) {
            return ['success' => false, 'message' => 'Failed to acknowledge warning.'];
        }

        return [
            'success' => true,
            'message' => 'Hazmat warning acknowledged. You may proceed.',
            'data'    => [
                'userID'      => $userID,
                'equipmentID' => $equipmentID
            ]
        ];
    }
}