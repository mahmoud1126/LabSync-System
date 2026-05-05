<?php

require_once __DIR__ . '/../config/Database.php';


class BillingManager  {

    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function calculateTotalCosts($transactionID) {
    $stmt = $this->db->prepare("SELECT baseCost, consumableCost, overheadCost FROM GrantTransactions WHERE transactionID = :id");
    $stmt->execute([':id' => $transactionID]);
    $costs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$costs) {
        return 0;
    }

    $total = (float)$costs['baseCost'] + (float)$costs['consumableCost'] + (float)$costs['overheadCost'];
    return $total;
}
    
    public function processSessionBilling($sessionID) {
        $sql = "SELECT s.*, e.hourlyRate 
                FROM Sessions s 
                JOIN Equipment e ON s.equipmentID = e.equipmentID 
                WHERE s.sessionID = :sessionID";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sessionID' => $sessionID]);
        $session = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$session) {
            return false;
        }

        $startTime = strtotime($session['startTime']);
        $endTime = strtotime($session['endTime']);
        $hours = ($endTime - $startTime) / 3600;
        $hours = max($hours, 0.1); 
        $totalCost = $hours * $session['hourlyRate'];

        $sql = "SELECT grantID, billingPercentage 
                FROM GrantUserAccess 
                WHERE userID = :userID";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':userID' => $session['userID']]);
        $allocations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($allocations as $alloc) {
            $splitAmount = $totalCost * ($alloc['billingPercentage'] / 100);
            $this->createTransaction(
                $session['userID'], 
                $alloc['grantID'], 
                $sessionID, 
                $splitAmount
            );
        }

        return true;
    }

    private function createTransaction($userID, $grantID, $sessionID, $amount) {
        $sql = "INSERT INTO GrantTransactions 
                (userID, grantID, sessionID, amountDeducted, approvalStatus, createdAt) 
                VALUES (:userID, :grantID, :sessionID, :amount, 'pending', NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':userID'    => $userID,
            ':grantID'   => $grantID,
            ':sessionID' => $sessionID,
            ':amount'    => $amount
        ]);
    }
}