<?php

require_once __DIR__ . '../config/database.php' ;

class IncidentLog {

    private $db ;
    
    public function __construct (){
        $this->db = Database::getInstance()->getConnection();
    }


    public function createIncident ($userID, $equipmentID, $reportedByID, $incidentType, $description, $severity, $timeOfIncident ){
        $sql = "INSERT INTO IncidentReports (userID , equipmentID , reportedByID , incidentType , description , severity , timeOfIncident)
                VALUES (:userID , :equipmentID , :reportedByID , :incidentType , :description , :severity , :timeOfIncident)" ;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
        'userID' => $userID,
        'equipmentID' => $equipmentID,
        'reportedByID'=> $reportedByID,
        'incidentType' => $incidentType, 
        'description'=> $description,
        'severity'=> $severity,
        'time'=> $timeOfIncident,
        ]);
    }

    public function getIncidentByID ($incidentID){
        $sql = "SELECT ir.*, 
                u.userName AS involvedUserName, reporter.userName AS reporterName, e.equipmentName
                FROM IncidentReports ir
                LEFT JOIN Users u ON ir.userID = u.userID
                LEFT JOIN Users reporter ON ir.reportedByID = reporter.userID
                LEFT JOIN Equipment e ON ir.equipmentID = e.equipmentID
                WHERE ir.incidentID = :incidentID";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['incidentID' => $incidentID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllIncidents(){
        $sql = "SELECT ir.*, 
                u.userName AS involvedUserName, reporter.userName AS reporterName, e.equipmentName
                FROM IncidentReports ir
                LEFT JOIN Users u ON ir.userID = u.userID
                LEFT JOIN Users reporter ON ir.reportedByID = reporter.userID
                LEFT JOIN Equipment e ON ir.equipmentID = e.equipmentID
                ORDER BY ir.timeOfIncident DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    


    public function getIncidentsByUserID($userID)
    {
        $sql = "SELECT ir.*, 
                e.equipmentName, reporter.userName AS reporterName
                FROM IncidentReports ir
                LEFT JOIN Equipment e ON ir.equipmentID = e.equipmentID
                LEFT JOIN Users reporter ON ir.reportedByID = reporter.userID
                WHERE ir.userID = :userID
                ORDER BY ir.timeOfIncident DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidentsByEquipmentID($equipmentID){
        $sql = "SELECT ir.*, u.userName AS involvedUserName, reporter.userName AS reporterName
                FROM IncidentReports ir
                LEFT JOIN Users u ON ir.userID = u.userID
                LEFT JOIN Users reporter ON ir.reportedByID = reporter.userID
                WHERE ir.equipmentID = :equipmentID
                ORDER BY ir.timeOfIncident DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':equipmentID' => $equipmentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidentsReportedBy($reportedByID){
        $sql = "SELECT ir.*, u.userName AS involvedUserName, e.equipmentName
                FROM IncidentReports ir
                LEFT JOIN Users u ON ir.userID = u.userID
                LEFT JOIN Equipment e ON ir.equipmentID = e.equipmentID
                WHERE ir.reportedByID = :reportedByID
                ORDER BY ir.timeOfIncident DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':reportedByID' => $reportedByID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}