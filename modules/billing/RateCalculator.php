<?php

require_once __DIR__ . '/../../models/Equipment.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Grant.php';


class RateCalculator
{
    private Equipment $equipmentModel;
    private User $userModel;

    public function __construct()
    {
        $this->equipmentModel = new Equipment();
        $this->userModel = new class extends User {
            public function getRole() {
                return 'generic'; 
            }
        };
        
    }

    public function calculateSessionRate(int $equipmentID, int $userID, float $actualHours): array
    {
        if ($actualHours <= 0) {
            return [
                'success' => false,
                'message' => 'Usage hours must be greater than zero.',
                'data' => []
            ];
        }

        $equipment = $this->equipmentModel->getEquipmentById($equipmentID);
        if (!$equipment) {
            return [
                'success' => false,
                'message' => 'Equipment not found.',
                'data'=> []
            ];
        }

        $user = $this->userModel->getUserById($userID);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'data' => []
            ];
        }

        $bufferHours = (
            (int)$equipment['powerUpBufferMinutes'] +
            (int)$equipment['coolDownBufferMinutes']
        ) / 60;

        $billableHours = $actualHours + $bufferHours;

        $isExternal = (bool)$user['isExternal'];
        $rateMultiplier = $isExternal ? 1.50 : 1.00;

        $hourlyRate = (float)$equipment['hourlyRateExternal'];

        $baseCost = round($hourlyRate * $rateMultiplier * $billableHours, 2);

        return [
            'success' => true,
            'message' => 'Base rate calculated successfully.',
            'data'  => [
                'equipmentID' => $equipmentID,
                'userID' => $userID,
                'actualHours'   => $actualHours,
                'bufferHours'  => round($bufferHours, 4),
                'billableHours' => round($billableHours, 4),
                'hourlyRate' => $hourlyRate,
                'isExternal'  => $isExternal,
                'rateMultiplier'  => $rateMultiplier,
                'baseCost' => $baseCost,
            ]
        ];
    }
}