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
        
        // Step 1: Use the model to fetch the transaction (Pre-condition check)
        $transaction = $this->transactionModel->getTransactionById($transactionID);

        if (!$transaction || $transaction['approvalStatus'] !== 'pending') {
            return false;
        }

        // Step 2: Handle the PI's selection
        if ($action === 'approve') {
            return $this->transactionModel->approveTransaction($transactionID, $piID);
        }

        if ($action === 'reject' || $action === 'refund') {
            
            // Fetch partitions through the model to see who needs a refund
            $partitions = $this->transactionModel->getPartitionsByTransactionId($transactionID);

            foreach ($partitions as $partition) {
                $this->grantModel->refundToBalance($partition['grantID'], $partition['amountDeducted']);
            }

            // Update the record status via the model
            if ($action === 'reject') {
                return $this->transactionModel->rejectTransaction($transactionID, $piID);
            } else {
                return $this->transactionModel->refundTransaction($transactionID, $piID);
            }
        }

        return false;
    }
}