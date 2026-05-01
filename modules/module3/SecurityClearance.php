<?php


require_once __DIR__ . '/../../models/Researcher.php';
require_once __DIR__ . '/../../models/Equipment.php';

class SecurityClearance
{

    private const DUAL_USE_MIN_LEVEL = 3;

    private const LEVEL_LABELS = [
        0 => 'Level 0 (Public / Unrestricted)',
        1 => 'Level 1 (Standard Researcher)',
        2 => 'Level 2 (Advanced Researcher)',
        3 => 'Level 3 (Restricted / Dual-Use)',
        4 => 'Level 4 (Classified)',
        5 => 'Level 5 (Top Clearance)',
    ];

    private Researcher $userModel;
    private Equipment $equipmentModel;

    public function __construct()
    {
        $this->userModel = new Researcher();
        $this->equipmentModel = new Equipment();
    }

    public function verifyAccess(int $userID, int $equipmentID): array{
        $user = $this->userModel->getUserById($userID);

        if ($user['userStatus'] !== 'active') {
            return [
                'success' => false,
                'message' => "Access denied: your account is currently '{$user['userStatus']}'. Please contact a Lab Manager to restore access.",
            ];
        }

        $equipment = $this->equipmentModel->getEquipmentById($equipmentID);

        $userLevel = (int)  ($user['clearanceLevel']);
        $requiredLevel = (int)  ($equipment['requiredClearanceLevel']);
        $isDualUse = (bool) ($equipment['isDualUse'] );
        $isExternal = (bool) ($user['isExternal'] );
        $eqName = ($equipment['equipmentName']);


        if ($userLevel < $requiredLevel) {
            $needed = self::LEVEL_LABELS[$requiredLevel] ;
            $have = self::LEVEL_LABELS[$userLevel]  ;

            return [
                'success' => false,
                'message' => "Access denied: $eqName requires $needed. Your current clearance is $have. Please contact your PI to request a clearance upgrade.",
            ];
        }

        if ($isDualUse) {

            if ($userLevel < self::DUAL_USE_MIN_LEVEL) {
                $needed = self::LEVEL_LABELS[self::DUAL_USE_MIN_LEVEL];

                return [
                    'success' => false,
                    'message' => "Access denied: $eqName is flagged as Dual-Use Technology. Minimum required clearance is $needed Please complete dual-use technology training before booking.",
                ];
            }

            if ($isExternal) {
                return [
                    'success' => false,
                    'message' => "Access denied: external researchers are not permitted to operate Dual-Use Technology equipment per institutional policy.",
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Clearance verified.',
            'data'    => [
                'userLevel' => $userLevel,
                'requiredLevel' => $requiredLevel,
                'isDualUse' => $isDualUse,
            ],
        ];
    }


    public function getCatalogFlag(array $equipmentRow): array{
        $required  = (int)  ($equipmentRow['requiredClearanceLevel'] ?? 0);
        $isDualUse = (bool) ($equipmentRow['isDualUse'] ?? false);

        if ($isDualUse) {
            return ['restricted' => true, 'reason' => 'DUAL_USE'];
        }
        if ($required >= self::DUAL_USE_MIN_LEVEL) {
            return ['restricted' => true, 'reason' => 'HIGH_CLEARANCE'];
        }
        return ['restricted' => false, 'reason' => null];
    }

    public function filterAccessibleEquipment(int $userID, array $equipmentList): array
    {
        $allowed = [];
        foreach ($equipmentList as $eq) {
            $check = $this->verifyAccess($userID, (int) $eq['equipmentID']);
            if ($check['success']) {
                $allowed[] = $eq;
            }
        }
        return $allowed;
    }
}