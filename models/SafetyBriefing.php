<?php

require_once __DIR__ . '/../config/Database.php';

class SafetyBriefing {

    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createBriefing($equipmentID, $briefingContent) {
        $sql = "INSERT INTO SafetyBriefings (equipmentID, briefingContent)
                VALUES (:equipmentID, :content)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':equipmentID'=> $equipmentID,
            ':content'=> $briefingContent
        ]);

        return $this->db->lastInsertId();
    }

    public function getBriefingById($briefingID) {
        $sql = "SELECT * FROM SafetyBriefings WHERE briefingID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $briefingID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllBriefings() {
        $stmt = $this->db->prepare("SELECT * FROM SafetyBriefings ORDER BY briefingID ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBriefingByEquipmentId($equipmentID) {
        $sql = "SELECT * FROM SafetyBriefings WHERE equipmentID = :equipmentID";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':equipmentID' => $equipmentID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBriefing($briefingID, $equipmentID, $briefingContent) {
        $sql = "UPDATE SafetyBriefings
                SET equipmentID = :equipmentID,
                    briefingContent = :content
                WHERE briefingID = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':equipmentID'=> $equipmentID,
            ':content'=> $briefingContent,
            ':id'=> $briefingID
        ]);
    }

    public function deleteBriefing($briefingID) {
        $sql = "DELETE FROM SafetyBriefings WHERE briefingID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $briefingID]);
    }
}
?>
