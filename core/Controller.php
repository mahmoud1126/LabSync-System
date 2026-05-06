<?php

class BaseController
{
    /**
     * Renders a view file and passes data to it.
     */
    protected function view($viewPath, $data = []) {
        $data['flash'] = $this->getFlash();

        if (isset($_SESSION['user'])) {
            $data['currentUser'] = $this->getCurrentUser();
            $data['currentRole'] = $this->getCurrentUserRole();
        } else {
            $data['currentUser'] = null;
            $data['currentRole'] = null;
        }

        // The "magic" that makes array keys like 'totalSpent' available as $totalSpent in the HTML
        extract($data);

        // Your structure uses the 'pages' directory for view files
        $viewFile = __DIR__ . '/../pages/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            // Updated error message to accurately reflect the 'pages' directory
            die("⚠️ LabSync Error: View Not Found: pages/{$viewPath}.php");
        }

        require_once $viewFile;
    }

    /**
     * Redirects to a internal URL within the system.
     */
    protected function redirect($url) {
        $basePath = '/LabSync-System';

        if (strpos($url, $basePath) !== 0) {
            $url = $basePath . $url;
        }

        header("Location: $url");
        exit;
    }

    /**
     * Ensures the user is logged in before proceeding.
     */
    protected function requireLogin(): bool {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Please log in to access this page.');
            $this->redirect('/login');
        }
        return true;
    }

    /**
     * Gets the currently logged-in user data.
     */
    protected function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Gets the current user's ID.
     */
    protected function getCurrentUserID() {
        return $_SESSION['user']['userID'] ?? null;
    }

    /**
     * Gets the current user's role (matches your header logic).
     */
    protected function getCurrentUserRole() {
        return $_SESSION['user']['userType'] ?? null;
    }

    /**
     * Enforces role-based access control.
     */
    protected function requireRole($allowedRoles) {
        $this->requireLogin();

        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        $currentRole = $this->getCurrentUserRole();

        if (!in_array($currentRole, $allowedRoles)) {
            http_response_code(403);

            // Attempt to find a custom 403 error page
            $errorPage = __DIR__ . '/../views/errors/403.php';

            if (file_exists($errorPage)) {
                $message = 'Access Denied: Your role does not have permission for this action.';
                $requiredRoles = $allowedRoles;
                require_once $errorPage;
            } else {
                echo "<h1>403 Forbidden</h1>";
                echo "<p>Access Denied: Your role does not have permission for this action.</p>";
                echo "<p>Required roles: " . implode(', ', $allowedRoles) . "</p>";
                echo "<p><a href='/LabSync-System/dashboard'>← Back to Dashboard</a></p>";
            }
            exit;
        }
    }

    /**
     * Checks if the user has a specific role without exiting.
     */
    protected function hasRole($roles) {
        if (!$this->requireLogin()) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array($this->getCurrentUserRole(), $roles);
    }

    /**
     * Ensures the researcher has acknowledged safety protocols.
     */
    protected function requireSafetyBriefing() {
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

    /**
     * Checks if a guest researcher's access has expired.
     */
    protected function checkGuestExpiry() {
        $this->requireLogin();
        $user = $this->getCurrentUser();

        if ($user['userType'] !== 'guest_researcher') {
            return;
        }

        require_once __DIR__ . '/../models/GuestResearcher.php';
        $guestModel = new GuestResearcher();
        $guest = $guestModel->getGuestResearcherById($user['userID']);

        if (!$guest) {
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $expired = strtotime($guest['expirationDate']) <= time();
        $inactive = $guest['userStatus'] !== 'active';

        if ($expired || $inactive) {
            if ($expired && $guest['userStatus'] === 'active') {
                $guestModel->expireGuestCredentials((int) $user['userID']);
            }

            session_destroy();
            session_start(); 
            $this->setFlash('error', 'Your guest access has expired. Please contact the Lab Manager.');
            $this->redirect('/login');
        }
    }

    /**
     * Logs an action to the audit trail.
     */
    protected function logAction($actionType, $tableAffected, $description = '', $recordID = null, $oldValue = null, $newValue = null) {
        require_once __DIR__ . '/../models/AuditLog.php';
        $auditLog = new AuditLog();
        $auditLog->log(
            $this->getCurrentUserID(),
            $actionType,
            $tableAffected,
            $recordID,
            $oldValue,
            $newValue,
            $description
        );
    }

    /**
     * Sets a session flash message.
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    /**
     * Gets and clears the session flash message.
     */
    protected function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']); 
            return $flash;
        }
        return null;
    }

    /**
     * Adds section context to the data array.
     */
    protected function withSection($section, $data = []) {
        $data['activeSection'] = $section;
        return $data;
    }

    /**
     * Adds modal context to the data array.
     */
    protected function withModal($modalID, $data = []) {
        $data['activeModal'] = $modalID;
        return $data;
    }

    /**
     * Retrieves and sanitizes POST data.
     */
    protected function getPost($key, $default = null) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    /**
     * Retrieves and sanitizes GET query data.
     */
    protected function getQuery($key, $default = null) {
        if (isset($_GET[$key])) {
            $value = $_GET[$key];
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    /**
     * Checks if the request method is POST.
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}