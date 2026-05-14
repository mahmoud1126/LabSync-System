<?php

require_once __DIR__ . '/../config/Database.php';

class AuditLog {
    private $db;

    public function __construct() {
    $this->db = Database::getInstance()->getConnection();
    }

    public function log($userID, $actionType, $tableAffected, $recordID , $oldValue = null, $newValue = null, $description = ''){

    $sql="INSERT INTO SystemAuditLogs (userID, actionType, tableAffected, recordID, oldValue, newValue, ipAddress, description, createdAt)
          VALUES (:userID, :actionType, :tableAffected, :recordID, :oldValue, :newValue, :ipAddress, :description, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':userID' => $userID,
            ':actionType' => $actionType,
            ':tableAffected' => $tableAffected,
            ':recordID'=> $recordID,
            ':oldValue' => $oldValue,
            ':newValue'=> $newValue,
            ':ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':description' => $description
        ]);

        return $this->db->lastInsertId();
    }

    public function getLogByID($logID)
    {
        $sql = "SELECT al.*, u.userName
                FROM SystemAuditLogs al
                LEFT JOIN Users u ON al.userID = u.userID
                WHERE al.logID = :logID";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':logID' => $logID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllLogs(){
        $sql = "SELECT al.*, u.userName
                FROM SystemAuditLogs al
                LEFT JOIN Users u ON al.userID = u.userID
                ORDER BY al.createdAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getLogsByUserID($userID){
        $sql = "SELECT * FROM SystemAuditLogs
                WHERE userID = :userID
                ORDER BY createdAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getLogsByActionType($actionType){
        $sql = "SELECT al.*, u.userName
                FROM SystemAuditLogs al
                LEFT JOIN Users u ON al.userID = u.userID
                WHERE al.actionType = :actionType
                ORDER BY al.createdAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':actionType' => $actionType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
