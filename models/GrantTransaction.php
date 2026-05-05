<?php

require_once __DIR__ . '/../config/Database.php';

class GrantTransaction {
    
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTransaction($grantID, $userID, $amount, $type, $desc, $base = 0, $cons = 0, $over = 0, $sessionID = null, $bookingID = null) {
        $sql = "INSERT INTO GrantTransactions 
                    (grantID, userID, sessionID, bookingID, amount, transactionType, description, baseCost, consumableCost, overheadCost, approvalStatus)
                VALUES 
                    (:grantID, :userID, :sessionID, :bookingID, :amount, :type, :desc, :base, :cons, :over, 'pending')";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':grantID'   => $grantID,
            ':userID'    => $userID,
            ':sessionID' => $sessionID,
            ':bookingID' => $bookingID,
            ':amount'    => $amount,
            ':type'      => $type,
            ':desc'      => $desc,
            ':base'      => $base,
            ':cons'      => $cons,
            ':over'      => $over
        ]);
    }


    public function getTransactionById($id) {
        $stmt = $this->db->prepare("SELECT * FROM GrantTransactions WHERE transactionID = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getPendingByPI($piID) {
        $sql = "SELECT gt.*, g.grantName, u.userName 
                FROM GrantTransactions gt
                JOIN Grants g ON gt.grantID = g.grantID
                JOIN Users u ON gt.userID = u.userID
                WHERE g.piID = :piID AND gt.approvalStatus = 'pending'
                ORDER BY gt.createdAt ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':piID' => $piID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getRecentByPI($piID) {
        $sql = "SELECT gt.*, g.grantName, u.userName 
                FROM GrantTransactions gt
                JOIN Grants g ON gt.grantID = g.grantID
                JOIN Users u ON gt.userID = u.userID
                WHERE g.piID = :piID AND gt.approvalStatus != 'pending'
                ORDER BY gt.createdAt DESC 
                LIMIT 15"; 

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':piID' => $piID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($transactionID, $newStatus) {
        $piID = $_SESSION['user']['userID'] ?? null;

        return match ($newStatus) {
            'approved' => $this->approveTransaction($transactionID, $piID),
            'rejected' => $this->rejectTransaction($transactionID, $piID),
            'refunded' => $this->refundTransaction($transactionID, $piID),
            default    => false,
        };
    }

    public function approveTransaction($transactionID, $piID) {
    try {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("SELECT amount, grantID FROM GrantTransactions WHERE transactionID = ?");
        $stmt->execute([$transactionID]);
        $tx = $stmt->fetch(PDO::FETCH_ASSOC);
        $grantModel = new Grant();
        $deducted = $grantModel->deductFromBalance($tx['grantID'], $tx['amount']);

        if (!$deducted) {
            throw new Exception("Insufficient grant balance.");
        }

        $sql = "UPDATE GrantTransactions 
                SET approvalStatus = 'approved', approvedByPIID = :piID, approvedAt = NOW() 
                WHERE transactionID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':piID' => $piID, ':id' => $transactionID]);

        $this->db->commit();
        return true;
    } catch (Exception $e) {
        $this->db->rollBack();
        return false;
    }
}

    public function rejectTransaction($transactionID, $piID) {
        $sql = "UPDATE GrantTransactions 
                SET approvalStatus = 'rejected', approvedByPIID = :piID, approvedAt = NOW() 
                WHERE transactionID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':piID' => $piID, ':id' => $transactionID]);
    }

    public function refundTransaction($transactionID, $piID) {
        $sql = "UPDATE GrantTransactions 
                SET approvalStatus = 'refunded', approvedByPIID = :piID, approvedAt = NOW() 
                WHERE transactionID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':piID' => $piID, ':id' => $transactionID]);
    }

    public function createPartition($transactionID, $grantID, $percentage, $amount) {
        $sql = "INSERT INTO GrantPartitions (transactionID, grantID, percentage, amountDeducted)
                VALUES (:tID, :gID, :pct, :amt)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':tID' => $transactionID, ':gID' => $grantID, ':pct' => $percentage, ':amt' => $amount]);
    }

    public function getByGrant($grantID) {
        $sql = "SELECT * FROM GrantTransactions WHERE grantID = :id ORDER BY createdAt DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $grantID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}