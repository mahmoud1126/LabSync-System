<?php

require_once __DIR__ . '/../config/Database.php';

class Booking {

    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createBooking($userID, $equipmentID, $startTime, $endTime, $bookingStatus = 'pending', $isAutoBooked = false, $parentBookingID = null, $grantID = null, $labManagerID = null) {
        $sql = "INSERT INTO Bookings (userID, equipmentID, startTime, endTime, bookingStatus, isAutoBooked, parentBookingID, grantID, labManagerID)
                VALUES (:userID, :equipmentID, :startTime, :endTime, :status, :isAutoBooked, :parentBookingID, :grantID, :labManagerID)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':userID'=> $userID,
            ':equipmentID'=> $equipmentID,
            ':startTime'=> $startTime,
            ':endTime'=> $endTime,
            ':status'=> $bookingStatus,
            ':isAutoBooked'=> $isAutoBooked ? 1 : 0,
            ':parentBookingID'=> $parentBookingID,
            ':grantID'=> $grantID,
            ':labManagerID'=> $labManagerID
        ]);

        return $this->db->lastInsertId();
    }



    public function getBookingById($bookingID) {
        $sql = "SELECT * FROM Bookings WHERE bookingID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $bookingID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllBookings() {
        $sql="SELECT * FROM Bookings ORDER BY startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingsByUser($userID) {
        $sql = "SELECT * FROM Bookings WHERE userID = :userID ORDER BY startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingsByEquipment($equipmentID) {
        $sql = "SELECT * FROM Bookings WHERE equipmentID = :equipmentID ORDER BY startTime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':equipmentID' => $equipmentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingsByStatus($status) {
    $sql = "SELECT b.*, 
                   u.userName,
                   e.equipmentName
            FROM Bookings b
            JOIN Users u ON b.userID = u.userID
            JOIN Equipment e ON b.equipmentID = e.equipmentID
            WHERE b.bookingStatus = :status 
            ORDER BY b.startTime ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':status' => $status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    public function updateBookingStatus($bookingID, $status, $labManagerID = null) {
        $sql = "UPDATE Bookings
                SET bookingStatus = :status, labManagerID = :managerID
                WHERE bookingID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status'=> $status,
            ':managerID'=> $labManagerID,
            ':id'=> $bookingID
        ]);
    }

    public function cancelBooking($bookingID, $reason) {
        $sql = "UPDATE Bookings
                SET bookingStatus = 'cancelled', cancellationReason = :reason
                WHERE bookingID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reason'=> $reason,
            ':id'=> $bookingID
        ]);
    }

    public function updateBookingTimes($bookingID, $startTime, $endTime) {
        $sql = "UPDATE Bookings
                SET startTime = :startTime, endTime = :endTime
                WHERE bookingID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':startTime'=> $startTime,
            ':endTime'=> $endTime,
            ':id'=> $bookingID
        ]);
    }

    public function hasTimeConflict($equipmentID, $requestedStartTime, $requestedEndTime) {
        $sql = "SELECT COUNT(*) FROM Bookings
                WHERE equipmentID = :equipmentID
                AND bookingStatus IN ('confirmed', 'pending')
                AND (
                    (startTime < :endTime AND endTime > :startTime)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':equipmentID' => $equipmentID,
            ':startTime'   => $requestedStartTime,
            ':endTime'     => $requestedEndTime
        ]);

        $count = $stmt->fetchColumn();
        return $count > 0;
    }
}
?>
