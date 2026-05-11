<?php

require_once __DIR__ . '/../config/Database.php';

class Equipment {

    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getEquipmentById($equipmentID)
    {
        // FIXED: Now fetches the most recent safety briefing content using a subquery
        $stmt = $this->db->prepare(
            "SELECT e.*, e.equipmentName AS name,
                   (SELECT briefingContent FROM SafetyBriefings sb WHERE sb.equipmentID = e.equipmentID ORDER BY briefingID DESC LIMIT 1) AS briefingContent
             FROM Equipment e 
             WHERE e.equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllEquipment()
    {
        // FIXED: Added a check to see if a safety briefing exists
        $stmt = $this->db->prepare(
            "SELECT e.*, e.equipmentName AS name,
                   (SELECT COUNT(*) FROM SafetyBriefings sb WHERE sb.equipmentID = e.equipmentID) AS hasBriefing
             FROM Equipment e 
             ORDER BY e.equipmentName ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquipmentName($equipmentID)
    {
        $stmt = $this->db->prepare("SELECT equipmentName FROM Equipment WHERE equipmentID = :id");
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['equipmentName'] : null;
    }

    public function getHourlyRate($equipmentID)
    {
        $stmt = $this->db->prepare("SELECT hourlyRateExternal FROM Equipment WHERE equipmentID = :id");
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (float) $result['hourlyRateExternal'] : 0.00;
    }

    public function getEquipmentAvailability($equipmentID)
    {
        $stmt = $this->db->prepare("SELECT equipmentStatus FROM Equipment WHERE equipmentID = :id");
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result && $result['equipmentStatus'] === 'available');
    }

    public function getRequiredClearanceLevel($equipmentID)
    {
        $stmt = $this->db->prepare("SELECT requiredClearanceLevel FROM Equipment WHERE equipmentID = :id");
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['requiredClearanceLevel'] : 0;
    }

    public function updateHourlyRate($equipmentID, $newRate)
    {
        if ($newRate < 0) return false;
        $stmt = $this->db->prepare("UPDATE Equipment SET hourlyRateExternal = :rate WHERE equipmentID = :id");
        return $stmt->execute([':rate' => (float) $newRate, ':id' => $equipmentID]);
    }

    public function needsConsumables($equipmentID)
    {
        $stmt = $this->db->prepare("SELECT 1 FROM Consumables WHERE equipmentID = :id LIMIT 1");
        $stmt->execute([':id' => $equipmentID]);
        return (bool) $stmt->fetchColumn();
    }

    public function calculateUsageCost($equipmentID, $usageHours)
    {
        if ($usageHours <= 0) return 0.00;

        $stmt = $this->db->prepare(
            "SELECT hourlyRateExternal, overheadPercentage, powerUpBufferMinutes, coolDownBufferMinutes
             FROM Equipment WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$equipment) return 0.00;

        $baseRate    = (float) $equipment['hourlyRateExternal'];
        $overhead    = (float) $equipment['overheadPercentage'];
        $bufferHours = ((int) $equipment['powerUpBufferMinutes'] + (int) $equipment['coolDownBufferMinutes']) / 60;

        $effectiveHourlyRate = $baseRate + ($baseRate * ($overhead / 100));
        $billedHours         = $usageHours + $bufferHours;

        return round($effectiveHourlyRate * $billedHours, 2);
    }

    public function updateEquipmentStatus($equipmentID, $newStatus)
    {
        $validStatuses = ['available', 'in_use', 'locked_out', 'under_maintenance', 'calibration_needed'];
        if (!in_array($newStatus, $validStatuses)) return false;

        $stmt = $this->db->prepare("UPDATE Equipment SET equipmentStatus = :status WHERE equipmentID = :id");
        return $stmt->execute([':status' => $newStatus, ':id' => $equipmentID]);
    }

    public function logUsageHours($equipmentID, $hours)
    {
        if ($hours <= 0) return false;

        $sql = "UPDATE Equipment
                   SET totalUsageHours         = totalUsageHours + :hours,
                       currentCalibrationHours = currentCalibrationHours + :hours2,
                       equipmentStatus         = CASE
                           WHEN (currentCalibrationHours + :hours3) >= calibrationThresholdHours THEN 'calibration_needed'
                           ELSE equipmentStatus
                       END
                 WHERE equipmentID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
             ':hours'  => $hours, ':hours2' => $hours, ':hours3' => $hours, ':id' => $equipmentID
        ]);
    }

    public function getDependencies($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT e.* FROM Equipment e
             JOIN EquipmentDependencies ed ON e.equipmentID = ed.secondaryEquipmentID
             WHERE ed.primaryEquipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function areDependenciesAvailable($equipmentID)
    {
        $dependencies = $this->getDependencies($equipmentID);
        foreach ($dependencies as $dep) {
            if ($dep['equipmentStatus'] !== 'available') return false;
        }
        return true;
    }

    public function addDependency($primaryID, $secondaryID)
    {
        if ($primaryID == $secondaryID) return false;
        $stmt = $this->db->prepare("INSERT IGNORE INTO EquipmentDependencies (primaryEquipmentID, secondaryEquipmentID) VALUES (:pID, :sID)");
        return $stmt->execute([':pID' => $primaryID, ':sID' => $secondaryID]);
    }

    public function removeDependency($primaryID, $secondaryID)
    {
        $stmt = $this->db->prepare("DELETE FROM EquipmentDependencies WHERE primaryEquipmentID = :pID AND secondaryEquipmentID = :sID");
        return $stmt->execute([':pID' => $primaryID, ':sID' => $secondaryID]);
    }

    public function deleteEquipment($equipmentID)
    {
        try {
            $this->db->beginTransaction();
            $stmt1 = $this->db->prepare("DELETE FROM EquipmentDependencies WHERE primaryEquipmentID = :id OR secondaryEquipmentID = :id");
            $stmt1->execute([':id' => $equipmentID]);

            $stmt2 = $this->db->prepare("DELETE FROM Bookings WHERE equipmentID = :id");
            $stmt2->execute([':id' => $equipmentID]);

            $stmt3 = $this->db->prepare("DELETE FROM Equipment WHERE equipmentID = :id");
            $stmt3->execute([':id' => $equipmentID]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function createEquipment($data) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO Equipment 
                (equipmentName, equipmentStatus, hourlyRateExternal, requiredClearanceLevel, powerUpBufferMinutes, coolDownBufferMinutes) 
                VALUES (:name, :status, :rate, :clearance, :powerUp, :coolDown)");
            
            $stmt->execute([
                ':name'      => $data['equipmentName'],
                ':status'    => $data['equipmentStatus'],
                ':rate'      => $data['hourlyRateExternal'],
                ':clearance' => $data['requiredClearanceLevel'],
                ':powerUp'   => $data['powerUpBufferMinutes'] ?? 0,
                ':coolDown'  => $data['coolDownBufferMinutes'] ?? 0
            ]);

            $equipmentID = $this->db->lastInsertId();

            if (!empty($data['briefingContent'])) {
                $stmtB = $this->db->prepare("INSERT INTO SafetyBriefings (equipmentID, briefingContent) VALUES (:eid, :content)");
                $stmtB->execute([
                    ':eid'     => $equipmentID,
                    ':content' => $data['briefingContent']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // FIXED: Safely checks and updates/inserts Briefings without relying on ON DUPLICATE KEY
    public function updateEquipment($id, $data) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE Equipment 
                                        SET equipmentName = :name, 
                                            equipmentStatus = :status, 
                                            hourlyRateExternal = :rate, 
                                            requiredClearanceLevel = :clearance,
                                            powerUpBufferMinutes = :powerUp,
                                            coolDownBufferMinutes = :coolDown
                                        WHERE equipmentID = :id");
            $stmt->execute([
                ':name'      => $data['equipmentName'],
                ':status'    => $data['equipmentStatus'],
                ':rate'      => $data['hourlyRateExternal'],
                ':clearance' => $data['requiredClearanceLevel'],
                ':powerUp'   => $data['powerUpBufferMinutes'] ?? 0,
                ':coolDown'  => $data['coolDownBufferMinutes'] ?? 0,
                ':id'        => $id
            ]);

            // Handle Safety Briefing safely
            if (!empty($data['briefingContent'])) {
                $stmtCheck = $this->db->prepare("SELECT briefingID FROM SafetyBriefings WHERE equipmentID = :id LIMIT 1");
                $stmtCheck->execute([':id' => $id]);
                $exists = $stmtCheck->fetchColumn();

                if ($exists) {
                    $stmtB = $this->db->prepare("UPDATE SafetyBriefings SET briefingContent = :content WHERE equipmentID = :id");
                } else {
                    $stmtB = $this->db->prepare("INSERT INTO SafetyBriefings (equipmentID, briefingContent) VALUES (:id, :content)");
                }
                $stmtB->execute([':id' => $id, ':content' => $data['briefingContent']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}