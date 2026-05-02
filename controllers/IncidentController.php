<?php 

require_once __DIR__ . "/../core/Controller.php" ;
class IncidentController extends BaseController {

    public function index(){
        $this->requireRole(['lab_manager']);
        
        require_once __DIR__ ."/../models/IncidentLog.php";
        $IncidentModel = new IncidentLog() ;
        $incidents = $IncidentModel-> getAllIncidents() ;

        $this->view('incidents/index', ['incidents' => $incidents,]);


    }



    public function create(){
        $this->requireRole(['lab_manager']);;
        require_once __DIR__ . '/../models/Researcher.php';
        require_once __DIR__ . '/../models/Equipment.php';

        $userModel = new Researcher() ;
        $equipmentModel = new Equipment() ;

        $users     = $userModel->getResearchersAndGuests();
        $equipment = $equipmentModel->getAllEquipment();

        $this-> view('incidents/create' , [ 'users'=> $users , 'equipment'=> $equipment ]);

    }


 
    public function store(){

        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/IncidentLog.php';

        $data = [
            'userID'         => $this->getPost('userID'),
            'equipmentID'    => $this->getPost('equipmentID'),
            'reportedByID'   => $this->getCurrentUserID(),
            'incidentType'   => $this->getPost('incidentType'),
            'description'    => $this->getPost('description'),
            'severity'       => $this->getPost('severity'),
            'timeOfIncident' => $this->getPost('timeOfIncident'),
        ];


        require_once __DIR__ .'/../modules/module3/IncidentLockout.php';
        $lockout = new IncidentLockout();
        $result  = $lockout->submitAndLockout($data);

        if ($result['success']) {
            $this->logAction('INCIDENT_REPORTED','IncidentReports',"Incident reported. " . $result['message'],(int) $data['userID']);
        }

        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );

        if ($result['success']) {
            $this->redirect('/incidents');
        } 
        else {
            $this->redirect('/incidents/create');
        }
    }


    public function show($id){

        $this->requireRole(['lab_manager']);

        require_once __DIR__ . '/../models/IncidentLog.php';
        $incidentModel = new IncidentLog();
        $incident      = $incidentModel->getIncidentByID($id);

        if (!$incident) {
            $this->setFlash('error', 'Incident not found.');
            $this->redirect('/incidents');
            return;
        }

        $this->view('incidents/show', ['incident' => $incident,]);
    }


}