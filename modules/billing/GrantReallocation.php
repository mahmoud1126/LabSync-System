<?php

require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/GrantTransaction.php';

class GrantReallocation {

    protected $grantModel;
    protected $transactionModel;

    public function __construct() {
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
    }

    public function reallocate($sourceID, $destinationID, $amount, $userID) {
        
        $sourceBalance = $this->grantModel->getGrantBalance($sourceID);

        if ($sourceBalance === null || $sourceBalance < $amount) {
            return false;
        }

        $deducted = $this->grantModel->deductFromBalance($sourceID, $amount);

        if ($deducted) {
            $added = $this->grantModel->refundToBalance($destinationID, $amount);

            if ($added) {
                $this->transactionModel->createTransaction(
                    $sourceID,
                    $userID,
                    $amount,
                    'reallocation_out',
                    "Funds transferred to Grant ID: $destinationID"
                );

                $this->transactionModel->createTransaction(
                    $destinationID,
                    $userID,
                    $amount,
                    'reallocation_in',
                    "Funds received from Grant ID: $sourceID"
                );

                return true;
            }
        }

        return false;
    }
}