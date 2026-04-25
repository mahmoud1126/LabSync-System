<?php

require_once __DIR__ . '/Researcher.php';

class GuestResearcher extends Researcher {
    public function __construct()
    {
        parent::__construct();
    }

    public function getRole()
    {
        return 'guest_researcher';
    }

   public function createGuestResearcher($userName, $userPassword, $institution, $expirationDate, $sponsorPiID, $clearanceLevel ){
    $this->db->beginTransaction();

    $sql = "INSERT INTO users (userName, userPassword, userType, userStatus, clearanceLevel)
            VALUES (:name, :password, :type, :status, :clearance)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':name'      => $userName,
        ':password'  => password_hash($userPassword, PASSWORD_DEFAULT),
        ':type'      => 'guest_researcher',
        ':status'    => 'active',
        ':clearance' => $clearanceLevel
    ]);

    $userID = $this->db->lastInsertId();


    $sql = "INSERT INTO guest_researchers (userID, institution, expirationDate, sponsorPiID)
            VALUES (:userID, :institution, :expirationDate, :sponsorPiID)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':userID'          => $userID,
        ':institution'     => $institution,
        ':expirationDate'  => $expirationDate,
        ':sponsorPiID'     => $sponsorPiID
    ]);

    $this->db->commit();
    return $userID;
    }



    public function getGuestResearcherById($userID)
    {
        $sql = "SELECT u.*, gr.guestID, gr.institution, gr.expirationDate, gr.sponsorPiID,
                       sponsor.userName AS sponsorName
                FROM users u
                JOIN guest_researchers gr ON u.userID = gr.userID
                LEFT JOIN users sponsor ON gr.sponsorPiID = sponsor.userID
                WHERE u.userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllGuestResearchers()
    {
        $sql = "SELECT u.*, gr.guestID, gr.institution, gr.expirationDate, gr.sponsorPiID,
                       sponsor.userName AS sponsorName
                FROM users u
                JOIN guest_researchers gr ON u.userID = gr.userID
                LEFT JOIN users sponsor ON gr.sponsorPiID = sponsor.userID
                WHERE u.userType = 'guest_researcher'
                ORDER BY u.userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getGuestsBySponsor($sponsorPiID)
    {
        $sql = "SELECT u.*, gr.institution, gr.expirationDate
                FROM users u
                JOIN guest_researchers gr ON u.userID = gr.userID
                WHERE gr.sponsorPiID = :sponsorID
                ORDER BY gr.expirationDate ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sponsorID' => $sponsorPiID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function isExpired($userID)
    {
        $sql = "SELECT expirationDate FROM guest_researchers WHERE userID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return true;
        }

        return (strtotime($row['expirationDate']) < time());
    }


    public function expireGuestCredentials($userID)
    {
        try {
            $this->db->beginTransaction();


            $this->updateStatus($userID, 'inactive');


            $sql = "UPDATE user_session_tokens
                    SET isActive = 0
                    WHERE userID = :id AND isActive = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $userID]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }



    public function updateGuestResearcher($userID, $userName, $userPassword, $institution, $expirationDate, $sponsorPiID, $clearanceLevel)
    {
        try {
            $this->db->beginTransaction();


            $this->updateUser(
                $userID,
                $userName,
                $userPassword,
                'guest_researcher',
                'active',
                $clearanceLevel
            );


            $sql = "UPDATE guest_researchers SET
                        institution = :institution,
                        expirationDate = :expirationDate,
                        sponsorPiID = :sponsorPiID
                    WHERE userID = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'              => $userID,
                ':institution'     => $institution,
                ':expirationDate'  => $expirationDate,
                ':sponsorPiID'     => $sponsorPiID
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }


    public function searchGuestResearchers($keyword)
    {
        $sql = "SELECT u.*, gr.institution, gr.expirationDate, gr.sponsorPiID
                FROM users u
                JOIN guest_researchers gr ON u.userID = gr.userID
                WHERE (u.userName LIKE :keyword OR gr.institution LIKE :keyword)
                ORDER BY u.userID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':keyword' => '%' . $keyword . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}