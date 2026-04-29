<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Grant.php';
require_once __DIR__ . '/../../models/GrantTransaction.php';
require_once __DIR__ . '/RateCalculator.php';
require_once __DIR__ . '/OverheadCalculator.php';
require_once __DIR__ . '/ConsumableDeduction.php';


class GrantHardCap
{
    protected $db;

    private $grantModel;
    private $transactionModel;
    private $rateCalculator;
    private $overheadCalculator;
    private $consumableDeduction;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
        $this->rateCalculator  = new RateCalculator();
        $this->overheadCalculator  = new OverheadCalculator();
        $this->consumableDeduction = new ConsumableDeduction();
    }

    public function charge(
        $grantID,
        $userID,
        $equipmentID,
        $sessionID,
        $bookingID,
        $durationHours,
        $consumables = []
    ) {
        $baseCost = $this->rateCalculator->calculate($equipmentID, $userID, $durationHours);
        if ($baseCost === false) {
           return $this->fail('Failed to calculate base price — check Equipment and RateTiers data.');
        }

        $overheadCost = $this->overheadCalculator->calculate($equipmentID, $baseCost);
        if ($overheadCost === false) {
            return $this->fail('Overhead calculation failed — equipment not found.');
        }

        $consumableCost = 0.00;
        if (!empty($consumables)) {
            $consumableResult = $this->consumableDeduction->processConsumables($sessionID, $consumables);
            if (!$consumableResult['success']) {
                $consumableCost = $consumableResult['totalCost'];
            } else {
                $consumableCost = $consumableResult['totalCost'];
            }
        }

        $totalCharge = round($baseCost + $overheadCost + $consumableCost, 2);

        if (!$this->grantModel->hasSufficientBalance($grantID, $totalCharge)) {
            return $this->fail(
                "Grant balance is insufficient. Required: {$totalCharge}$. " .
                "Available: " . ($this->grantModel->getGrantBalance($grantID) ?? 0) . "$"
            );
        }

    $this->db->beginTransaction();

    $deducted = $this->grantModel->deductFromBalance($grantID, $totalCharge);
    if (!$deducted) {
       $this->db->rollBack();
       return $this->fail('Deduction failed — balance depleted by a concurrent transaction.');
    }

    $recorded = $this->transactionModel->createTransaction(
        $grantID,
        $userID,
        $totalCharge,
        'deduction',
        "Equipment session deduction | sessionID: {$sessionID} | Duration: {$durationHours} hours",
        $baseCost,
        $consumableCost,
        $overheadCost,
        $sessionID,
        $bookingID
    );
    if (!$recorded) {
        $this->db->rollBack();
        return $this->fail('Failed to record transaction — deduction rolled back.');
    }

    $this->db->commit();

        return [
            'success' => true,
            'totalCharge' => $totalCharge,
            'baseCost' => $baseCost,
            'overheadCost' => $overheadCost,
            'consumableCost' => $consumableCost,
            'reason' => '',
        ];
    }


public function refund($grantID, $userID, $amount, $reason, $sessionID = null, $bookingID = null)
{
    $this->db->beginTransaction();

    $refunded = $this->grantModel->refundToBalance($grantID, $amount);
    if (!$refunded) {
        $this->db->rollBack();
        return false;
    }

    $recorded = $this->transactionModel->createTransaction(
        $grantID,
        $userID,
        $amount,
        'refund',
        "Refund: {$reason}",
        0,
        0,
        0,
        $sessionID,
        $bookingID
    );

    if (!$recorded) {
        $this->db->rollBack();
        return false;
    }

    $this->db->commit();
    return true;
}

 
    public function estimateCharge($equipmentID, $userID, $durationHours)
    {
        $baseCost = $this->rateCalculator->calculate($equipmentID, $userID, $durationHours);
        if ($baseCost === false) {
            return false;
        }

        $overheadCost   = $this->overheadCalculator->calculate($equipmentID, $baseCost);
        $estimatedTotal = round($baseCost + $overheadCost, 2);

        return [
            'baseCost'       => $baseCost,
            'overheadCost'   => $overheadCost,
            'estimatedTotal' => $estimatedTotal,
        ];
    }

    private function fail($reason)
    {
        return [
            'success'        => false,
            'totalCharge'    => 0.00,
            'baseCost'       => 0.00,
            'overheadCost'   => 0.00,
            'consumableCost' => 0.00,
            'reason'         => $reason,
        ];
    }
}