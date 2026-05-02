<?php

$router->get('/',          'AuthController', 'showLogin');

$router->get('/login',     'AuthController', 'showLogin');
$router->post('/login',    'AuthController', 'login');

$router->get('/logout',    'AuthController', 'logout');




//  INCIDENTS
$router->get('/incidents', 'IncidentController', 'index');
$router->get('/incidents/create', 'IncidentController', 'create');
$router->post('/incidents/store','IncidentController', 'store');
$router->get('/incidents/{id}', 'IncidentController', 'show');



$router->get('/',           'AuthController', 'showLogin');
$router->get('/login',      'AuthController', 'showLogin');
$router->post('/login',     'AuthController', 'doLogin');
$router->get('/logout',     'AuthController', 'logout');