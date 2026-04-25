<?php

abstract class User {

    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    abstract public function getRole();

    public function createUser($userName , $userPassword , $userType, $userStatus , $clearanceLevel  ) {

        $sql = "INSERT INTO users (userName, userPassword, userType, userStatus, clearanceLevel)
        VALUES (:name, :password, :active , :type, :clearance)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'      => $userName,
            ':password'  => password_hash($userPassword, PASSWORD_DEFAULT),
            ':type'      => $userType,
            ':active'=> $userStatus,
            ':clearance' => $clearanceLevel
        ]);
    }


    public function getUserById($userID) {
        $sql = "SELECT * FROM users WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers(){
        $stmt = $this->db->prepare("SELECT * FROM users ORDER BY userID DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersByType($userType)
    {
        $sql = "SELECT * FROM users WHERE userType = :type ORDER BY userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':type' => $userType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($userID ,$userName , $userPassword , $userType, $userStatus , $clearanceLevel ){
        $sql = "UPDATE users 
                SET userName = :name, userPassword= :password , user_type= :type , userStatus = :status ,clearance_level = :clearance
                WHERE userID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'   => $userID,
            ':name' => $userName,
            ':password'=> password_hash($userPassword, PASSWORD_DEFAULT),
            ':type' =>$userType,
            ':status'=> $userStatus,
            ':clearance' => $clearanceLevel
        ]);
    }




    public function updatePassword($userID, $userPassword) {
        $sql = "UPDATE users SET userPassword = :password WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'       => $userID,
            ':password' => password_hash($userPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function updateStatus($userID, $userStatus)
    {
        $sql = "UPDATE users SET status = :status WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ":id" => $userID,
            ":status"  => $userStatus
        ]);
    }




}