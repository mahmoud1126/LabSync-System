<?php

require_once __DIR__ . '/../models/Grant.php';

class GrantExpiryLockOut {

    protected $grantModel;

    public function __construct() {
        $this->grantModel = new Grant();
    }

    public function validateGrant($grantID) {
        
        $grant = $this->grantModel->getGrantById($grantID);

        if (!$grant) {
            return [
                'status' => 'Invalid',
                'reason' => 'Grant does not exist in the system.'
            ];
        }

        $currentDate = date('Y-m-d');
        $expiryDate = $grant['expirationDate'];

        if ($currentDate > $expiryDate) {
            
            if ($grant['grantStatus'] !== 'expired') {
                $this->grantModel->updateGrantStatus($grantID, 'expired');
            }

            return [
                'status' => 'Invalid',
                'reason' => "Transaction blocked. The grant expired on {$expiryDate}."
            ];
        }

        if ($grant['grantStatus'] !== 'active') {
             return [
                'status' => 'Invalid',
                'reason' => "Transaction blocked. The grant is currently marked as '{$grant['grantStatus']}'."
            ];
        }

        return [
            'status' => 'Valid',
            'reason' => 'Grant is active and ready for use.'
        ];
    }
}