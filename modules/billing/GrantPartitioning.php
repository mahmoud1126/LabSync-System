<?php

require_once __DIR__ . '/../models/Grant.php';
require_once __DIR__ . '/../models/GrantTransaction.php';

class GrantPartitioning {

    protected $grantModel;
    protected $transactionModel;

    public function __construct() {
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
    }

    public function partitionAndDeduct($transactionID, $totalBill, $grants) {
        
        if (empty($grants)) {
            return false;
        }

        $totalPercentage = 0;
        foreach ($grants as $g) {
            $totalPercentage += $g['percentage'];
        }

        if ($totalPercentage != 100) {
            return false;
        }

        foreach ($grants as $g) {
            $grantID = $g['grantID'];
            $percentage = $g['percentage'];
            $amountToDeduct = ($totalBill * $percentage) / 100;

            $success = $this->grantModel->deductFromBalance($grantID, $amountToDeduct);

            if ($success) {
                $this->transactionModel->createPartition(
                    $transactionID,
                    $grantID,
                    $percentage,
                    $amountToDeduct
                );
            } else {
                return false;
            }
        }

        return true;
    }
}