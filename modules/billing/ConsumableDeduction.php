<?php

require_once __DIR__ . '/../../models/Consumables.php';
require_once __DIR__ . '/../../models/Equipment.php';


class ConsumableDeduction
{
    private Consumables $consumableModel;
    private Equipment   $equipmentModel;

    public function __construct()
    {
        $this->consumableModel = new Consumables();
        $this->equipmentModel  = new Equipment();
    }

    public function processDeduction(int $sessionID, int $equipmentID, array $itemsUsed): array
    {
        if (empty($itemsUsed)) {
            return [
                'success' => false,
                'message' => 'No consumable items provided.',
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

        $breakdown      = [];
        $totalCost      = 0.0; 

        foreach ($itemsUsed as $index => $item) {

            if (empty($item['consumableID']) || empty($item['quantity'])) {
                return [
                    'success' => false,
                    'message' => "Item at index {$index} is missing consumableID or quantity.",
                    'data'    => []
                ];
            }

            $consumableID = (int)$item['consumableID'];
            $quantity     = (int)$item['quantity'];

            if ($quantity <= 0) {
                return [
                    'success' => false,
                    'message' => "Quantity for consumable ID {$consumableID} must be greater than zero.",
                    'data'    => []
                ];
            }

            $consumable = $this->consumableModel->getConsumableById($consumableID);
            if (!$consumable) {
                return [
                    'success' => false,
                    'message' => "Consumable ID {$consumableID} not found.",
                    'data'    => []
                ];
            }

            if ((int)$consumable['equipmentID'] !== $equipmentID) {
                return [
                    'success' => false,
                    'message' => "Consumable '{$consumable['consumableName']}' does not belong to this equipment.",
                    'data'    => []
                ];
            }

            if ((int)$consumable['stockQuantity'] < $quantity) {
                return [
                    'success' => false,
                    'message' => "Insufficient stock for '{$consumable['consumableName']}'. "
                               . "Available: {$consumable['stockQuantity']}, Requested: {$quantity}.",
                    'data'    => []
                ];
            }

            $itemCost   = round((float)$consumable['unitCost'] * $quantity, 2);
            $totalCost += $itemCost;

            $breakdown[] = [
                'consumableID'   => $consumableID,
                'consumableName' => $consumable['consumableName'],
                'unitCost'       => (float)$consumable['unitCost'],
                'quantity'       => $quantity,
                'itemCost'       => $itemCost,
            ];
        }

        foreach ($breakdown as $item) {

            $deducted = $this->consumableModel->deductStock(
                $item['consumableID'],
                $item['quantity']
            );

            if (!$deducted) {
                return [
                    'success' => false,
                    'message' => "Failed to deduct stock for '{$item['consumableName']}'. Please try again.",
                    'data'    => []
                ];
            }

            $this->consumableModel->recordSessionConsumable(
                $sessionID,
                $item['consumableID'],
                $item['quantity'],
                $item['itemCost']
            );
        }

        return [
            'success' => true,
            'message' => 'Consumables deducted and recorded successfully.',
            'data'    => [
                'sessionID'        => $sessionID,
                'equipmentID'      => $equipmentID,
                'breakdown'        => $breakdown,
                'totalConsumableCost' => round($totalCost, 2),
            ]
        ];
    }
}