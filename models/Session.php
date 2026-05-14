<?php

require_once __DIR__ . '/../config/Database.php';

class Session {

    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function startSession($bookingID, $userID, $equipmentID)
    {
        $sql = "INSERT INTO Sessions
                    (bookingID, userID, equipmentID, actualStartTime, sessionStatus)
                VALUES
                    (:booking, :user, :equip, NOW(), 'active')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking' => $bookingID,
            ':user'    => $userID,
            ':equip'   => $equipmentID
        ]);

        return $this->db->lastInsertId();
    }

    public function endSession($sessionID)
    {
        $sql = "UPDATE Sessions
                   SET actualEndTime = NOW(),
                       sessionStatus = 'completed'
                 WHERE sessionID = :id
                   AND sessionStatus = 'active'";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([':id' => $sessionID]);

        if ($success && $stmt->rowCount() > 0) {
            return $this->calculateFinalCost($sessionID);
        }

        return false;
    }

    public function calculateFinalCost($sessionID)
    {
        $sql = "SELECT s.actualStartTime, s.actualEndTime, e.hourlyRateExternal
                FROM Sessions s
                JOIN Equipment e ON s.equipmentID = e.equipmentID
                WHERE s.sessionID = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $sessionID]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data || !$data['actualEndTime']) {
            return false;
        }

        $start = new DateTime($data['actualStartTime']);
        $end = new DateTime($data['actualEndTime']);
        $interval = $start->diff($end);

        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);
        $totalCost = $hours * (float)$data['hourlyRateExternal'];

        $updateSql = "UPDATE Sessions
                         SET durationHours = :hours,
                             totalCost = :cost
                       WHERE sessionID = :id";

        $updateStmt = $this->db->prepare($updateSql);
        return $updateStmt->execute([
            ':hours' => round($hours, 2),
            ':cost'  => round($totalCost, 2),
            ':id'    => $sessionID
        ]);
    }

    public function getSessionById($sessionID)
    {
        $stmt = $this->db->prepare("SELECT * FROM Sessions WHERE sessionID = :id");
        $stmt->execute([':id' => $sessionID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserID($sessionID)
    {
        $stmt = $this->db->prepare("SELECT userID FROM Sessions WHERE sessionID = :id");
        $stmt->execute([':id' => $sessionID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['userID'] : null;
    }

    public function getDuration($sessionID)
    {
        $stmt = $this->db->prepare("SELECT durationHours FROM Sessions WHERE sessionID = :id");
        $stmt->execute([':id' => $sessionID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['durationHours'] : 0;
    }

    public function getActiveSessionsByUser($userID)
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, e.equipmentName
             FROM Sessions s
             JOIN Equipment e ON s.equipmentID = e.equipmentID
             WHERE s.userID = :uid AND s.sessionStatus = 'active'"
        );
        $stmt->execute([':uid' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasActiveSession($userID)
    {
        $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM Sessions WHERE userID = :uid AND sessionStatus = 'active'"
                );
        $stmt->execute([':uid' => $userID]);
        return $stmt->fetchColumn() > 0;
    }

}
