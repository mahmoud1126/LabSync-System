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
        $stmt = $this->db->prepare(
            "SELECT * FROM Equipment WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllEquipment()
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Equipment ORDER BY equipmentName ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquipmentName($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT equipmentName FROM Equipment WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['equipmentName'] : null;
    }

    public function getHourlyRate($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT hourlyRateExternal FROM Equipment WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (float) $result['hourlyRateExternal'] : 0.00;
    }

    public function getEquipmentAvailability($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT equipmentStatus FROM Equipment WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result && $result['equipmentStatus'] === 'available');
    }

    public function getRequiredClearanceLevel($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT requiredClearanceLevel FROM Equipment WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int) $result['requiredClearanceLevel'] : 0;
    }

    public function updateHourlyRate($equipmentID, $newRate)
    {
        if ($newRate < 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE Equipment
                SET hourlyRateExternal = :rate
              WHERE equipmentID = :id"
        );
        return $stmt->execute([
            ':rate' => (float) $newRate,
            ':id'   => $equipmentID
        ]);
    }

    public function needsConsumables($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM Consumables
              WHERE equipmentID = :id
              LIMIT 1"
        );
        $stmt->execute([':id' => $equipmentID]);
        return (bool) $stmt->fetchColumn();
    }

    public function calculateUsageCost($equipmentID, $usageHours)
    {
        if ($usageHours <= 0) {
            return 0.00;
        }

        $stmt = $this->db->prepare(
            "SELECT hourlyRateExternal, overheadPercentage,
                    powerUpBufferMinutes, coolDownBufferMinutes
               FROM Equipment
              WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$equipment) {
            return 0.00;
        }

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

        if (!in_array($newStatus, $validStatuses)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE Equipment
                SET equipmentStatus = :status
              WHERE equipmentID = :id"
        );
        return $stmt->execute([':status' => $newStatus, ':id' => $equipmentID]);
    }

    public function logUsageHours($equipmentID, $hours)
    {
        if ($hours <= 0) {
            return false;
        }

        $sql = "UPDATE Equipment
                   SET totalUsageHours         = totalUsageHours + :hours,
                       currentCalibrationHours = currentCalibrationHours + :hours2,
                       equipmentStatus         = CASE
                            WHEN (currentCalibrationHours + :hours3) >= calibrationThresholdHours
                            THEN 'calibration_needed'
                            ELSE equipmentStatus
                       END
                 WHERE equipmentID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
             ':hours'  => $hours,
             ':hours2' => $hours,
             ':hours3' => $hours,
             ':id'     => $equipmentID
        ]);
    }

    public function getDependencies($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT e.*
               FROM Equipment e
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
            if ($dep['equipmentStatus'] !== 'available') {
                return false;
            }
        }
        return true;
    }

    public function addDependency($primaryID, $secondaryID)
    {
        if ($primaryID == $secondaryID) return false;

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO EquipmentDependencies (primaryEquipmentID, secondaryEquipmentID)
             VALUES (:pID, :sID)"
        );
        return $stmt->execute([':pID' => $primaryID, ':sID' => $secondaryID]);
    }

    public function removeDependency($primaryID, $secondaryID)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM EquipmentDependencies
              WHERE primaryEquipmentID = :pID AND secondaryEquipmentID = :sID"
        );
        return $stmt->execute([':pID' => $primaryID, ':sID' => $secondaryID]);
    }

    public function createBooking($userID, $equipmentID, $startTime, $endTime) {
    try {
        $this->db->beginTransaction();

        $checkQuery = "SELECT COUNT(*) FROM Bookings 
                       WHERE equipmentID = :eid 
                       AND status != 'cancelled'
                       AND ((startTime < :end AND endTime > :start))";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->execute([
            ':eid'   => $equipmentID,
            ':start' => $startTime,
            ':end'   => $endTime
        ]);

        if ($stmt->fetchColumn() > 0) {
            $this->db->rollBack();
            return false;
        }

        $insertQuery = "INSERT INTO Bookings (userID, equipmentID, startTime, endTime, status) 
                        VALUES (:uid, :eid, :start, :end, 'pending')";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->execute([
            ':uid'   => $userID,
            ':eid'   => $equipmentID,
            ':start' => $startTime,
            ':end'   => $endTime
        ]);

        $safetyQuery = "SELECT safetyBriefingContent FROM Equipment WHERE equipmentID = :eid";
        $stmt = $this->db->prepare($safetyQuery);
        $stmt->execute([':eid' => $equipmentID]);
        $briefingContent = $stmt->fetchColumn();

        $this->db->commit();

        return [
            'briefingContent' => $briefingContent
        ];

    } catch (PDOException $e) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        return false;
    }
}
}

