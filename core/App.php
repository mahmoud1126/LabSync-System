<?php

class App
{

    private $basePath = 'LabSync-System';


    public function __construct()
    {

        $router = new Router(); 
        require_once __DIR__ . '/../routes/routes.php';

        $url = $this->getURL();

        $httpMethod = $_SERVER['REQUEST_METHOD'];

        $router->dispatch($url, $httpMethod);
    }


    private function getURL()
    {

        $url = $_GET['url'] ?? '';

        $url = str_replace($this->basePath, '', $url);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = rtrim($url, '/');
        $url = ltrim($url, '/');

        return $url;
    }
}