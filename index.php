<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/App.php';

$app = new App();