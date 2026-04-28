<?php

$router->get('/',          'AuthController', 'showLogin');

$router->get('/login',     'AuthController', 'showLogin');
$router->post('/login',    'AuthController', 'login');

$router->get('/logout',    'AuthController', 'logout');
