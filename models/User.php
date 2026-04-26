<?php

require_once __DIR__ . '/../config/database.php';

abstract class User {

    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    abstract public function getRole();


    public function createUser($userName, $userPassword, $userType,  $userStatus = 'active', $clearanceLevel = 0, $isExternal = false,$maxBookingHoursPerWeek = 20 ) {
        $sql = "INSERT INTO Users (userName, userPassword, userType, userStatus, clearanceLevel, isExternal, maxBookingHoursPerWeek)
                VALUES (:name, :password, :type, :status, :clearance, :isExternal, :maxBookingHours)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $userName,
            ':password'  => password_hash($userPassword, PASSWORD_DEFAULT),
            ':type'  => $userType,
            ':status'  => $userStatus,        
            ':clearance'  => $clearanceLevel,
            ':isExternal' => $isExternal ? 1 : 0,  
            ':maxBookingHours' => $maxBookingHoursPerWeek // NEW: maps to maxBookingHoursPerWeek column
        ]);
        return $this->db->lastInsertId();
    }

    public function getUserById($userID) {
        $sql = "SELECT * FROM Users WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {

        $stmt = $this->db->prepare("SELECT * FROM Users ORDER BY userID DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersByType($userType) {

        $sql = "SELECT * FROM Users WHERE userType = :type ORDER BY userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':type' => $userType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($userID, $userName, $userPassword, $userType, $userStatus, $clearanceLevel, $isExternal = false, $maxBookingHoursPerWeek = 20) {
        $sql = "UPDATE Users 
                SET userName = :name,
                    userPassword = :password,
                    userType = :type,
                    userStatus = :status,
                    clearanceLevel = :clearance,
                    isExternal = :isExternal,
                    maxBookingHoursPerWeek = :maxBookingHours
                WHERE userID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $userID,
            ':name' => $userName,
            ':password' => password_hash($userPassword, PASSWORD_DEFAULT),
            ':type' => $userType,
            ':status' => $userStatus,
            ':clearance' => $clearanceLevel,
            ':isExternal' => $isExternal ? 1 : 0,  
            ':maxBookingHours' => $maxBookingHoursPerWeek 
        ]);
    }

    public function updatePassword($userID, $userPassword) {
        // CHANGED: Table name to 'Users'
        $sql = "UPDATE Users SET userPassword = :password WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'       => $userID,
            ':password' => password_hash($userPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function updateStatus($userID, $userStatus) {
        $sql = "UPDATE Users SET userStatus = :status WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'     => $userID,
            ':status' => $userStatus
        ]);
    }


    public function updateClearanceLevel($userID, $clearanceLevel) {
        $sql = "UPDATE Users SET clearanceLevel = :clearance WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'        => $userID,
            ':clearance' => $clearanceLevel
        ]);
    }

    public function getCurrentWeeklyBookedHours($userID) {
        $sql = "SELECT currentWeeklyBookedHours, maxBookingHoursPerWeek FROM Users WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateCurrentWeeklyBookedHours($userID, $hours) {
        $sql = "UPDATE Users SET currentWeeklyBookedHours = :hours WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'    => $userID,
            ':hours' => $hours
        ]);
    }


    public function canBookHours($userID, $requestedHours) {
        $data = $this->getCurrentWeeklyBookedHours($userID);
        if (!$data) return false;
        return ($data['currentWeeklyBookedHours'] + $requestedHours) <= $data['maxBookingHoursPerWeek'];
    }

    public function acknowledgeSafetyBriefing($userID) {
        $sql = "UPDATE Users 
                SET safetyBriefingAcknowledged = 1, safetyBriefingAcknowledgedAt = NOW() 
                WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userID]);
    }

    public function hasSafetyBriefingAcknowledged($userID) {
        $sql = "SELECT safetyBriefingAcknowledged FROM Users WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (bool) $row['safetyBriefingAcknowledged'] : false;
    }

    public function getUsersByStatus($userStatus) {
        $sql = "SELECT * FROM Users WHERE userStatus = :status ORDER BY userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $userStatus]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getExternalUsers() {
        $sql = "SELECT * FROM Users WHERE isExternal = 1 ORDER BY userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function authenticate($userName, $userPassword) {
        $sql = "SELECT * FROM Users WHERE userName = :name AND userStatus = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $userName]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($userPassword, $user['userPassword'])) {
            return $user;
        }
        return false;
    }

}