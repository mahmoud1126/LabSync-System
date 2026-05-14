<?php

class TieredAccessService {

    private $roleHierarchy = [
        'guest_researcher' => 1,
        'researcher'       => 2,
        'faculty_pi'       => 3,
        'lab_manager'      => 4,
    ];

    private $permissions = [
        'guest_researcher' => [
            'can_view_equipment'     => true,
            'can_book_equipment'     => true,
            'can_cancel_own_booking' => true,
            'can_view_own_bookings'  => true,
            'can_use_dual_use'       => false,
            'can_manage_equipment'   => false,
            'can_lockout_equipment'  => false,
            'can_manage_bookings'    => false,
            'can_view_all_bookings'  => false,
            'can_manage_grants'      => false,
            'can_view_reports'       => false,
        ],
        'researcher' => [
            'can_view_equipment'     => true,
            'can_book_equipment'     => true,
            'can_cancel_own_booking' => true,
            'can_view_own_bookings'  => true,
            'can_use_dual_use'       => false,
            'can_manage_equipment'   => false,
            'can_lockout_equipment'  => false,
            'can_manage_bookings'    => false,
            'can_view_all_bookings'  => false,
            'can_manage_grants'      => false,
            'can_view_reports'       => false,
        ],
        'faculty_pi' => [
            'can_view_equipment'     => true,
            'can_book_equipment'     => true,
            'can_cancel_own_booking' => true,
            'can_view_own_bookings'  => true,
            'can_use_dual_use'       => true,
            'can_manage_equipment'   => false,
            'can_lockout_equipment'  => false,
            'can_manage_bookings'    => false,
            'can_view_all_bookings'  => true,
            'can_manage_grants'      => true,
            'can_view_reports'       => true,
        ],
        'lab_manager' => [
            'can_view_equipment'     => true,
            'can_book_equipment'     => true,
            'can_cancel_own_booking' => true,
            'can_view_own_bookings'  => true,
            'can_use_dual_use'       => true,
            'can_manage_equipment'   => true,
            'can_lockout_equipment'  => true,
            'can_manage_bookings'    => true,
            'can_view_all_bookings'  => true,
            'can_manage_grants'      => false,
            'can_view_reports'       => true,
        ],
    ];

    public function can($role, $permission) {
        if (!isset($this->permissions[$role])) {
            return false;
        }
        return $this->permissions[$role][$permission] ?? false;
    }

    public function canAccessClearance($userClearance, $requiredClearance) {
        return (int)$userClearance >= (int)$requiredClearance;
    }

    public function canAccessDualUse($role, $userClearance) {
        return $this->can($role, 'can_use_dual_use') && (int)$userClearance >= 2;
    }

    public function outranks($role, $targetRole) {
        $roleLevel   = $this->roleHierarchy[$role]       ?? 0;
        $targetLevel = $this->roleHierarchy[$targetRole] ?? 0;
        return $roleLevel > $targetLevel;
    }

    public function getPermissions($role) {
        return $this->permissions[$role] ?? [];
    }

    public function getRolesWithPermission($permission) {
        $roles = [];
        foreach ($this->permissions as $role => $perms) {
            if (!empty($perms[$permission])) {
                $roles[] = $role;
            }
        }
        return $roles;
    }
}
