<?php
require_once __DIR__ . '/../core/Router.php';
$router = new Router();

// --- AUTHENTICATION ---
$router->get('/',           'AuthController', 'showLogin');
$router->get('/login',      'AuthController', 'showLogin');
$router->post('/login',     'AuthController', 'doLogin');
$router->get('/logout',     'AuthController', 'logout');

// --- THE MISSING ROUTE (FIX) ---
$router->get('/dashboard',  'DashboardController', 'index');

// --- INCIDENTS ---
$router->get('/incidents', 'IncidentController', 'index');
$router->get('/incidents/create', 'IncidentController', 'create');
$router->post('/incidents/store','IncidentController', 'store');
$router->get('/incidents/{id}', 'IncidentController', 'show');

// --- EQUIPMENT ---
$router->get('/equipment',             'EquipmentController', 'index');
$router->get('/equipment/info/{id}',   'EquipmentController', 'info');
$router->get('/equipment/create',      'EquipmentController', 'create');
$router->get('/equipment/edit/{id}',   'EquipmentController', 'edit');
$router->post('/equipment/delete/{id}','EquipmentController', 'delete');
$router->post('/equipment/store',      'EquipmentController', 'store');
$router->post('/equipment/update/{id}', 'EquipmentController', 'update');
$router->post('/EquipmentController/book', 'EquipmentController', 'book');
$router->post('/equipment/acknowledge', 'EquipmentController', 'acknowledgeSafety');

// --- BOOKINGS & PHASE 1 APPROVALS ---
$router->get('/bookings', 'BookingController', 'index');
$router->get('/booking/index', 'BookingController', 'index');
$router->post('/booking/store', 'BookingController', 'store');
$router->post('/booking/cancel/{id}', 'BookingController', 'cancel');
$router->get('/booking/view', 'BookingController', 'details');
// NEW: Phase 1 Lab Manager Confirmation Route
$router->post('/booking/confirm/{id}', 'BookingController', 'confirm');

// --- GRANTS ---
$router->get('/grants', 'GrantController', 'index');
$router->post('/grants/reallocate', 'GrantController', 'reallocate');
$router->get('/grants/add', 'GrantController', 'create');
$router->post('/grants/store', 'GrantController', 'store');
$router->get('/grants/assign', 'GrantController', 'assign');
$router->post('/grants/processAssign', 'GrantController', 'processAssign');
$router->post('/grants/delete', 'GrantController', 'delete');
$router->get('/grants/manage', 'GrantController', 'manage');
$router->post('/grants/updateAssignment', 'GrantController', 'updateAssignment');

// --- ADMIN ---
$router->get('/admin', 'AdminController', 'index');
$router->get('/admin/users',                    'AdminController', 'users');
$router->get('/admin/users/create',             'AdminController', 'createUser');
$router->post('/admin/users/store',             'AdminController', 'storeUser');
$router->get('/admin/users/{id}',               'AdminController', 'showUser');
$router->post('/admin/users/{id}/status',       'AdminController', 'updateUserStatus');
$router->post('/admin/users/{id}/clearance',    'AdminController', 'updateUserClearance');
$router->get('/admin/equipment',                'AdminController', 'equipment');
$router->get('/admin/equipment/{id}',           'AdminController', 'showEquipment');
$router->post('/admin/equipment/{id}/status',   'AdminController', 'updateEquipmentStatus');
$router->post('/admin/equipment/{id}/rate',     'AdminController', 'updateEquipmentRate');
$router->get('/admin/grants',          'AdminController', 'grants');
$router->get('/admin/grants/{id}',     'AdminController', 'showGrant');
$router->get('/admin/logs',            'AdminController', 'auditLogs');
$router->get('/admin/logs/{id}',       'AdminController', 'showAuditLog');
$router->get('/admin/briefings',          'AdminController', 'briefings');
$router->get('/admin/briefings/create',   'AdminController', 'createBriefing');
$router->post('/admin/briefings/store',   'AdminController', 'storeBriefing');

// --- PI (Financial & Administrative) & PHASE 2 APPROVALS ---
$router->get('/pi', 'PIController', 'index');            
$router->get('/PI', 'PIController', 'index');            
$router->get('/pi/requests', 'PIController', 'requests');   
$router->get('/PI/requests', 'PIController', 'requests');   
$router->get('/pi/users', 'PIController', 'users');         
$router->get('/PI/users', 'PIController', 'users');         
$router->post('/pi/approve', 'PIController', 'approveTransaction');
$router->post('/PI/approve', 'PIController', 'approveTransaction');
$router->post('/PI/approveBooking', 'PIController', 'approveBooking');
$router->post('/PI/rejectBooking', 'PIController', 'rejectBooking');
$router->post('/PI/rejectTransactionList', 'PIController', 'rejectTransactionList');

// --- COMPLIANCE ---
$router->post('/compliance/requestSupervision',    'ComplianceController', 'requestSupervision');
$router->get('/compliance/pending-supervisions',   'ComplianceController', 'showPendingSupervisions');
$router->post('/compliance/approveSupervision',    'ComplianceController', 'approveSupervision');
$router->post('/compliance/rejectSupervision',     'ComplianceController', 'rejectSupervision');
$router->get('/compliance/hazmat-alert',           'ComplianceController', 'displayAlert');
$router->post('/compliance/acknowledgeWarning',    'ComplianceController', 'acknowledgeWarning');

// SESSIONS
$router->get('/sessions/active',  'SessionController', 'active');
$router->post('/sessions/start',  'SessionController', 'start');
$router->post('/sessions/end',    'SessionController', 'end');
$router->post('/compliance/acknowledgeWarning',    'ComplianceController', 'acknowledgeWarning');
