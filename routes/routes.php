<?php
/**
 * ═══════════════════════════════════════════════════
 * LabSync System — Route Definitions
 * ═══════════════════════════════════════════════════
 * 
 * This file defines EVERY URL in the application.
 * If a URL is not listed here, it returns 404.
 * 
 * ── PAGE STRATEGY: ONE PAGE PER FEATURE ──
 * 
 *   Each feature has ONE GET route that loads the entire page
 *   with all sections (list, create, edit, details) built in.
 *   
 *   Forms on each page submit to separate POST routes.
 *   After POST processes, controller redirects BACK to the GET page.
 * 
 *   Example:
 *     GET  /booking       → Loads page (list + create form + edit modal + details modal)
 *     POST /booking/store → "Create Booking" form submits here → redirects to /booking
 *     POST /booking/cancel/{id} → "Cancel" button submits here → redirects to /booking
 * 
 *   User stays on /booking the entire time.
 *   Sections switch via JavaScript tabs/modals on the same page.
 * 
 * ── FORMAT ──
 *   $router->get('/url',  'ControllerName', 'methodName');   ← Load page
 *   $router->post('/url', 'ControllerName', 'methodName');   ← Process form
 * 
 * ── DYNAMIC PARAMETERS ──
 *   $router->post('/booking/cancel/{id}', 'BookingController', 'cancel');
 *   When form submits to /booking/cancel/5 → cancel($id) is called with $id = '5'
 * 
 * ── NOTE ──
 *   $router variable is created in App.php and available here
 *   because this file is loaded with require_once inside App.php
 * ═══════════════════════════════════════════════════
 */


// ═══════════════════════════════════════════════════
//  AUTHENTICATION
//  Controller: AuthController
//  ───────────────────────────────────────────────
//  These are the ONLY public pages (no login required).
//  All other routes require login.
// 
//  Pages: Login page, Registration page
//  Login and Register each have their OWN page
//  because the user is NOT logged in yet — no dashboard,
//  no navbar, no shared layout with other features.
// ═══════════════════════════════════════════════════

// ── Root URL → Login page ──
// When user visits localhost/LabSync-System/ with nothing after it
// They should see the login page (your project starts from login)
$router->get('/',          'AuthController', 'showLogin');

// ── Login ──
// GET  = Show the login form (HTML page)
// POST = Process the submitted username/password
$router->get('/login',     'AuthController', 'showLogin');
$router->post('/login',    'AuthController', 'login');

// ── Registration ──
// GET  = Show the registration form
// POST = Process the new user data and create account
$router->get('/register',  'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');

// ── Logout ──
// GET = Destroy session and redirect to login
// No POST needed — logout doesn't submit any data
$router->get('/logout',    'AuthController', 'logout');


// ═══════════════════════════════════════════════════
//  DASHBOARD
//  Controller: DashboardController
//  ───────────────────────────────────────────────
//  ONE page showing overview cards and statistics.
//  Requires: Login + Safety Briefing Acknowledgement