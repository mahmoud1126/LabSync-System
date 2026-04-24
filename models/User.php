<?php

abstract class User {

    protected $db;

    public function __construct() {
        this->db = Database::getInstance()->getconnection;
    }

    abstract public function getRole();

    public function createUser($userName , $userPassword , $userType, $useractive , $clearanceLevel = 1  ) {

        $sql = "INSERT INTO users (user_name, user_password, user_type, user_active, clearance_level)
        VALUES (:name, :password, :active , :type, 1, :clearance)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'      => $userName,
            ':password'  => password_hash($userPassword, PASSWORD_DEFAULT),
            ':type'      => $userType,
            ':active'=> $useractive,
            ':clearance' => $clearanceLevel
        ]);

        return (int) $this->db->lastInsertId();
    }


    public function getUserById($userId) {
        $sql = "SELECT * FROM users WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers(){
        $stmt = $this->db->prepare("SELECT * FROM users ORDER BY user_id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersByType($userType)
    {
        $sql = "SELECT * FROM users WHERE user_type = :type ORDER BY user_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':type' => $userType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($userId, $userName, $clearanceLevel)
    {
        $sql = "UPDATE users 
                SET user_name = :name,user_type= :type ,clearance_level = :clearance
                WHERE user_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'   => $userId,
            ':type' =>$userType,
            ':name' => $userName,
            ':clearance' => $clearanceLevel
        ]);
    }


    public function updatePassword($userId, $newPassword) {
        $sql = "UPDATE users SET user_password = :password WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'       => $userId,
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function deactivateUser($userId)
    {
        $sql = "UPDATE users SET is_active = 0 WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }


    public function activateUser($userId)
    {
        $sql = "UPDATE users SET is_active = 1 WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    public function meetsAccessTier($userId, $requiredLevel) {
        return $this->getClearanceLevel($userId) >= $requiredLevel;
    }




}