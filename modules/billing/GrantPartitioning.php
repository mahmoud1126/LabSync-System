<?php

require_once __DIR__ . '/../../models/Grant.php';
require_once __DIR__ . '/../../models/GrantTransaction.php';

class GrantPartitioning {

    protected $grantModel;
    protected $transactionModel;

    public function __construct() {
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
    }

    public function partitionAndDeduct($transactionID, $totalBill, $grants) {
        
        if (empty($grants)) {
            throw new Exception("No grants provided for partitioning.");
        }

        // 1. Verify total percentage equals exactly 100% (with float rounding protection)
        $totalPercentage = 0;
        foreach ($grants as $g) {
            $totalPercentage += (float)$g['percentage'];
        }

        if (round($totalPercentage, 2) != 100.00) {
            throw new Exception("Grant coverage percentages do not total 100%. Current total: " . round($totalPercentage, 2) . "%");
        }

        // 2. PRE-FLIGHT CHECK: Ensure ALL grants have enough money and are active BEFORE deducting anything
        foreach ($grants as $g) {
            $amountToDeduct = round(($totalBill * (float)$g['percentage']) / 100, 2);
            
            if ($amountToDeduct > 0) {
                if (!$this->grantModel->hasSufficientBalance($g['grantID'], $amountToDeduct)) {
                    throw new Exception("Insufficient funds or inactive status for Grant ID: " . $g['grantID']);
                }
            }
        }

        // 3. Execution: Deduct and Create Partitions
        foreach ($grants as $g) {
            $grantID = $g['grantID'];
            $percentage = (float)$g['percentage'];
            $amountToDeduct = round(($totalBill * $percentage) / 100, 2);

            // Skip 0% grants
            if ($amountToDeduct <= 0) continue;

            $success = $this->grantModel->deductFromBalance($grantID, $amountToDeduct);

            if ($success) {
                $this->transactionModel->createPartition(
                    $transactionID,
                    $grantID,
                    $percentage,
                    $amountToDeduct
                );
            } else {
                // Because of our pre-flight check, this should theoretically never happen,
                // but it's here as a final failsafe against database locks/errors.
                throw new Exception("Critical Database Error while deducting from Grant ID: " . $grantID);
            }
        }

        return true;
    }
}