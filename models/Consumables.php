<?php

require_once __DIR__ . '/../config/Database.php';

class Consumables
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getConsumableById(int $consumableID): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Consumables WHERE consumableID = :id"
        );
        $stmt->execute([':id' => $consumableID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getConsumablesByEquipment(int $equipmentID): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Consumables WHERE equipmentID = :id"
        );
        $stmt->execute([':id' => $equipmentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deductStock(int $consumableID, int $quantity): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Consumables
            SET stockQuantity = stockQuantity - :qty
            WHERE consumableID = :id
             AND stockQuantity >= :qty2"
        );
        $stmt->execute([
            ':qty' => $quantity,
            ':qty2' => $quantity,
            ':id' => $consumableID
        ]);
        return $stmt->rowCount() === 1;
    }

    public function recordSessionConsumable(int $sessionID, int $consumableID, int $quantity, float $totalCost): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO SessionConsumables (sessionID, consumableID, quantityUsed, totalCost)
             VALUES (:sessionID, :consumableID, :qty, :cost)"
        );
        return $stmt->execute([
            ':sessionID'  => $sessionID,
            ':consumableID' => $consumableID,
            ':qty' => $quantity,
            ':cost' => $totalCost
        ]);
    }
}