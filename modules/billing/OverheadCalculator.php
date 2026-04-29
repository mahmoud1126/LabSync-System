<?php

require_once __DIR__ . '/../../config/Database.php';


class OverheadCalculator
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function calculate($equipmentID, $baseCost)
    {
        $percentage = $this->getOverheadPercentage($equipmentID);

    if ($percentage === false) {
        return false;
    }

    if ($percentage <= 0) {
        return 0.00;
    }
    return round($baseCost * ($percentage / 100), 2);
   }


    private function getOverheadPercentage($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT overheadPercentage
               FROM Equipment
              WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (float) $row['overheadPercentage'] : false;
    }


    public function getBreakdown($equipmentID, $baseCost)
    {
        $percentage  = $this->getOverheadPercentage($equipmentID);
        $percentage  = ($percentage !== false) ? $percentage : 0.00;
        $overheadCost = round($baseCost * ($percentage / 100), 2);

        return [
            'baseCost' => $baseCost,
            'overheadPercentage' => $percentage,
            'overheadCost' => $overheadCost,
            'totalCost' => round($baseCost + $overheadCost, 2),
        ];
    }
}