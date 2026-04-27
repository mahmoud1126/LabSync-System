<?php

class HazmatWarning {

    private $warningID;
    private $equipmentID;
    private $hazardType;
    private $warningMessage;
    private $disposalInstructions;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function acknowledgeWarning($userID): bool {
        $stmt = $this->db->prepare("INSERT INTO SystemAuditLogs 
                                    (userID, actionType, tableAffected, recordID, description, createdAt)
                                    VALUES (:user_id, 'HAZMAT_ACKNOWLEDGED', 'HazmatWarnings', :warning_id, :desc, NOW())");
        return $stmt->execute([
            ':user_id'    => $userID,
            ':warning_id' => $this->warningID,
            ':desc'       => json_encode([
                'hazard_type'    => $this->hazardType,
                'equipment_id'   => $this->equipmentID,
                'warning_message'=> $this->warningMessage
            ])
        ]);
    }

    public function displayAlert(): void {
        $stmt = $this->db->prepare("SELECT * FROM HazmatWarnings 
                                    WHERE equipmentID = :equipment_id");
        $stmt->execute([':equipment_id' => $this->equipmentID]);
        $warning = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($warning) {
            $this->warningID            = $warning['warningID'];
            $this->hazardType           = $warning['hazardType'];
            $this->warningMessage       = $warning['warningMessage'];
            $this->disposalInstructions = $warning['disposalInstructions'];
        }
    }

    public static function getWarningsByEquipment($equipmentID, $db): array {
        $stmt = $db->prepare("SELECT * FROM HazmatWarnings 
                              WHERE equipmentID = :equipment_id");
        $stmt->execute([':equipment_id' => $equipmentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createWarning($equipmentID, $hazardType, $warningMessage, $disposalInstructions, $db): bool {
        $stmt = $db->prepare("INSERT INTO HazmatWarnings 
                              (equipmentID, hazardType, warningMessage, disposalInstructions)
                              VALUES (:equipment_id, :hazard_type, :warning_message, :disposal_instructions)");
        return $stmt->execute([
            ':equipment_id'          => $equipmentID,
            ':hazard_type'           => $hazardType,
            ':warning_message'       => $warningMessage,
            ':disposal_instructions' => $disposalInstructions
        ]);
    }
}