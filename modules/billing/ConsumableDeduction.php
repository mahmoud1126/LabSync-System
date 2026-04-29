<?php

require_once __DIR__ . '/../../config/Database.php';

class ConsumableDeduction
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

 
    public function processConsumables($sessionID, $consumables)
    {
        $totalCost = 0.00;
        $errors = [];
        $processed = [];

foreach ($consumables as $item) {
    $consumableID = $item['consumableID'];
    $quantity = $item['quantity'];

    $consumable = $this->getConsumableWithStock($consumableID, $quantity);
    if ($consumable === false) {
        $errors[] = "consumableID {$consumableID}: unavailable or insufficient stock.";
        continue;
    }

    $lineCost = round($consumable['unitCost'] * $quantity, 2);

    $this->db->beginTransaction();

    $recorded = $this->recordUsage($sessionID, $consumableID, $quantity, $lineCost);
    if (!$recorded) {
        $this->db->rollBack();
        $errors[] = "consumableID {$consumableID}: failed to add to SessionConsumables.";
        continue;
    }

    $deducted = $this->deductStock($consumableID, $quantity);
    if (!$deducted) {
        $this->db->rollBack();
        $errors[] = "consumableID {$consumableID}: stock deduction failed.";
        continue;
    }

    $this->db->commit();

    $totalCost += $lineCost;
    $processed[] = [
        'consumableID' => $consumableID,
        'consumableName' => $consumable['consumableName'],
        'quantity' => $quantity,
        'unitCost' => $consumable['unitCost'],
        'lineCost' => $lineCost,
    ];
}

        return [
            'success' => empty($errors),
            'totalCost' => round($totalCost, 2),
            'errors' => $errors,
            'processed' => $processed,
        ];
    }


    private function getConsumableWithStock($consumableID, $requiredQty)
    {
        $stmt = $this->db->prepare(
            "SELECT consumableID, consumableName, unitCost, stockQuantity
               FROM Consumables
              WHERE consumableID = :id
                AND stockQuantity >= :qty"
        );
        $stmt->execute([':id' => $consumableID, ':qty' => $requiredQty]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: false;
    }


    private function recordUsage($sessionID, $consumableID, $quantity, $totalCost)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO SessionConsumables
                 (sessionID, consumableID, quantityUsed, totalCost)
             VALUES
                 (:sessionID, :consumableID, :qty, :cost)"
        );
        return $stmt->execute([
            ':sessionID' => $sessionID,
            ':consumableID' => $consumableID,
            ':qty' => $quantity,
            ':cost' => $totalCost,
        ]);
    }


    private function deductStock($consumableID, $quantity)
    {
        $stmt = $this->db->prepare(
            "UPDATE Consumables
                SET stockQuantity = GREATEST(stockQuantity - :qty, 0)
              WHERE consumableID  = :id"
        );
    $stmt->execute([':qty' => $quantity, ':id' => $consumableID]);

    return $stmt->rowCount() === 1;
}

    public function getSessionConsumables($sessionID)
    {
        $stmt = $this->db->prepare(
            "SELECT sc.sessionConsumableID,
                    sc.consumableID,
                    c.consumableName,
                    sc.quantityUsed,
                    c.unitCost,
                    sc.totalCost
               FROM SessionConsumables sc
               JOIN Consumables  c  ON c.consumableID = sc.consumableID
              WHERE sc.sessionID = :id"
        );
        $stmt->execute([':id' => $sessionID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getConsumablesByEquipment($equipmentID)
    {
        $stmt = $this->db->prepare(
            "SELECT consumableID, consumableName, unitCost, stockQuantity
               FROM Consumables
              WHERE equipmentID = :id
              ORDER BY consumableName ASC"
        );
        $stmt->execute([':id' => $equipmentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}