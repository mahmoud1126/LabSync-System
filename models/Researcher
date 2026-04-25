<?php

require_once __DIR__ . '/User.php';

class Researcher extends User
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getRole()
    {
        return 'researcher';
    }


    public function createResearcher($userName, $userPassword, $clearanceLevel = 1){
        return $this->createUser(
            $userName,
            $userPassword,
            'researcher',
            'active',
            $clearanceLevel
        );
    }


    public function getAllResearchers(){
        return $this->getUsersByType('researcher');
    }


    /* ──────────── GET RESEARCHER BOOKINGS ──────────── */
    public function getResearcherBookings($userID)
    {
        $sql = "SELECT b.*, e.equipmentName
                FROM bookings b
                JOIN equipment e ON b.equipmentID = e.equipmentID
                WHERE b.userID = :id
                ORDER BY b.startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getResearcherSessions($userID)
    {
        $sql = "SELECT s.*, e.equipmentName
                FROM sessions s
                JOIN equipment e ON s.equipmentID = e.equipmentID
                WHERE s.userID = :id
                ORDER BY s.startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //                                         under review 
    // public function getResearcherGrants($userID)
    // {
    //     $sql = "SELECT g.*, gua.percentage
    //             FROM grants g
    //             JOIN grant_user_access gua ON g.grantID = gua.grantID
    //             WHERE gua.userID = :id AND g.grantStatus = 'active'
    //             ORDER BY g.expirationDate ASC";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([':id' => $userID]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }


    // public function getResearcherTransactions($userID)
    // {
    //     $sql = "SELECT gt.*, g.grantName
    //             FROM grant_transactions gt
    //             JOIN grants g ON gt.grantID = g.grantID
    //             WHERE gt.userID = :id
    //             ORDER BY gt.createdAt DESC";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([':id' => $userID]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function updateResearcher($userID, $userName, $userPassword, $clearanceLevel)
    {
        return $this->updateUser(
            $userID,
            $userName,
            $userPassword,
            'researcher',
            'active',
            $clearanceLevel
        );
    }
}