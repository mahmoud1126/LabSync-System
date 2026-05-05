<?php

require_once __DIR__ . '/../config/Database.php';

class Grant {

    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


     public function createGrant($grantName, $piID, $totalBudget, $expirationDate)
    {
        if ($totalBudget <= 0) {
            return 0;
        }
               $initialStatus = ($expirationDate >= date('Y-m-d')) ? 'active' : 'inactive';

        $sql = "INSERT INTO Grants
                    (grantName, piID, totalBudget, currentBalance, grantStatus, expirationDate)
                VALUES
                    (:name, :piID, :total, :balance, :status, :expiry)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'    => trim($grantName),
            ':piID'    => $piID,
            ':total'   => $totalBudget,
            ':balance' => $totalBudget,
            ':status'  => $initialStatus,
            ':expiry'  => $expirationDate,
        ]);
        return $this->db->lastInsertId();
    }


        public function getGrantById($grantID)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Grants WHERE grantID = :id"
        );
        $stmt->execute([':id' => $grantID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


        public function getGrantsByPI($piID)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Grants
              WHERE piID = :piID
              ORDER BY createdAt DESC"
        );
        $stmt->execute([':piID' => $piID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGrantsByResearcher($userID) {
    $query = "
        SELECT g.*, gua.accessLevel, gua.dateAssigned 
        FROM Grants g
        JOIN GrantUserAccess gua ON g.grantID = gua.grantID
        WHERE gua.userID = :userID AND g.grantStatus = 'active'
    ";

    try {
        $stmt = $this->db->prepare($query);
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}


        public function getActiveGrants()
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Grants
              WHERE grantStatus    = 'active'
                AND expirationDate >= CURDATE()
                AND currentBalance  > 0
              ORDER BY expirationDate ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function getAllGrants()
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, u.userName AS piName
               FROM Grants g
               JOIN Users  u ON u.userID = g.piID
              ORDER BY g.createdAt DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function getGrantsByStatus($status)
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, u.userName AS piName
               FROM Grants g
               JOIN Users  u ON u.userID = g.piID
              WHERE g.grantStatus = :status
              ORDER BY g.expirationDate ASC"
        );
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function updateGrantStatus($grantID, $newStatus)
    {
        $stmt = $this->db->prepare(
            "UPDATE Grants
                SET grantStatus = :status
              WHERE grantID = :id"
        );
        return $stmt->execute([':status' => $newStatus, ':id' => $grantID]);
    }


        public function updateGrantName($grantID, $newName)
    {
        $stmt = $this->db->prepare(
            "UPDATE Grants
                SET grantName = :name
              WHERE grantID = :id"
        );
        return $stmt->execute([':name' => trim($newName), ':id' => $grantID]);
    }


        public function extendGrantExpiry($grantID, $newDate)
    {
        if ($newDate <= date('Y-m-d')) {
            return false;
        }

        $sql = "UPDATE Grants
                   SET expirationDate = :date,
                       grantStatus    = CASE
                       WHEN grantStatus = 'expired'
                        THEN 'active'
                        ELSE grantStatus
                            END
                 WHERE grantID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':date' => $newDate, ':id' => $grantID]);
    }


        public function expireStaleGrants()
    {
        $stmt = $this->db->prepare(
            "UPDATE Grants
                SET grantStatus = 'expired'
              WHERE grantStatus  = 'active'
                AND expirationDate < CURDATE()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }


        public function getGrantBalance($grantID)
    {
        $stmt = $this->db->prepare(
            "SELECT currentBalance FROM Grants WHERE grantID = :id"
        );
        $stmt->execute([':id' => $grantID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row !== false) ? (float) $row['currentBalance'] : null;
    }


        public function hasSufficientBalance($grantID, $requiredAmount)
    {
        $stmt = $this->db->prepare(
            "SELECT currentBalance, grantStatus, expirationDate
               FROM Grants
              WHERE grantID = :id"
        );
        $stmt->execute([':id' => $grantID]);
        $grant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$grant)
               return false;
        if ($grant['grantStatus'] !== 'active')
               return false;
        if ($grant['expirationDate'] < date('Y-m-d'))
               return false;
        return (float)$grant['currentBalance'] >= (float)$requiredAmount;
    }


        public function deductFromBalance($grantID, $amount)
    {
        if ($amount <= 0) {
            return false;
        }

        $sql = "UPDATE Grants
                   SET currentBalance = currentBalance - :amount,
                       grantStatus    = CASE
                            WHEN (currentBalance - :amount2) <= 0
                            THEN 'depleted'
                            ELSE grantStatus
                            END
                 WHERE grantID= :id
                   AND grantStatus = 'active'
                   AND currentBalance >= :amount3
                   AND expirationDate >= CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':amount'  => $amount,
            ':amount2' => $amount,
            ':amount3' => $amount,
            ':id' => $grantID,
        ]);

        return $stmt->rowCount() === 1;
    }


        public function refundToBalance($grantID, $amount)
    {
        if ($amount <= 0) {
            return false;
        }

        $sql = "UPDATE Grants
                   SET currentBalance = LEAST(currentBalance + :amount, totalBudget),
                       grantStatus = CASE
                              WHEN grantStatus    = 'depleted'
                              AND expirationDate >= CURDATE()
                              THEN 'active'
                              ELSE grantStatus
                                END
                 WHERE grantID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':amount' => $amount, ':id' => $grantID]);
    }


        public function getExpiringGrants($daysAhead = 30)
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, u.userName AS piName
               FROM Grants g
               JOIN Users  u ON u.userID = g.piID
              WHERE g.grantStatus   = 'active'
                AND g.expirationDate BETWEEN CURDATE()
                   AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
              ORDER BY g.expirationDate ASC"
        );
        $stmt->execute([':days' => $daysAhead]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function addUserToGrant($grantID, $userID, $billingPercentage = 100.00)
    {
        if ($billingPercentage <= 0 || $billingPercentage > 100) {
            return false;
        }

        $sql = "INSERT INTO GrantUserAccess (grantID, userID, billingPercentage)
                VALUES (:grantID, :userID, :pct)
                ON DUPLICATE KEY UPDATE billingPercentage = :pct2";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':grantID' => $grantID,
            ':userID' => $userID,
            ':pct' => $billingPercentage,
            ':pct2' => $billingPercentage,
        ]);
    }


        public function removeUserFromGrant($grantID, $userID)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM GrantUserAccess
              WHERE grantID = :grantID AND userID = :userID"
        );
        return $stmt->execute([':grantID' => $grantID, ':userID' => $userID]);
    }


        public function userHasAccessToGrant($grantID, $userID)
    {
        $stmt = $this->db->prepare(
            "SELECT 1
               FROM GrantUserAccess gua
               JOIN Grants g ON g.grantID = gua.grantID
              WHERE gua.grantID = :grantID
                AND gua.userID = :userID
                AND g.grantStatus = 'active'
                AND g.expirationDate >= CURDATE()
              LIMIT 1"
        );
        $stmt->execute([':grantID' => $grantID, ':userID' => $userID]);
        return (bool) $stmt->fetchColumn();
    }


        public function getUsersOnGrant($grantID)
    {
        $stmt = $this->db->prepare(
            "SELECT gua.accessID,
                    gua.userID,
                    gua.billingPercentage,
                    u.userName,
                    u.userType,
                    u.userStatus
               FROM GrantUserAccess gua
               JOIN Users           u  ON u.userID = gua.userID
              WHERE gua.grantID = :grantID
              ORDER BY u.userName ASC"
        );
        $stmt->execute([':grantID' => $grantID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function getGrantsForUser($userID)
    {
        $stmt = $this->db->prepare(
            "SELECT g.grantID,
                    g.grantName,
                    g.currentBalance,
                    g.expirationDate,
                    gua.billingPercentage
               FROM GrantUserAccess gua
               JOIN Grants          g  ON g.grantID = gua.grantID
              WHERE gua.userID      = :userID
                AND g.grantStatus   = 'active'
                AND g.expirationDate >= CURDATE()
              ORDER BY g.expirationDate ASC"
        );
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function updateBillingPercentage($grantID, $userID, $newPercentage)
    {
        if ($newPercentage <= 0 || $newPercentage > 100) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE GrantUserAccess
                SET billingPercentage = :pct
              WHERE grantID = :grantID AND userID = :userID"
        );
        return $stmt->execute([
            ':pct'     => $newPercentage,
            ':grantID' => $grantID,
            ':userID'  => $userID,
        ]);
    }
    
}
