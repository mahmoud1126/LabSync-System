<?php


class BaseController
{
    
    protected function view($viewPath, $data = [])
    {

    $data['flash'] = $this->getFlash();

        if ($this->requireLogin()) {
            $data['currentUser'] = $this->getCurrentUser();
            $data['currentRole'] = $this->getCurrentUserRole();
        }


        extract($data);

        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: View Not Found</h2>"
                . "<p>Looking for: <code>views/{$viewPath}.php</code></p>"
                . "<p><strong>Fix:</strong> Create this file in the views/ folder.</p>"
                . "</div>"
            );
        }

        require_once $viewFile;
    }


    protected function redirect($url)
    {
       
        $basePath = '/LabSync-System';

        if (strpos($url, $basePath) !== 0) {
            $url = $basePath . $url;
        }

        header("Location: $url");

        exit;
    }


    protected function requireLogin()
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Please log in to access this page.');
            $this->redirect('/login');
        }
    }

    protected function getCurrentUser(){
        return $_SESSION['user'] ?? null;
    }


    protected function getCurrentUserID()    {
        return $_SESSION['user']['userID'] ?? null;
    }

    protected function getCurrentUserRole(){
        return $_SESSION['user']['userType'] ?? null;
    }


    protected function requireRole($allowedRoles)
    {

        $this->requireLogin();

        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }


        $currentRole = $this->getCurrentUserRole();

        if (!in_array($currentRole, $allowedRoles)) {
            http_response_code(403);

            $errorPage = __DIR__ . '/../views/errors/403.php';

            if (file_exists($errorPage)) {
                $message = 'Access Denied: Your role does not have permission for this action.';
                $requiredRoles = $allowedRoles;
                require_once $errorPage;
            } 
            exit;
        }
    }


    protected function hasRole($roles)
    {
        if (!$this->requireLogin()) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array($this->getCurrentUserRole(), $roles);
    }



    protected function requireSafetyBriefing()
    {

        $this->requireLogin();

        $userID = $this->getCurrentUserID();

        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $acknowledged = $userModel->getResearcherSafetyAcknowledgements($userID);

        if (!$acknowledged) {
            $this->setFlash('warning', 'You must read and acknowledge the safety briefing before proceeding.');
            $this->redirect('/compliance/safety-briefing');
        }
    }

    protected function checkGuestExpiry()
    {

        if (!$this->requireLogin()) {
            return;
        }

        $user = $this->getCurrentUser();

        if ($user['userType'] !== 'guest') {
            return;
        }

        if (isset($user['expirationDate'])) {
            $expirationDate = strtotime($user['expirationDate']);
            $now = time();

            if ($now > $expirationDate) {
                session_destroy();
                session_start(); 
                $this->setFlash('error', 'Your guest access has expired. Please contact the Lab Manager.');
                $this->redirect('/login');
            }
        }
    }


    // protected function logAction($actionType, $description = '', $targetID = null, $targetType = null)
    // {
        
    //     $db = Database::getInstance()->getConnection();

    //     $sql = "INSERT INTO SystemAuditLogs 
    //             (userID, actionType, tableAffected,description, targetID, targetType, ipAddress, createdAt)
    //             VALUES 
    //             (:userID, :actionType, :description, :targetID, :targetType, :ipAddress, NOW())";

    //     $stmt = $db->prepare($sql);
    //     $stmt->execute([
    //         ':userID'  => $this->getCurrentUserID(),
    //         ':actionType' => $actionType,
    //         ':description' => $description,
    //         ':targetID' => $targetID,
    //         ':targetType' => $targetType,
    //         ':ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    //     ]);

    // }



    protected function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    protected function getFlash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']); 
            return $flash;
        }
        return null;
    }



    protected function withSection($section, $data = []){
        $data['activeSection'] = $section;
        return $data;
    }


    protected function withModal($modalID, $data = [])
    {
        $data['activeModal'] = $modalID;
        return $data;
    }



    protected function getPost($key, $default = null)
    {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            // Trim strings, leave other types alone
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    protected function getQuery($key, $default = null)
    {
        if (isset($_GET[$key])) {
            $value = $_GET[$key];
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    protected function isPost(){
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}