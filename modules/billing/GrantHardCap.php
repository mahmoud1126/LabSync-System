<?php

require_once __DIR__ . '/../../models/Grant.php';
require_once __DIR__ . '/../../models/GrantTransaction.php';
require_once __DIR__ . '/../../models/AuditLog.php';


class GrantHardCap
{
    private Grant  $grantModel;
    private GrantTransaction $transactionModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->grantModel = new Grant();
        $this->transactionModel = new GrantTransaction();
        $this->auditLog = new AuditLog();
    }

    public function enforce(
        int   $grantID,
        int   $userID,
        float $totalCost,
        int   $sessionID,
        int   $bookingID,
        float $baseCost  = 0.0,
        float $consumableCost = 0.0,
        float $overheadCost   = 0.0
    ): array {

        if ($totalCost <= 0) {
            return [
                'success' => false,
                'message' => 'Total cost must be greater than zero.',
                'data'  => []
            ];
        }

        $grant = $this->grantModel->getGrantById($grantID);
        if (!$grant) {
            return [
                'success' => false,
                'message' => 'Grant not found.',
                'data' => []
            ];
        }

        if ($grant['grantStatus'] !== 'active') {
            return [
                'success' => false,
                'message' => "Grant '{$grant['grantName']}' is not active (status: {$grant['grantStatus']}).",
                'data'  => []
            ];
        }

        if ($grant['expirationDate'] < date('Y-m-d')) {
            return [
                'success' => false,
                'message' => "Grant '{$grant['grantName']}' has expired on {$grant['expirationDate']}.",
                'data'  => []
            ];
        }

        $hasAccess = $this->grantModel->userHasAccessToGrant($grantID, $userID);
        if (!$hasAccess) {
            return [
                'success' => false,
                'message' => 'User does not have access to this grant.',
                'data' => []
            ];
        }

        $currentBalance = (float)$grant['currentBalance'];

        if ($currentBalance < $totalCost) {
            return [
                'success' => false,
                'message' => "Insufficient grant balance. "
                           . "Required: \${$totalCost}, Available: \${$currentBalance}. "
                           . "Transaction blocked by hard cap.",
                'data'    => [
                    'grantID' => $grantID,
                    'required' => $totalCost,
                    'available' => $currentBalance,
                    'shortfall' => round($totalCost - $currentBalance, 2),
                ]
            ];
        }

        $deducted = $this->grantModel->deductFromBalance($grantID, $totalCost);

        if (!$deducted) {
            return [
                'success' => false,
                'message' => 'Failed to deduct from grant balance. The grant may have been modified concurrently. Please retry.',
                'data' => []
            ];
        }

        $description = "Session #{$sessionID} charge: base=\${$baseCost}, "
                     . "overhead=\${$overheadCost}, consumables=\${$consumableCost}";

        $this->transactionModel->createTransaction(
            $grantID,
            $userID,
            $totalCost,
            'deduction',
            $description,
            $baseCost,
            $consumableCost,
            $overheadCost,
            $sessionID,
            $bookingID
        );

        $newBalance = round($currentBalance - $totalCost, 2);

        $this->auditLog->log(
            $userID,
            'GRANT_HARD_CAP_DEDUCTION',
            'Grants',
            $grantID,
            json_encode(['currentBalance' => $currentBalance]),
            json_encode(['currentBalance' => $newBalance]),
            "Hard cap enforced. Deducted \${$totalCost} from grant '{$grant['grantName']}' "
          . "for session #{$sessionID}. New balance: \${$newBalance}."
        );

        return [
            'success' => true,
            'message' => "Amount of \${$totalCost} successfully deducted from grant '{$grant['grantName']}'.",
            'data'  => [
                'grantID' => $grantID,
                'grantName'   => $grant['grantName'],
                'amountDeducted' => $totalCost,
                'balanceBefore'  => $currentBalance,
                'balanceAfter'   => $newBalance,
                'sessionID'  => $sessionID,
                'bookingID' => $bookingID,
            ]
        ];
    }
}