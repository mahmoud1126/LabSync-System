<?php

require_once __DIR__ . '/../../models/Equipment.php';


class OverheadCalculator
{
    private Equipment $equipmentModel;

    public function __construct()
    {
        $this->equipmentModel = new Equipment();
    }


    public function applyOverhead(int $equipmentID, float $baseCost): array
    {
        if ($baseCost < 0) {
            return [
                'success' => false,
                'message' => 'Base cost cannot be negative.',
                'data'    => []
            ];
        }

        $equipment = $this->equipmentModel->getEquipmentById($equipmentID);
        if (!$equipment) {
            return [
                'success' => false,
                'message' => 'Equipment not found.',
                'data'    => []
            ];
        }

        $overheadPercentage = (float)$equipment['overheadPercentage'];

        $overheadCost = round($baseCost * ($overheadPercentage / 100), 2);

        $totalWithOverhead = round($baseCost + $overheadCost, 2);

        return [
            'success' => true,
            'message' => 'Overhead applied successfully.',
            'data'   => [
                'equipmentID'     => $equipmentID,
                'baseCost'    => $baseCost,
                'overheadPercentage'=> $overheadPercentage,
                'overheadCost'    => $overheadCost, 
                'totalWithOverhead' => $totalWithOverhead,
            ]
        ];
    }
}