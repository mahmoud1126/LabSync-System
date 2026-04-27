<?php
/**
 * ═══════════════════════════════════════════════════
 * LabSync System — Base Controller
 * ═══════════════════════════════════════════════════
 * 
 * PARENT CLASS for ALL controllers in the system.
 * Every controller extends this class and inherits all methods.
 * 
 * WHY THIS EXISTS:
 *   Without this, every controller method would need to repeat:
 *   - Login checks (15 lines × 30 methods = 450 repeated lines)
 *   - Role checks
 *   - Safety briefing checks
 *   - Audit logging
 *   - View loading
 *   - Redirect logic
 * 
 *   With this, each check = ONE line in any controller.
 * 
 * METHODS ORGANIZED BY PURPOSE:
 * 
 *   ── VIEW & NAVIGATION ──
 *   view()              → Load an HTML page and pass data to it
 *   redirect()          → Send user to another URL
 * 
 *   ── AUTHENTICATION ──
 *   requireLogin()      → Block access if not logged in
 *   isLoggedIn()        → Check login without blocking
 *   getCurrentUser()    → Get logged-in user's full data
 *   getCurrentUserID()  → Get logged-in user's ID
 *   getCurrentUserRole()→ Get logged-in user's type/role
 * 
 *   ── ACCESS CONTROL (Function #7: Tiered Access Permissions) ──
 *   requireRole()       → Block access if wrong role
 *   hasRole()           → Check role without blocking
 * 
 *   ── COMPLIANCE GATES ──
 *   requireSafetyBriefing() → Function #8: Safety Briefing Gatekeeper
 *   checkGuestExpiry()      → Function #6 Module 3: Guest Time-Limited Access
 * 
 *   ── AUDIT TRAIL (Function #8 Module 3) ──
 *   logAction()         → Record action to immutable audit log
 * 
 *   ── USER EXPERIENCE ──
 *   setFlash()          → Store message shown after redirect
 *   getFlash()          → Read and clear flash message
 * 
 *   ── FORM & INPUT ──
 *   getPost()           → Safely read POST data
 *   getQuery()          → Safely read GET parameters
 *   isPost()            → Check if current request is POST
 * 
 *   ── PAGE SECTION CONTROL ──
 *   showSection()       → Tell the view which section/modal to open
 * 
 * YOUR CONTROLLERS THAT EXTEND THIS:
 *   AuthController, DashboardController, BookingController,
 *   EquipmentController, SessionController, GrantController,
 *   IncidentController, ComplianceController, AdminController
 * ═══════════════════════════════════════════════════
 */

class BaseController
{
    // ═══════════════════════════════════════
    //  VIEW & NAVIGATION
    // ═══════════════════════════════════════

    /**
     * Load a view file and pass data to it
     * 
     * HOW IT WORKS:
     *   $this->view('booking/index', ['bookings' => $data, 'user' => $user]);
     * 
     *   1. Takes path 'booking/index'
     *   2. Looks for: views/booking/index.php
     *   3. extract() converts array keys into PHP variables:
     *      ['bookings' => $data] becomes $bookings = $data
     *   4. The view file uses $bookings directly in HTML
     * 
     * ONE-PAGE APPROACH:
     *   Since each feature is ONE page, we always load index.php
     *   but we pass different data depending on what the user did:
     * 
     *   // Show booking page normally (list section visible)
     *   $this->view('booking/index', ['bookings' => $data]);
     * 
     *   // Show booking page with create section open (after validation error)
     *   $this->view('booking/index', [
     *       'bookings'    => $data,
     *       'activeSection' => 'create',
     *       'error'       => 'Insufficient funds'
     *   ]);
     * 
     * @param string $viewPath  Path relative to views/ folder (without .php)
     * @param array  $data      Data to pass to the view (becomes PHP variables)
     */
    protected function view($viewPath, $data = [])
    {
        // ── Always include flash messages in view data ──
        // This way every view can display success/error messages
        // without the controller having to pass it manually
        $data['flash'] = $this->getFlash();

        // ── Always include current user data if logged in ──
        // Views often need user info (name, role) for navbar, permissions, etc.
        if ($this->isLoggedIn()) {
            $data['currentUser'] = $this->getCurrentUser();
            $data['currentRole'] = $this->getCurrentUserRole();
        }

        // ── Convert array keys to variables ──
        // ['bookings' => [...], 'user' => [...]]
        // becomes: $bookings = [...] and $user = [...]
        // So the view file can use $bookings and $user directly
        extract($data);

        // ── Build full file path ──
        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';

        // ── Check if view exists ──
        if (!file_exists($viewFile)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: View Not Found</h2>"
                . "<p>Looking for: <code>views/{$viewPath}.php</code></p>"
                . "<p><strong>Fix:</strong> Create this file in the views/ folder.</p>"
                . "</div>"
            );
        }

        // ── Load the view file ──
        // All extracted variables ($bookings, $user, $flash, etc.)
        // are available inside the view file
        require_once $viewFile;
    }

    /**
     * Redirect the user to another URL
     * 
     * Used after form submissions to prevent double-submit:
     *   User submits "Create Booking" form (POST /booking/store)
     *   → Controller processes it
     *   → Controller redirects to /booking (GET)
     *   → Page loads fresh with updated data
     *   → If user refreshes, it's just a GET (no double submit)
     * 
     * IMPORTANT: After redirect, the current script STOPS.
     * No code after redirect() will execute.
     * 
     * @param string $url The URL to redirect to (e.g., '/booking')
     */
    protected function redirect($url)
    {
        // ── Add base path ──
        // Your project lives at localhost/LabSync-System/
        // So /booking must become /LabSync-System/booking
        // CHANGE THIS if your folder name is different!
        $basePath = '/LabSync-System';

        // Only add base path if not already included
        if (strpos($url, $basePath) !== 0) {
            $url = $basePath . $url;
        }

        // ── Send HTTP redirect header ──
        header("Location: $url");

        // ── Stop script execution ──
        // Without exit, PHP continues executing code below redirect()
        // which could cause unexpected behavior
        exit;
    }


    // ═══════════════════════════════════════
    //  AUTHENTICATION
    //  These methods handle login status
    // ═══════════════════════════════════════

    /**
     * Require the user to be logged in
     * If not logged in → redirect to login page
     * 
     * Call this at the START of any protected method:
     * 
     *   public function index() {
     *       $this->requireLogin();   ← Blocks if not logged in
     *       // ... safe to proceed, user IS logged in
     *   }
     * 
     * WHAT HAPPENS:
     *   1. Checks if $_SESSION['user'] exists
     *   2. If YES → does nothing, code continues
     *   3. If NO → sets error message, redirects to /login, script STOPS
     */
    protected function requireLogin()
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Please log in to access this page.');
            $this->redirect('/login');
            // Script stops here — exit is inside redirect()
        }
    }

    /**
     * Check if user is logged in WITHOUT redirecting
     * 
     * Use this when you want to CHECK but not BLOCK:
     *   - Navbar: show "Login" vs "Logout" button
     *   - Home page: show different content for guests vs users
     * 
     * @return bool True if logged in, false if not
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user']);
    }

    /**
     * Get the full data array of the logged-in user
     * 
     * Returns the same array that was stored during login:
     *   $_SESSION['user'] = $user; (in AuthController::login)
     * 
     * Contains: userID, userName, userType, userStatus,
     *           clearanceLevel, isExternal, etc.
     * 
     * @return array|null User data or null if not logged in
     */
    protected function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get the logged-in user's ID
     * 
     * Shortcut for: $_SESSION['user']['userID']
     * Used frequently in queries and logging.
     * 
     * @return int|null User ID or null
     */
    protected function getCurrentUserID()
    {
        return $_SESSION['user']['userID'] ?? null;
    }

    /**
     * Get the logged-in user's role/type
     * 
     * Returns one of: 'researcher', 'guest', 'facultyPI', 'labManager'
     * 
     * Used for:
     *   - Role-based access checks
     *   - Showing/hiding UI elements
     *   - Determining pricing tier (Consumption-Based Rate Calculator)
     * 
     * @return string|null User type or null
     */
    protected function getCurrentUserRole()
    {
        return $_SESSION['user']['userType'] ?? null;
    }


    // ═══════════════════════════════════════
    //  TIERED ACCESS PERMISSIONS ENGINE
    //  (Function #7 from Module 1)
    // ═══════════════════════════════════════
    //
    //  YOUR SRS SAYS:
    //  "The backend routing logic enforcing role-based 
    //   barriers across the app"
    //
    //  ROLE HIERARCHY:
    //    guest       → Lowest  (temporary, time-limited)
    //    researcher  → Standard (book equipment, view grants)
    //    facultyPI   → High    (approve transactions, manage grants)
    //    labManager  → Highest (lock equipment, manage users, audit logs)
    //
    // ═══════════════════════════════════════

    /**
     * Require the user to have a specific role
     * If wrong role → show 403 Forbidden error
     * 
     * USAGE EXAMPLES:
     * 
     *   // Only Lab Managers can lock equipment
     *   $this->requireRole('labManager');
     * 
     *   // Only Faculty PI and Lab Manager can approve grants
     *   $this->requireRole(['facultyPI', 'labManager']);
     * 
     *   // Only researchers and guests can create bookings
     *   $this->requireRole(['researcher', 'guest']);
     * 
     * @param string|array $allowedRoles One role or array of roles
     */
    protected function requireRole($allowedRoles)
    {
        // First ensure user is logged in
        $this->requireLogin();

        // Convert single role string to array for uniform handling
        // 'labManager' → ['labManager']
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        // Get current user's role
        $currentRole = $this->getCurrentUserRole();

        // Check if user's role is in the allowed list
        if (!in_array($currentRole, $allowedRoles)) {
            // User doesn't have permission
            http_response_code(403);

            // Try to load custom 403 page
            $errorPage = __DIR__ . '/../views/errors/403.php';

            if (file_exists($errorPage)) {
                // Pass useful data to the error page
                $message = 'Access Denied: Your role does not have permission for this action.';
                $requiredRoles = $allowedRoles;
                require_once $errorPage;
            } else {
                // Fallback error
                echo "<!DOCTYPE html>
                <html>
                <head><title>403 — Access Denied | LabSync</title></head>
                <body style='font-family:Arial; text-align:center; padding:50px;'>
                    <h1>403 — Access Denied</h1>
                    <p>Your role (<strong>{$currentRole}</strong>) cannot access this page.</p>
                    <p>Required role: <strong>" . implode(' or ', $allowedRoles) . "</strong></p>
                    <a href='/LabSync-System/dashboard'>← Back to Dashboard</a>
                </body>
                </html>";
            }
            exit;
        }
    }

    /**
     * Check if current user has a specific role WITHOUT blocking
     * 
     * Returns true/false instead of showing error.
     * Use this in views to show/hide elements:
     * 
     *   // In controller, pass it to view
     *   $this->view('equipment/index', [
     *       'canLockout' => $this->hasRole('labManager')
     *   ]);
     * 
     *   // In view, use it to show/hide buttons
     *   <?php if ($canLockout): ?>
     *       <button>Emergency Lockout</button>
     *   <?php endif; ?>
     * 
     * @param string|array $roles One role or array of roles
     * @return bool True if user has one of the specified roles
     */
    protected function hasRole($roles)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array($this->getCurrentUserRole(), $roles);
    }


    // ═══════════════════════════════════════
    //  AUTOMATED SAFETY BRIEFING GATEKEEPER
    //  (Function #8 from Module 1)
    // ═══════════════════════════════════════
    //
    //  YOUR SRS SAYS:
    //  "A state-check forcing a user to acknowledge updated
    //   rules before the dashboard loads"
    //
    //  This blocks access to ANY protected page until the
    //  user has read and confirmed the safety briefing.
    //
    // ═══════════════════════════════════════

    /**
     * Require safety briefing acknowledgement
     * If not acknowledged → redirect to safety briefing page
     * 
     * Call this in controllers that should be blocked:
     * 
     *   public function index() {
     *       $this->requireLogin();
     *       $this->requireSafetyBriefing();   ← Blocks if not acknowledged
     *       // ... page loads normally
     *   }
     * 
     * WHAT HAPPENS:
     *   1. Gets user ID from session
     *   2. Queries database: has this user acknowledged the briefing?
     *   3. If YES → does nothing, code continues
     *   4. If NO → redirects to /compliance/safety-briefing
     */
    protected function requireSafetyBriefing()
    {
        // Must be logged in first
        $this->requireLogin();

        $userID = $this->getCurrentUserID();

        // Use your existing User model method
        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $acknowledged = $userModel->hasSafetyBriefingAcknowledged($userID);

        if (!$acknowledged) {
            $this->setFlash('warning', 'You must read and acknowledge the safety briefing before proceeding.');
            $this->redirect('/compliance/safety-briefing');
            // Script stops here
        }
    }


    // ═══════════════════════════════════════
    //  GUEST RESEARCHER EXPIRY CHECK
    //  (Function #6 from Module 3)
    // ═══════════════════════════════════════
    //
    //  YOUR SRS SAYS:
    //  "When a Guest Researcher's expiration_date passes,
    //   the system shall forcefully invalidate all of their
    //   active session tokens across all devices within 1 second"
    //
    // ═══════════════════════════════════════

    /**
     * Check if guest researcher's access has expired
     * If expired → destroy session and redirect to login
     * 
     * Call this in controllers accessible by guests:
     * 
     *   public function index() {
     *       $this->requireLogin();
     *       $this->checkGuestExpiry();   ← Auto-checks if guest is expired
     *       // ... if still here, guest is valid
     *   }
     * 
     * WHAT HAPPENS:
     *   1. Checks if current user is a guest (skips for other roles)
     *   2. Checks if expirationDate has passed
     *   3. If expired → destroys session, redirects to login
     *   4. If not expired → does nothing, code continues
     */
    protected function checkGuestExpiry()
    {
        // Skip if not logged in
        if (!$this->isLoggedIn()) {
            return;
        }

        $user = $this->getCurrentUser();

        // Only check for guest researchers — skip other roles
        if ($user['userType'] !== 'guest') {
            return;
        }

        // Check expiration date
        if (isset($user['expirationDate'])) {
            $expirationDate = strtotime($user['expirationDate']);
            $now = time();

            if ($now > $expirationDate) {
                // Guest access has expired — force logout
                session_destroy();
                session_start(); // Restart session to set flash message
                $this->setFlash('error', 'Your guest access has expired. Please contact the Lab Manager.');
                $this->redirect('/login');
                // Script stops here
            }
        }
    }


    // ═══════════════════════════════════════
    //  SYSTEM AUDIT TRAIL LOGGER
    //  (Function #8 from Module 3)
    // ═══════════════════════════════════════
    //
    //  YOUR SRS SAYS:
    //  "A background event listener recording an immutable
    //   history of every system modification"
    //  "The database must be strictly configured to allow
    //   only Append-Only (INSERT) permissions"
    //
    //  NFR-SE-01: "Prevent modification or deletion of
    //   100% of existing log entries"
    //
    // ═══════════════════════════════════════

    /**
     * Log an action to the immutable audit trail
     * 
     * Call this in ANY controller method that modifies data:
     * 
     *   // After creating a booking
     *   $this->logAction('booking_created', "Booking #$bookingID for equipment #$eqID");
     * 
     *   // After locking equipment
     *   $this->logAction('equipment_locked', "Emergency lockout: $reason", $equipmentID, 'equipment');
     * 
     *   // After approving a grant transaction
     *   $this->logAction('grant_approved', "Transaction #$txID approved", $txID, 'transaction');
     * 
     *   // After submitting an incident report
     *   $this->logAction('incident_reported', "Incident on equipment #$eqID", $incidentID, 'incident');
     * 
     * IMPORTANT:
     *   This method is wrapped in try/catch so that if logging fails,
     *   the main action still succeeds. Logging should NEVER crash
     *   the application.
     * 
     * @param string   $actionType  What happened (e.g., 'booking_created')
     * @param string   $description Human-readable details
     * @param int|null $targetID    ID of affected record (optional)
     * @param string|null $targetType Type of record: booking, equipment, grant, etc. (optional)
     */
    protected function logAction($actionType, $description = '', $targetID = null, $targetType = null)
    {
        try {
            $db = Database::getInstance()->getConnection();

            // INSERT only — this table should be append-only in the database
            // No UPDATE or DELETE queries should ever be run on this table
            $sql = "INSERT INTO SystemAuditLogs 
                    (userID, actionType, description, targetID, targetType, ipAddress, createdAt)
                    VALUES 
                    (:userID, :actionType, :description, :targetID, :targetType, :ipAddress, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':userID'      => $this->getCurrentUserID(),
                ':actionType'  => $actionType,
                ':description' => $description,
                ':targetID'    => $targetID,
                ':targetType'  => $targetType,
                ':ipAddress'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Log error to PHP error log — don't crash the app
            // The main action (booking, lockout, etc.) should still work
            // even if audit logging temporarily fails
            error_log("LabSync Audit Log Error: " . $e->getMessage());
        }
    }


    // ═══════════════════════════════════════
    //  FLASH MESSAGES
    //  Success/error messages shown after redirect
    // ═══════════════════════════════════════
    //
    //  THE PROBLEM:
    //    User submits "Create Booking" → controller processes → redirects to /booking
    //    After redirect, all variables are LOST.
    //    How does the user know the booking was created?
    //
    //  THE SOLUTION:
    //    Store message in $_SESSION (survives redirect)
    //    Read it on the next page load and DELETE it (show only once)
    //
    //  FLOW:
    //    1. Controller: $this->setFlash('success', 'Booking created!');
    //    2. Controller: $this->redirect('/booking');
    //    3. ---- HTTP REDIRECT HAPPENS ----
    //    4. New page loads, view() method auto-includes flash in $data
    //    5. View shows: "✅ Booking created!"
    //    6. Flash is deleted — refresh won't show it again
    //
    // ═══════════════════════════════════════

    /**
     * Set a flash message
     * 
     * @param string $type    'success', 'error', 'warning', or 'info'
     * @param string $message The text to display
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear the flash message
     * 
     * Called automatically by view() method so every page
     * has access to $flash variable.
     * 
     * Returns null if no flash message exists.
     * After reading, the message is DELETED (shown only once).
     * 
     * @return array|null ['type' => '...', 'message' => '...'] or null
     */
    protected function getFlash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']); // Delete after reading
            return $flash;
        }
        return null;
    }


    // ═══════════════════════════════════════
    //  PAGE SECTION CONTROL
    //  For the one-page-per-feature approach
    // ═══════════════════════════════════════
    //
    //  Since each feature is ONE page with multiple sections
    //  (list, create, edit, details), we need a way to tell
    //  the view which section to show after a form error.
    //
    //  NORMAL FLOW:
    //    User visits /booking → list section is visible
    //
    //  ERROR FLOW:
    //    User submits create form → validation fails
    //    → Controller reloads the page with create section open
    //    → User sees the error message in the create section
    //
    // ═══════════════════════════════════════

    /**
     * Build view data with a specific section active
     * 
     * Use this when a POST action fails and you need to
     * reload the page with a specific section/modal open:
     * 
     *   // In controller after validation fails:
     *   $viewData = $this->withSection('create', [
     *       'bookings'  => $bookings,
     *       'equipment' => $equipment,
     *       'error'     => 'Insufficient funds'
     *   ]);
     *   $this->view('booking/index', $viewData);
     * 
     *   // In the view:
     *   <script>
     *       <?php if (isset($activeSection)): ?>
     *           showSection('<?= $activeSection ?>');
     *       <?php endif; ?>
     *   </script>
     * 
     * @param string $section The section name to show ('create', 'edit', 'details')
     * @param array  $data    The existing view data array
     * @return array The data array with activeSection added
     */
    protected function withSection($section, $data = [])
    {
        $data['activeSection'] = $section;
        return $data;
    }

    /**
     * Build view data with a specific modal open
     * 
     * Similar to withSection but for modals:
     * 
     *   $viewData = $this->withModal('editModal', [
     *       'bookings' => $bookings,
     *       'editBooking' => $bookingToEdit,
     *       'error' => 'Invalid date range'
     *   ]);
     *   $this->view('booking/index', $viewData);
     * 
     *   // In view:
     *   <script>
     *       <?php if (isset($activeModal)): ?>
     *           document.getElementById('<?= $activeModal ?>').style.display = 'flex';
     *       <?php endif; ?>
     *   </script>
     * 
     * @param string $modalID The modal element ID to open
     * @param array  $data    The existing view data array
     * @return array The data array with activeModal added
     */
    protected function withModal($modalID, $data = [])
    {
        $data['activeModal'] = $modalID;
        return $data;
    }


    // ═══════════════════════════════════════
    //  FORM & INPUT HELPERS
    //  Safe methods to read user input
    // ═══════════════════════════════════════

    /**
     * Safely read a POST value (form field)
     * 
     * Instead of: $_POST['equipmentID'] (may cause "undefined index" warning)
     * Use:        $this->getPost('equipmentID') (returns null if missing)
     * 
     * Also trims whitespace automatically.
     * 
     * @param string $key     The form field name
     * @param mixed  $default Value to return if field doesn't exist (default: null)
     * @return mixed The field value or default
     */
    protected function getPost($key, $default = null)
    {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            // Trim strings, leave other types alone
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    /**
     * Safely read a GET value (URL query parameter)
     * 
     * For URLs like: /equipment?search=microscope&status=active
     *   $this->getQuery('search')  → 'microscope'
     *   $this->getQuery('status')  → 'active'
     *   $this->getQuery('page', 1) → 1 (default because 'page' doesn't exist)
     * 
     * @param string $key     The parameter name
     * @param mixed  $default Value to return if parameter doesn't exist
     * @return mixed The parameter value or default
     */
    protected function getQuery($key, $default = null)
    {
        if (isset($_GET[$key])) {
            $value = $_GET[$key];
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    /**
     * Check if the current request is a POST request
     * 
     * Useful when one method handles both GET and POST:
     * 
     *   public function index() {
     *       if ($this->isPost()) {
     *           // Handle form submission
     *       }
     *       // Show the page
     *   }
     * 
     * @return bool True if POST, false if GET
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}