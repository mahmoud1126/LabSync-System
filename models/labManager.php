<?php

require_once 'User.php';

class LabManager extends User {

    private $facilityID;
    private $managedEquipmentIDs;

    public function getRole() {
        return 'lab_manager';
    }

    public function submitIncidentReport($userID, $equipmentID, $incidentType, $description): void {
        $stmt = $this->db->prepare("INSERT INTO IncidentReports 
                                    (userID, equipmentID, reportedByID, incidentType, 
                                     description, severity, timeOfIncident)
                                    VALUES 
                                    (:user_id, :equipment_id, :reported_by, :incident_type,
                                     :description, 'medium', NOW())");
        $stmt->execute([
            ':user_id'       => $userID,
            ':equipment_id'  => $equipmentID,
            ':reported_by'   => $_SESSION['user_id'],
            ':incident_type' => $incidentType,
            ':description'   => $description
        ]);
    }

    public function reallocateCharge($transactionId, $fromGrantId, $toGrantId) {

        $stmt = $this->db->prepare("SELECT * FROM GrantTransactions 
                                    WHERE transactionID = :id 
                                    AND grantID = :grant_id");
        $stmt->execute([
            ':id'       => $transactionId,
            ':grant_id' => $fromGrantId
        ]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        $stmt = $this->db->prepare("SELECT * FROM Grants 
                                    WHERE grantID = :id 
                                    AND grantStatus = 'active'
                                    AND currentBalance >= :amount");
        $stmt->execute([
            ':id'     => $toGrantId,
            ':amount' => $transaction['amount']
        ]);

        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Insufficient funds in destination grant'];
        }

        $this->db->beginTransaction();

        try {

            $stmt = $this->db->prepare("UPDATE Grants 
                                        SET currentBalance = currentBalance + :amount 
                                        WHERE grantID = :id");
            $stmt->execute([
                ':amount' => $transaction['amount'],
                ':id'     => $fromGrantId
            ]);

            $stmt = $this->db->prepare("UPDATE Grants 
                                        SET currentBalance = currentBalance - :amount 
                                        WHERE grantID = :id");
            $stmt->execute([
                ':amount' => $transaction['amount'],
                ':id'     => $toGrantId
            ]);

            $stmt = $this->db->prepare("INSERT INTO GrantTransactions 
                                        (grantID, userID, amount, transactionType, description, createdAt)
                                        VALUES (:grant_id, :user_id, :amount, 'reallocation_out', :desc, NOW())");
            $stmt->execute([
                ':grant_id' => $fromGrantId,
                ':user_id'  => $_SESSION['user_id'],
                ':amount'   => $transaction['amount'],
                ':desc'     => 'Reallocated to grant ' . $toGrantId
            ]);

            $stmt = $this->db->prepare("INSERT INTO GrantTransactions 
                                        (grantID, userID, amount, transactionType, description, createdAt)
                                        VALUES (:grant_id, :user_id, :amount, 'reallocation_in', :desc, NOW())");
            $stmt->execute([
                ':grant_id' => $toGrantId,
                ':user_id'  => $_SESSION['user_id'],
                ':amount'   => $transaction['amount'],
                ':desc'     => 'Reallocated from grant ' . $fromGrantId
            ]);

            $stmt = $this->db->prepare("INSERT INTO SystemAuditLogs 
                                        (userID, actionType, tableAffected, recordID, description, createdAt)
                                        VALUES (:user_id, 'GRANT_REALLOCATION', 'GrantTransactions', :record_id, :desc, NOW())");
            $stmt->execute([
                ':user_id'   => $_SESSION['user_id'],
                ':record_id' => $transactionId,
                ':desc'      => json_encode([
                    'from_grant' => $fromGrantId,
                    'to_grant'   => $toGrantId,
                    'amount'     => $transaction['amount']
                ])
            ]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Charge reallocated successfully'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllGrants() {
        $stmt = $this->db->prepare("SELECT g.*, u.userName AS piName 
                                    FROM Grants g
                                    JOIN Users u ON g.piID = u.userID
                                    WHERE g.grantStatus = 'active'
                                    ORDER BY g.createdAt DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGrantTransactions($grantId) {
        $stmt = $this->db->prepare("SELECT gt.*, 
                                           u.userName,
                                           e.equipmentName
                                    FROM GrantTransactions gt
                                    JOIN Users u ON gt.userID = u.userID
                                    LEFT JOIN Sessions s ON gt.sessionID = s.sessionID
                                    LEFT JOIN Equipment e ON s.equipmentID = e.equipmentID
                                    WHERE gt.grantID = :grant_id
                                    ORDER BY gt.createdAt DESC");
        $stmt->execute([':grant_id' => $grantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllBookings() {
        $stmt = $this->db->prepare("SELECT b.*, 
                                           u.userName,
                                           e.equipmentName
                                    FROM Bookings b
                                    JOIN Users u ON b.userID = u.userID
                                    JOIN Equipment e ON b.equipmentID = e.equipmentID
                                    ORDER BY b.createdAt DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}