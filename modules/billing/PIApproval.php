<?php

require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/GrantTransaction.php';

class PIApproval {

    protected $grantModel;
    protected $transactionModel;

    public function __construct() {
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
    }

    public function processApproval($transactionID, $piID, $action) {
        
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM GrantTransactions WHERE transactionID = :id AND approvalStatus = 'pending'");
        $stmt->execute([':id' => $transactionID]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaction) {
            return false;
        }

        $stmt = $db->prepare("SELECT * FROM GrantPartitions WHERE transactionID = :id");
        $stmt->execute([':id' => $transactionID]);
        $partitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($action === 'approve') {
            return $this->transactionModel->approveTransaction($transactionID, $piID);
        }

        if ($action === 'reject' || $action === 'refund') {
            
            foreach ($partitions as $partition) {
                $this->grantModel->refundToBalance($partition['grantID'], $partition['amountDeducted']);
            }

            $newStatus = ($action === 'reject') ? 'rejected' : 'refunded';
            
            $sql = "UPDATE GrantTransactions 
                    SET approvalStatus = :status, 
                        approvedByPIID = :piID, 
                        approvedAt = NOW() 
                    WHERE transactionID = :id";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':status' => $newStatus,
                ':piID' => $piID,
                ':id' => $transactionID
            ]);
        }

        return false;
    }
}