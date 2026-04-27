<?php
/**
 * ═══════════════════════════════════════════════════
 * LabSync System — Single Entry Point (Front Controller)
 * ═══════════════════════════════════════════════════
 * 
 * EVERY request in the entire application starts HERE.
 * No other PHP file is accessed directly by the browser.
 * 
 * This file does 3 simple things:
 *   1. Starts the session system (enables $_SESSION)
 *   2. Loads all core framework files
 *   3. Creates the App object which routes the request
 * 
 * IMPORTANT:
 *   session_start() does NOT log anyone in.
 *   It just opens the session system so we CAN use it.
 *   Think of it as "turning on the lights" —
 *   doesn't mean anyone is in the building.
 * 
 * FLOW:
 *   Browser → .htaccess → index.php → App → Router → Controller → View
 * 
 * THIS FILE SHOULD NEVER CONTAIN:
 *   ❌ Business logic
 *   ❌ HTML
 *   ❌ Database queries
 *   ❌ Direct output (echo/print)
 * ═══════════════════════════════════════════════════
 */

// ══════════════════════════════════════════════════
// STEP 1: Start the session system
// ══════════════════════════════════════════════════
// This enables $_SESSION so controllers can:
//   - Store user data after login: $_SESSION['user'] = $userData
//   - Check if user is logged in:  isset($_SESSION['user'])
//   - Store flash messages:        $_SESSION['flash'] = 'Success!'
//
// Must be called BEFORE any output (no echo/HTML before this)
session_start();

// ══════════════════════════════════════════════════
// STEP 2: Load core framework files
// ══════════════════════════════════════════════════
// Order matters! Each file depends on the ones before it.
//
// Database.php  → Singleton PDO connection (your existing file)
// Router.php    → Maps URLs to Controllers
// Controller.php → Base class with shared methods (login checks, etc.)
// App.php       → Creates Router, loads routes, dispatches request

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/App.php';

// ══════════════════════════════════════════════════
// STEP 3: Launch the application
// ══════════════════════════════════════════════════
// This single line triggers the ENTIRE request lifecycle:
//   App.__construct() → creates Router → loads routes.php
//   → gets URL from browser → dispatches to correct Controller
$app = new App();