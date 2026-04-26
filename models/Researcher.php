<?php

require_once __DIR__ . '/User.php';

class Researcher extends User
{
    public function __construct(){
        parent::__construct();
    }

    public function getRole(){
        return 'researcher';
    }


    public function createResearcher( $userName,$userPassword, $clearanceLevel , $isExternal = false,$maxBookingHoursPerWeek = 20
    ) {
        return $this->createUser(
            $userName,
            $userPassword,
            'researcher',
            'active',
            $clearanceLevel,
            $isExternal,            
            $maxBookingHoursPerWeek 
        );
    }

    public function getAllResearchers(){
        return $this->getUsersByType('researcher');
    }


    public function getResearcherBookings($userID){
        $sql = "SELECT b.*, e.equipmentName
                FROM Bookings b
                JOIN Equipment e ON b.equipmentID = e.equipmentID
                WHERE b.userID = :id
                ORDER BY b.startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResearcherSessions($userID)
    {
        $sql = "SELECT s.*, e.equipmentName
                FROM Sessions s
                JOIN Equipment e ON s.equipmentID = e.equipmentID
                WHERE s.userID = :id
                ORDER BY s.actualStartTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getResearcherGrants($userID)
    {
        $sql = "SELECT g.*, gua.billingPercentage
                FROM Grants g
                JOIN GrantUserAccess gua ON g.grantID = gua.grantID
                WHERE gua.userID = :id AND g.grantStatus = 'active'
                ORDER BY g.expirationDate ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getResearcherTransactions($userID)
    {
        $sql = "SELECT gt.*, g.grantName
                FROM GrantTransactions gt
                JOIN Grants g ON gt.grantID = g.grantID
                WHERE gt.userID = :id
                ORDER BY gt.createdAt DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateResearcher( $userID, $userName, $userPassword, $clearanceLevel,  $isExternal = false, $maxBookingHoursPerWeek = 20) {
        return $this->updateUser(
            $userID,
            $userName,
            $userPassword,
            'researcher',
            'active',
            $clearanceLevel,
            $isExternal,           
            $maxBookingHoursPerWeek 
        );
    }


    public function getResearcherBookingsByStatus($userID, $bookingStatus)
    {
        $sql = "SELECT b.*, e.equipmentName
                FROM Bookings b
                JOIN Equipment e ON b.equipmentID = e.equipmentID
                WHERE b.userID = :id AND b.bookingStatus = :status
                ORDER BY b.startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'     => $userID,
            ':status' => $bookingStatus
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getResearcherConsumableUsage($userID)
    {
        $sql = "SELECT sc.*, c.consumableName, c.unitCost, s.actualStartTime, s.actualEndTime
                FROM SessionConsumables sc
                JOIN Sessions s ON sc.sessionID = s.sessionID
                JOIN Consumables c ON sc.consumableID = c.consumableID
                WHERE s.userID = :id
                ORDER BY s.actualStartTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResearcherSafetyAcknowledgements($userID)
    {
        $sql = "SELECT sba.*, sb.equipmentID, sb.briefingContent, e.equipmentName
                FROM SafetyBriefingAcknowledgements sba
                JOIN SafetyBriefings sb ON sba.briefingID = sb.briefingID
                JOIN Equipment e ON sb.equipmentID = e.equipmentID
                WHERE sba.userID = :id
                ORDER BY sba.acknowledgedAt DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getResearcherIncidents($userID){
        $sql = "SELECT ir.*, e.equipmentName
                FROM IncidentReports ir
                JOIN Equipment e ON ir.equipmentID = e.equipmentID
                WHERE ir.userID = :id
                ORDER BY ir.timeOfIncident DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getResearcherTotalSpending($userID){
        $sql = "SELECT COALESCE(SUM(gt.amount), 0) AS totalSpent
                FROM GrantTransactions gt
                WHERE gt.userID = :id AND gt.transactionType = 'deduction'
                  AND gt.approvalStatus = 'approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['totalSpent'];
    }
}