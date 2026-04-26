<?php

require_once __DIR__ . '/Researcher.php';

class GuestResearcher extends Researcher {
    public function __construct(){
        parent::__construct();
    }

    public function getRole(){
        return 'guest_researcher';
    }

    public function createGuestResearcher( $userName, $userPassword, $institution, $expirationDate, $sponsorPIID,  $clearanceLevel = 0, $maxBookingHoursPerWeek = 20) {
        try {
            $this->db->beginTransaction();


            $userID = $this->createUser(
                $userName,
                $userPassword,
                'guest_researcher',
                'active',
                $clearanceLevel,
                true,                    
                $maxBookingHoursPerWeek  
            );

            $sql = "INSERT INTO GuestResearchers (userID, institution, expirationDate, sponsorPIID)
                    VALUES (:userID, :institution, :expirationDate, :sponsorPIID)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':userID'          => $userID,
                ':institution'     => $institution,
                ':expirationDate'  => $expirationDate,
                ':sponsorPIID'     => $sponsorPIID
            ]);

            $this->db->commit();

        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e; 
        }
    }


    public function getGuestResearcherById($userID)
    {
        $sql = "SELECT u.*, gr.institution, gr.expirationDate, gr.sponsorPIID,
                sponsor.userName AS sponsorName
                FROM Users u
                JOIN GuestResearchers gr ON u.userID = gr.userID
                LEFT JOIN Users sponsor ON gr.sponsorPIID = sponsor.userID
                WHERE u.userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllGuestResearchers()
    {
        $sql = "SELECT u.*, gr.institution, gr.expirationDate, gr.sponsorPIID,
                sponsor.userName AS sponsorName
                FROM Users u
                JOIN GuestResearchers gr ON u.userID = gr.userID
                LEFT JOIN Users sponsor ON gr.sponsorPIID = sponsor.userID
                WHERE u.userType = 'guest_researcher'
                ORDER BY u.userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getGuestsBySponsor($sponsorPIID)
    {
        $sql = "SELECT u.*, gr.institution, gr.expirationDate
                FROM Users u
                JOIN GuestResearchers gr ON u.userID = gr.userID
                WHERE gr.sponsorPIID = :sponsorID
                ORDER BY gr.expirationDate ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sponsorID' => $sponsorPIID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isExpired($userID)
    {
        $sql = "SELECT expirationDate FROM GuestResearchers WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (strtotime($row['expirationDate']) < time());
    }


    public function expireGuestCredentials($userID)
    {
        try {
            $this->db->beginTransaction();

            
            $this->updateStatus($userID, 'inactive');

            $sql = "UPDATE Bookings
                    SET bookingStatus = 'cancelled',
                    cancellationReason = 'Guest researcher account expired'
                    WHERE userID = :id
                    AND bookingStatus IN ('pending', 'confirmed')
                    AND startTime > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $userID]);

            $sql = "INSERT INTO SystemAuditLogs (userID, actionType, tableAffected, recordID, description)
                    VALUES (:id, 'guest_expired', 'GuestResearchers', :recordID, 'Guest researcher credentials expired')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $userID,
                ':recordID' => $userID
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }


    public function updateGuestResearcher( $userID, $userName, $userPassword, $institution, $expirationDate, $sponsorPIID,  $clearanceLevel, $maxBookingHoursPerWeek = 20) {
        try {
            $this->db->beginTransaction();

            $this->updateUser(
                $userID,
                $userName,
                $userPassword,
                'guest_researcher',
                'active',
                $clearanceLevel,
                true,                    
                $maxBookingHoursPerWeek  
            );

            $sql = "UPDATE GuestResearchers SET
                    institution = :institution,
                    expirationDate = :expirationDate,
                    sponsorPIID = :sponsorPIID
                    WHERE userID = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'    => $userID,
                ':institution'  => $institution,
                ':expirationDate' => $expirationDate,
                ':sponsorPIID'  => $sponsorPIID
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }



    public function extendExpiration($userID, $newExpirationDate){
        $sql = "UPDATE GuestResearchers SET expirationDate = :expDate WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'=> $userID,
            ':expDate' => $newExpirationDate
        ]);
    }

 
    public function getGuestsExpiringSoon($days = 17)
    {
        $sql = "SELECT u.*, gr.institution, gr.expirationDate, gr.sponsorPIID,
                       sponsor.userName AS sponsorName
                FROM Users u
                JOIN GuestResearchers gr ON u.userID = gr.userID
                LEFT JOIN Users sponsor ON gr.sponsorPIID = sponsor.userID
                WHERE u.userStatus = 'active'
                  AND gr.expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY gr.expirationDate ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}