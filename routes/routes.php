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

// --- BOOKINGS ---
$router->get('/bookings', 'BookingController', 'index');
$router->get('/booking/index', 'BookingController', 'index');
$router->post('/booking/store', 'BookingController', 'store');
$router->post('/booking/cancel/{id}', 'BookingController', 'cancel');
$router->get('/booking/view', 'BookingController', 'details');


$router->get('/grants', 'GrantController', 'index');
$router->post('/grants/reallocate', 'GrantController', 'reallocate');

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

// --- PI ---
$router->get('/pi/dashboard', 'PIController', 'index');
$router->post('/pi/approve-transaction', 'PIController', 'approveTransaction');

// --- COMPLIANCE ---
$router->post('/compliance/requestSupervision',    'ComplianceController', 'requestSupervision');
$router->get('/compliance/pending-supervisions',   'ComplianceController', 'showPendingSupervisions');
$router->post('/compliance/approveSupervision',    'ComplianceController', 'approveSupervision');
$router->post('/compliance/rejectSupervision',     'ComplianceController', 'rejectSupervision');
$router->get('/compliance/hazmat-alert',           'ComplianceController', 'displayAlert');
$router->post('/compliance/acknowledgeWarning',    'ComplianceController', 'acknowledgeWarning');