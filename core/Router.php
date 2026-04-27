<?php
/**
 * ═══════════════════════════════════════════════════
 * LabSync System — Router
 * ═══════════════════════════════════════════════════
 * 
 * The Router is the BRAIN of URL handling.
 * 
 * ITS JOB:
 *   1. Store a list of all registered routes (URL → Controller mapping)
 *   2. When a request comes in, find the matching route
 *   3. Call the correct Controller and method
 * 
 * OUR PAGE STRATEGY:
 *   Each feature has ONE page (one GET route) with all sections.
 *   Forms on that page submit to separate POST routes.
 *   
 *   Example for Booking:
 *     GET  /booking              → BookingController::index()  → Loads the WHOLE page
 *     POST /booking/store        → BookingController::store()  → Handles "Create" form
 *     POST /booking/update/{id}  → BookingController::update() → Handles "Edit" form
 *     POST /booking/cancel/{id}  → BookingController::cancel() → Handles "Cancel" button
 *   
 *   The user STAYS on /booking the whole time.
 *   Forms submit, controller processes, redirects BACK to /booking.
 * 
 * SUPPORTS:
 *   - GET routes  (loading pages)
 *   - POST routes (form submissions)
 *   - Dynamic parameters: /booking/view/{id} where {id} = any value
 * 
 * SECURITY:
 *   - Only URLs registered here can be accessed
 *   - Internal helper methods in controllers are NOT accessible via URL
 *   - POST-only routes cannot be triggered by clicking a link
 * ═══════════════════════════════════════════════════
 */

class Router
{
    /**
     * ═══════════════════════════════════════
     * Routes storage array
     * ═══════════════════════════════════════
     * 
     * Structure:
     * [
     *   'GET' => [
     *     '/booking' => ['controller' => 'BookingController', 'method' => 'index'],
     *   ],
     *   'POST' => [
     *     '/booking/store' => ['controller' => 'BookingController', 'method' => 'store'],
     *   ]
     * ]
     */
    private $routes = [];


    // ═══════════════════════════════════════
    // ROUTE REGISTRATION
    // These methods are called in routes/routes.php
    // to define all URLs in the application
    // ═══════════════════════════════════════

    /**
     * Register a GET route
     * 
     * GET routes are for LOADING PAGES.
     * When user visits a URL in the browser or clicks a link,
     * that's a GET request.
     * 
     * In our one-page-per-feature approach, we have ONE GET route
     * per feature that loads the entire page with all sections:
     * 
     *   $router->get('/booking', 'BookingController', 'index');
     *   // This ONE route loads: list + create form + edit form + details modal
     * 
     * @param string $url         The URL pattern
     * @param string $controller  The controller class name
     * @param string $method      The method to call
     */
    public function get($url, $controller, $method)
    {
        $this->registerRoute('GET', $url, $controller, $method);
    }

    /**
     * Register a POST route
     * 
     * POST routes are for PROCESSING FORMS.
     * When user clicks a submit button in a form,
     * that's a POST request.
     * 
     * Each form on the page has its own POST route:
     * 
     *   $router->post('/booking/store', 'BookingController', 'store');
     *   // Handles the "Create New Booking" form
     * 
     *   $router->post('/booking/cancel/{id}', 'BookingController', 'cancel');
     *   // Handles the "Cancel" button inside the details modal
     * 
     * WHY POST-only?
     *   Security! Actions like "cancel booking" or "lock equipment"
     *   should NEVER happen from just clicking a link (GET).
     *   They must come from a form submission (POST).
     * 
     * @param string $url         The URL pattern
     * @param string $controller  The controller class name
     * @param string $method      The method to call
     */
    public function post($url, $controller, $method)
    {
        $this->registerRoute('POST', $url, $controller, $method);
    }

    /**
     * Internal: Store a route in the routes array
     * 
     * Both get() and post() call this method.
     * This avoids duplicating the storage logic.
     * 
     * @param string $httpMethod  'GET' or 'POST'
     * @param string $url         The URL pattern
     * @param string $controller  The controller class name
     * @param string $method      The method to call
     */
    private function registerRoute($httpMethod, $url, $controller, $method)
    {
        // Ensure URL always starts with /
        // 'booking' becomes '/booking'
        // '/booking' stays '/booking'
        $url = '/' . trim($url, '/');

        $this->routes[$httpMethod][$url] = [
            'controller' => $controller,
            'method'     => $method
        ];
    }


    // ═══════════════════════════════════════
    // DISPATCH — The Main Method
    // Matches the current URL to a route
    // and calls the correct controller
    // ═══════════════════════════════════════

    /**
     * Find the matching route for the current URL and execute it
     * 
     * This is called by App.php with the current URL and HTTP method.
     * 
     * It tries to match in this order:
     *   1. EXACT match (fastest) — /booking, /login, /dashboard
     *   2. DYNAMIC match (with {parameters}) — /booking/cancel/{id}
     *   3. No match → 404 error page
     * 
     * EXAMPLE:
     *   URL = '/booking/cancel/5', Method = 'POST'
     *   
     *   Step 1: Exact match? Is '/booking/cancel/5' registered? NO
     *   Step 2: Dynamic match? Loop through POST routes...
     *           '/booking/cancel/{id}' → converts to regex → matches!
     *           Extracts: $id = '5'
     *           Calls: BookingController::cancel('5')
     * 
     * @param string $url        The URL from the browser
     * @param string $httpMethod 'GET' or 'POST'
     */
    public function dispatch($url, $httpMethod)
    {
        // ── Clean the URL ──
        // Remove slashes, add leading slash
        // '' → '/', 'booking/' → '/booking', 'booking/view/5' → '/booking/view/5'
        $url = '/' . trim($url, '/');

        // Handle empty URL (root of the site)
        if ($url === '/') {
            $url = '/';
        }


        // ══════════════════════════════════════
        // TRY 1: Exact Match (No parameters)
        // ══════════════════════════════════════
        // This handles simple routes like:
        //   GET  /booking     → BookingController::index()
        //   POST /booking/store → BookingController::store()
        //   GET  /login       → AuthController::showLogin()
        //
        // This is the FASTEST match because it's just an array lookup.
        // Most of our GET routes (one per feature) will match here.

        if (isset($this->routes[$httpMethod][$url])) {
            $route = $this->routes[$httpMethod][$url];
            $this->callController($route['controller'], $route['method']);
            return; // Done! Stop looking.
        }


        // ══════════════════════════════════════
        // TRY 2: Dynamic Match (With {parameters})
        // ══════════════════════════════════════
        // This handles routes with variables like:
        //   POST /booking/cancel/{id}  → URL: /booking/cancel/5  → cancel($id=5)
        //   POST /equipment/update/{id} → URL: /equipment/update/3 → update($id=3)
        //   GET  /grants/forecast/{id}  → URL: /grants/forecast/7 → forecast($id=7)
        //
        // HOW IT WORKS:
        //   1. Take the route pattern: '/booking/cancel/{id}'
        //   2. Convert {id} to a regex group: '/booking/cancel/([a-zA-Z0-9_-]+)'
        //   3. Try to match the actual URL against this regex
        //   4. If it matches, extract the value of {id}
        //   5. Pass it as a parameter to the controller method

        if (isset($this->routes[$httpMethod])) {
            foreach ($this->routes[$httpMethod] as $routePattern => $route) {

                // Skip routes without { } — they already failed in Try 1
                if (strpos($routePattern, '{') === false) {
                    continue;
                }

                // Convert route pattern to regex
                // '/booking/cancel/{id}' → '/booking/cancel/([a-zA-Z0-9_-]+)'
                // '/admin/profile/{id}'  → '/admin/profile/([a-zA-Z0-9_-]+)'
                $pattern = preg_replace(
                    '/\{([a-zA-Z]+)\}/',        // Find {paramName}
                    '([a-zA-Z0-9_-]+)',          // Replace with regex capture group
                    $routePattern
                );

                // Wrap in regex delimiters and add start/end anchors
                // This ensures FULL match, not partial
                $pattern = '#^' . $pattern . '$#';

                // Try to match the actual URL
                if (preg_match($pattern, $url, $matches)) {

                    // $matches[0] = full URL match (we don't need this)
                    // $matches[1] = first {param} value
                    // $matches[2] = second {param} value (if exists)
                    array_shift($matches); // Remove the full match, keep only params

                    // Call the controller with the extracted parameters
                    // Example: BookingController::cancel('5')
                    $this->callController($route['controller'], $route['method'], $matches);
                    return; // Done! Stop looking.
                }
            }
        }


        // ══════════════════════════════════════
        // NO MATCH FOUND → 404 Error
        // ══════════════════════════════════════
        // If we get here, no route matched the URL.
        // Show a friendly 404 error page.
        $this->handleNotFound($url);
    }


    // ═══════════════════════════════════════
    // CONTROLLER EXECUTION
    // Loads the file, creates object, calls method
    // ═══════════════════════════════════════

    /**
     * Load a controller file, create its object, and call the method
     * 
     * THIS IS WHERE OOP HAPPENS:
     *   1. require_once loads the class file
     *   2. new $controller() creates an object of that class
     *   3. The object inherits all BaseController methods
     *      (requireLogin, requireRole, view, redirect, etc.)
     *   4. call_user_func_array calls the specific method with params
     * 
     * EXAMPLE:
     *   callController('BookingController', 'cancel', ['5'])
     *   
     *   1. Loads: controllers/BookingController.php
     *   2. Creates: $obj = new BookingController()
     *      (BookingController extends BaseController, so $obj has ALL helper methods)
     *   3. Calls: $obj->cancel('5')
     * 
     * @param string $controller Class name (e.g., 'BookingController')
     * @param string $method     Method name (e.g., 'cancel')
     * @param array  $params     URL parameters (e.g., ['5'])
     */
    private function callController($controller, $method, $params = [])
    {
        // ── Build file path ──
        $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';

        // ── Check file exists ──
        if (!file_exists($controllerFile)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Controller File Not Found</h2>"
                . "<p>Looking for: <code>controllers/{$controller}.php</code></p>"
                . "<p><strong>Fix:</strong> Create this file in the controllers/ folder.</p>"
                . "</div>"
            );
        }

        // ── Load the file ──
        require_once $controllerFile;

        // ── Check class exists in the file ──
        if (!class_exists($controller)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Controller Class Not Found</h2>"
                . "<p>File <code>controllers/{$controller}.php</code> was loaded,</p>"
                . "<p>but no class named <code>{$controller}</code> was found inside.</p>"
                . "<p><strong>Fix:</strong> Make sure your class name matches: "
                . "<code>class {$controller} extends BaseController</code></p>"
                . "</div>"
            );
        }

        // ── Create the controller object (OOP!) ──
        // This calls the constructor, which may set up model objects
        $controllerObject = new $controller();

        // ── Check method exists ──
        if (!method_exists($controllerObject, $method)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Method Not Found</h2>"
                . "<p>Class <code>{$controller}</code> doesn't have method "
                . "<code>{$method}()</code></p>"
                . "<p><strong>Available methods:</strong> "
                . implode(', ', get_class_methods($controllerObject))
                . "</p>"
                . "</div>"
            );
        }

        // ── Call the method with parameters ──
        // If URL was /booking/cancel/5 and route was /booking/cancel/{id}
        // Then $params = ['5'] and this calls: $controllerObject->cancel('5')
        call_user_func_array([$controllerObject, $method], $params);
    }


    // ═══════════════════════════════════════
    // 404 ERROR HANDLING
    // ═══════════════════════════════════════

    /**
     * Handle 404 Not Found errors
     * 
     * Shows a user-friendly error page when someone visits
     * a URL that doesn't exist in our routes.
     * 
     * First tries to load a custom 404 view.
     * If that doesn't exist, shows a simple HTML fallback.
     * 
     * @param string $url The URL that wasn't found
     */
    private function handleNotFound($url)
    {
        // Set HTTP status code to 404
        http_response_code(404);

        // Try custom 404 page
        $errorPage = __DIR__ . '/../views/errors/404.php';

        if (file_exists($errorPage)) {
            require_once $errorPage;
        } else {
            // Fallback: simple error message
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <title>404 — Page Not Found | LabSync</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        text-align: center; 
                        padding: 80px 20px;
                        background: #f5f5f5;
                    }
                    h1 { color: #333; font-size: 48px; }
                    p { color: #666; font-size: 18px; }
                    code { 
                        background: #e0e0e0; 
                        padding: 3px 8px; 
                        border-radius: 4px; 
                    }
                    a { 
                        color: #007bff; 
                        text-decoration: none;
                        font-size: 18px;
                    }
                    a:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <h1>404</h1>
                <p>The page <code>{$url}</code> was not found in LabSync.</p>
                <a href='/LabSync-System/login'>← Back to Login</a>
            </body>
            </html>";
        }
    }
}