<?php
require_once __DIR__ . '/../core/Router.php';
$router = new Router();

// login
$router->get('/',           'AuthController', 'showLogin');
$router->get('/login',      'AuthController', 'showLogin');
$router->post('/login',     'AuthController', 'doLogin');
$router->get('/logout',     'AuthController', 'logout');


//  INCIDENTS
$router->get('/incidents', 'IncidentController', 'index');
$router->get('/incidents/create', 'IncidentController', 'create');
$router->post('/incidents/store','IncidentController', 'store');
$router->get('/incidents/{id}', 'IncidentController', 'show');




$router->get('/admin', 'AdminController', 'index');


// User Management 
$router->get('/admin/users',                    'AdminController', 'users');
$router->get('/admin/users/create',             'AdminController', 'createUser');
$router->post('/admin/users/store',             'AdminController', 'storeUser');
$router->get('/admin/users/{id}',               'AdminController', 'showUser');
$router->post('/admin/users/{id}/status',       'AdminController', 'updateUserStatus');
$router->post('/admin/users/{id}/clearance',    'AdminController', 'updateUserClearance');


//Equipment Management 
$router->get('/admin/equipment',                'AdminController', 'equipment');
$router->get('/admin/equipment/{id}',           'AdminController', 'showEquipment');
$router->post('/admin/equipment/{id}/status',   'AdminController', 'updateEquipmentStatus');
$router->post('/admin/equipment/{id}/rate',     'AdminController', 'updateEquipmentRate');


//Grants Overview 
$router->get('/admin/grants',          'AdminController', 'grants');
$router->get('/admin/grants/{id}',     'AdminController', 'showGrant');


//Audit Logs 
$router->get('/admin/logs',            'AdminController', 'auditLogs');
$router->get('/admin/logs/{id}',       'AdminController', 'showAuditLog');


//Safety Briefings Management
$router->get('/admin/briefings',          'AdminController', 'briefings');
$router->get('/admin/briefings/create',   'AdminController', 'createBriefing');
$router->post('/admin/briefings/store',   'AdminController', 'storeBriefing');

// COMPLIANCE
$router->post('/compliance/requestSupervision',    'ComplianceController', 'requestSupervision');
$router->get('/compliance/pending-supervisions',   'ComplianceController', 'showPendingSupervisions');
$router->post('/compliance/approveSupervision',    'ComplianceController', 'approveSupervision');
$router->post('/compliance/rejectSupervision',     'ComplianceController', 'rejectSupervision');
$router->get('/compliance/hazmat-alert',           'ComplianceController', 'displayAlert');
$router->post('/compliance/acknowledgeWarning',    'ComplianceController', 'acknowledgeWarning');

