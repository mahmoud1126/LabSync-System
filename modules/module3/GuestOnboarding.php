<?php

require_once __DIR__ . '/../../models/GuestResearcher.php';
require_once __DIR__ . '/../../models/AuditLog.php';

class GuestOnboarding{

    private const MAX_ONBOARDING_DAYS = 365;

    private GuestResearcher $guestModel;
    private AuditLog  $auditLog;

    public function __construct(){
        $this->guestModel = new GuestResearcher();
        $this->auditLog   = new AuditLog();
    }


    public function onboard(array $data): array{

        $required = ['userName', 'userPassword', 'institution', 'expirationDate', 'sponsorPIID'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success'=> false,
                    'message' => "Missing required field: {$field}",
                ];
            }
        }

        $expTs = strtotime($data['expirationDate']);
        if ($expTs === false) {
            return [
                'success' => false,
                'message' => 'Invalid expiration date format. Use YYYY-MM-DD.',
            ];
        }
        if ($expTs <= time()) {
            return [
                'success' => false,
                'message' => 'Expiration date must be in the future.',
            ];
        }
        $maxAllowed = strtotime('+' . self::MAX_ONBOARDING_DAYS . ' days');
        if ($expTs > $maxAllowed) {
            return [
                'success' => false,
                'message' => 'Onboarding period cannot exceed '. self::MAX_ONBOARDING_DAYS . ' days from today.',
            ];
        }


        $clearanceLevel = (int) ($data['clearanceLevel'] ?? 0);
        $maxBookingHoursPerWeek = (int) ($data['maxBookingHoursPerWeek'] ?? 20);
        
        // NEW: Grab the tax rate (default 100%)
        $taxRate = (float) ($data['taxRate'] ?? 100.00);

        try {
            // NEW: Added $taxRate to the creation method
            $this->guestModel->createGuestResearcher(
                $data['userName'], 
                $data['userPassword'], 
                $data['institution'], 
                $data['expirationDate'], 
                (int) $data['sponsorPIID'],
                $clearanceLevel, 
                $maxBookingHoursPerWeek, 
                $taxRate
            );
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create guest account: ' . $e->getMessage(),
            ];
        }

        $this->auditLog->log(
            (int) $data['sponsorPIID'],
            'GUEST_ONBOARDED',
            'GuestResearchers',
            null,
            null,
            json_encode([
                'userName' => $data['userName'],
                'institution' => $data['institution'],
                'expirationDate' => $data['expirationDate'],
                'sponsorPIID' => (int) $data['sponsorPIID'],
                'taxRate' => $taxRate
            ]),
        );

        return [
            'success' => true,
            'message' => 'Guest researcher onboarded successfully. '
                       . "Account active until {$data['expirationDate']}.",
            'data'    => ['expirationDate' => $data['expirationDate']],
        ];
    }

    public function extendExpiration( $userID, $newExpirationDate, $approverID):array {

        $newTs = strtotime($newExpirationDate);
        if ($newTs === false || $newTs <= time()) {
            return [
                'success' => false,
                'message' => 'New expiration date must be a valid future date.',
            ];
        }
        $maxAllowed = strtotime('+' . self::MAX_ONBOARDING_DAYS . ' days');
        if ($newTs > $maxAllowed) {
            return [
                'success' => false,
                'message' => 'Extension cannot exceed '. self::MAX_ONBOARDING_DAYS . ' days from today.',
            ];
        }


        $guest = $this->guestModel->getGuestResearcherById($userID);
        if (!$guest) {
            return ['success' => false, 'message' => 'Guest not found.'];
        }

        $oldDate    = $guest['expirationDate'];
        $oldStatus  = $guest['userStatus'];

        $this->guestModel->extendExpiration($userID, $newExpirationDate);

        if ($oldStatus !== 'active') {
            $this->guestModel->updateStatus($userID, 'active');
        }

        $this->auditLog->log(
            $approverID,
            'GUEST_EXTENDED',
            'Users',
            $userID,
            json_encode([
                'expirationDate' => $oldDate,
                'userStatus'     => $oldStatus,
            ]),
            json_encode([
                'expirationDate' => $newExpirationDate,
                'userStatus'     => 'active',
            ]),
            "Guest expiration extended: {$oldDate} → {$newExpirationDate}"
        );

        return [
            'success' => true,
            'message' => "Guest credentials extended until {$newExpirationDate}.",
            'data'    => ['newExpirationDate' => $newExpirationDate],
        ];
    }
}