<?php

require_once __DIR__ . '/../../config/Database.php';


class RateCalculator
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function calculate($equipmentID, $userID, $durationHours)
    {
        $hourlyRate = $this->getEquipmentHourlyRate($equipmentID);
        if ($hourlyRate === false) {
            return false;
        }

        $multiplier = $this->getUserRateMultiplier($userID);
        if ($multiplier === false) {
            return false;
        }

        return round($hourlyRate * $multiplier * $durationHours, 2);
    }


    private function getEquipmentHourlyRate($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT hourlyRateExternal
               FROM Equipment
              WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (float) $row['hourlyRateExternal'] : false;
    }

 
    private function getUserRateMultiplier($userID)
    {
        $stmtUser = $this->db->prepare(
            "SELECT userType, isExternal
               FROM Users
              WHERE userID = :id"
        );
        $stmtUser->execute([':id' => $userID]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if ($user['userType'] === 'lab_manager') {
            return 1.00;
        }

     $stmt = $this->db->prepare(
        "SELECT rt.rateMultiplier
           FROM Users u
      LEFT JOIN RateTiers rt
             ON rt.userType = u.userType
            AND rt.isExternal = u.isExternal
          WHERE u.userID = :id
          LIMIT 1"
    );
    $stmt->execute([':id' => $userID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($row && $row['rateMultiplier'] !== null)
        ? (float) $row['rateMultiplier']
        : 1.00;
}
}