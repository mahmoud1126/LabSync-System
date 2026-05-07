<?php

require_once 'Users.php';

class FacultyPI extends User {

    public function __construct() {
        parent::__construct();
    }

    public function getRole() {
        return 'faculty_pi';
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

    public function getMyGrants($piID): array {
        $sql = "SELECT * FROM Grants 
                WHERE piID = :pi_id
                AND grantStatus = 'active'
                ORDER BY createdAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pi_id' => $piID]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPendingTransactions($piID): array {
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
        $stmt->execute([':pi_id' => $piID]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // ... other methods (setSpendingLimit, etc) remain same
}