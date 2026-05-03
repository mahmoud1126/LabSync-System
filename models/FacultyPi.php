<?php

require_once 'Users.php';

class FacultyPI extends User {

    private $labGroupID;

    public function __construct() {
        parent::__construct();
    }

    public function getRole() {
        return 'faculty_pi';
    }



    public function createFacultyPI($userName, $userPassword, $clearanceLevel = 4, $maxBookingHoursPerWeek = 40) {
        return $this->createUser(
            $userName,
            $userPassword,
            'faculty_pi',
            'active',
            $clearanceLevel,
            false,                    
            $maxBookingHoursPerWeek
        );
    }

    public function getAllPIs() {
        return $this->getUsersByType('faculty_pi');
    }




    public function approveGrantAccess($researcherID, $grantID): bool {
        $sql = "UPDATE GrantTransactions 
                SET approvalStatus = 'approved',
                    approvedByPIID = :pi_id,
                    approvedAt     = NOW()
                WHERE grantID = :grant_id
                AND userID    = :researcher_id
                AND approvalStatus = 'pending'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':pi_id'         => $_SESSION['user_id'],
            ':grant_id'      => $grantID,
            ':researcher_id' => $researcherID
        ]);
    }

    public function revokeGrantAccess($researcherID, $grantID): void {
        $sql = "DELETE FROM GrantUserAccess 
                WHERE grantID = :grant_id
                AND userID    = :researcher_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':grant_id'      => $grantID,
            ':researcher_id' => $researcherID
        ]);
    }

    public function setSpendingLimit($researcherID, $grantID, $limit): void {
        $sql = "UPDATE GrantUserAccess gua
                JOIN Grants g ON gua.grantID = g.grantID
                SET gua.billingPercentage = :limit
                WHERE gua.grantID = :grant_id
                AND gua.userID    = :researcher_id
                AND g.piID        = :pi_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':limit'         => (float)$limit,
            ':grant_id'      => $grantID,
            ':researcher_id' => $researcherID,
            ':pi_id'         => $_SESSION['user_id']
        ]);
    }

    public function getMyGrants(): array {
        $sql = "SELECT * FROM Grants 
                WHERE piID = :pi_id
                AND grantStatus = 'active'
                ORDER BY createdAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pi_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingTransactions(): array {
        $sql = "SELECT gt.*, 
                       u.userName,
                       e.equipmentName,
                       g.grantName
                FROM GrantTransactions gt
                JOIN Users u ON gt.userID = u.userID
                JOIN Grants g ON gt.grantID = g.grantID
                LEFT JOIN Sessions s ON gt.sessionID = s.sessionID
                LEFT JOIN Equipment e ON s.equipmentID = e.equipmentID
                WHERE g.piID = :pi_id
                AND gt.approvalStatus = 'pending'
                ORDER BY gt.createdAt ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pi_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isAssignedToTransaction($piID, $transactionID): bool {
        $sql = "SELECT COUNT(*) 
                FROM GrantTransactions gt
                JOIN Grants g ON gt.grantID = g.grantID
                WHERE g.piID = :pi_id 
                AND gt.transactionID = :tx_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pi_id' => $piID,
            ':tx_id' => $transactionID
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}