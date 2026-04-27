<?php
/**
 * ═══════════════════════════════════════════════════
 * LabSync System — Application Bootstrap
 * ═══════════════════════════════════════════════════
 * 
 * This class GLUES everything together.
 * It connects: Router ↔ Routes ↔ URL ↔ Controller
 * 
 * WHAT IT DOES:
 *   1. Creates the Router object
 *   2. Loads route definitions from routes/routes.php
 *   3. Reads the current URL from the browser
 *   4. Tells Router to dispatch (find matching route → call controller)
 * 
 * LIFECYCLE (every single request):
 *   Browser → .htaccess → index.php → App → Router → Controller → View
 * 
 * CREATED ONCE:
 *   In index.php: $app = new App();
 *   The constructor does all the work.
 * ═══════════════════════════════════════════════════
 */

class App
{
    /**
     * The base folder name of the application
     * 
     * If your project is at: localhost/LabSync-System/
     * Set this to: 'LabSync-System'
     * 
     * If your project is at: localhost/
     * Set this to: ''
     * 
     * CHANGE THIS if your folder name is different!
     */
    private $basePath = 'LabSync-System';

    /**
     * Constructor — The entire request lifecycle happens here
     * 
     * When index.php creates new App(), this constructor:
     *   1. Creates a Router
     *   2. Loads all route definitions
     *   3. Gets the URL the user is trying to visit
     *   4. Dispatches to the correct controller
     * 
     * After this constructor finishes, the response has been sent
     * and the script ends.
     */
    public function __construct()
    {
        // ══════════════════════════════════════
        // STEP 1: Create the Router
        // ══════════════════════════════════════
        // Router starts empty — no routes registered yet
        $router = new Router();


        // ══════════════════════════════════════
        // STEP 2: Load all route definitions
        // ══════════════════════════════════════
        // routes.php contains all $router->get() and $router->post() calls
        // The $router variable is available inside routes.php because
        // require_once runs the file in THIS scope (where $router exists)
        //
        // After this, the Router knows about all URLs:
        //   GET  /login, /dashboard, /booking, /equipment, etc.
        //   POST /booking/store, /booking/cancel/{id}, etc.
        $routesFile = __DIR__ . '/../routes/routes.php';

        if (!file_exists($routesFile)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Routes File Not Found</h2>"
                . "<p>Looking for: <code>routes/routes.php</code></p>"
                . "<p><strong>Fix:</strong> Create this file and define your routes.</p>"
                . "</div>"
            );
        }

        require_once $routesFile;


        // ══════════════════════════════════════
        // STEP 3: Get the current URL
        // ══════════════════════════════════════
        // .htaccess converts:
        //   localhost/LabSync-System/booking → index.php?url=booking
        // getURL() reads $_GET['url'] and cleans it
        $url = $this->getURL();


        // ══════════════════════════════════════
        // STEP 4: Get the HTTP method
        // ══════════════════════════════════════
        // 'GET'  → User clicked a link or typed URL (loading a page)
        // 'POST' → User submitted a form (creating/updating/deleting data)
        $httpMethod = $_SERVER['REQUEST_METHOD'];


        // ══════════════════════════════════════
        // STEP 5: Dispatch!
        // ══════════════════════════════════════
        // Router takes the URL and HTTP method,
        // finds the matching route, and calls the controller.
        //
        // Example:
        //   URL = 'booking', Method = 'GET'
        //   Router finds: GET /booking → BookingController::index()
        //   Router creates BookingController object and calls index()
        //   index() loads the booking page with all sections
        $router->dispatch($url, $httpMethod);
    }

    /**
     * Extract and clean the URL from the browser request
     * 
     * HOW IT WORKS:
     *   Browser URL:    localhost/LabSync-System/booking
     *   .htaccess sets: $_GET['url'] = 'booking'
     *   This method:    Returns 'booking'
     *   Router adds /:  '/booking'
     *   Router matches: GET /booking → BookingController::index()
     * 
     * EDGE CASES:
     *   localhost/LabSync-System/           → Returns '' → Router makes '/' → root route
     *   localhost/LabSync-System/booking/   → Returns 'booking' (trailing / removed)
     *   localhost/LabSync-System/BOOKING    → Returns 'BOOKING' (case preserved)
     * 
     * @return string The clean URL
     */
    private function getURL()
    {
        // ── Read URL from .htaccess ──
        // .htaccess rule: RewriteRule ^(.+)$ index.php?url=\$1
        // This puts everything after the base path into $_GET['url']
        // If no URL (root), default to empty string
        $url = $_GET['url'] ?? '';

        // ── Remove base path if accidentally included ──
        // Sometimes .htaccess includes the folder name in the URL
        // This removes it so the Router only sees the clean path
        if (!empty($this->basePath)) {
            // Remove 'LabSync-System' from 'LabSync-System/booking'
            // Result: '/booking' or 'booking'
            $url = str_replace($this->basePath, '', $url);
        }

        // ── Sanitize the URL ──
        // Remove any dangerous characters
        // Only allows: letters, numbers, /, -, _, .
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // ── Remove trailing slashes ──
        // 'booking/' becomes 'booking'
        // This ensures consistent matching in the Router
        $url = rtrim($url, '/');

        // ── Remove leading slashes ──
        // '/booking' becomes 'booking'
        // The Router will add the / back when matching
        $url = ltrim($url, '/');

        return $url;
    }
}