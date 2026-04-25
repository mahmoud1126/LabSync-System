<?php

require_once 'User.php';

class FacultyPI extends User {

    private $labGroupID;

    public function getRole() {
        return 'faculty_pi';
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
        $sql = "UPDATE GrantUserAccess 
                SET billingPercentage = :limit
                WHERE grantID = :grant_id
                AND userID    = :researcher_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':limit'         => $limit,
            ':grant_id'      => $grantID,
            ':researcher_id' => $researcherID
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
                       e.equipmentName
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
}