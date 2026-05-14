<?php

class App
{
    private $basePath = 'LabSync-System';

    public function __construct()
    {
        // Define $router first so routes.php can use it
        $router = new Router(); 
        require_once __DIR__ . '/../routes/routes.php';

        $url = $this->getURL();
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        // Dispatch the request
        $router->dispatch($url, $httpMethod);
    }

    private function getURL()
    {
        $url = $_GET['url'] ?? '';
        
        // Remove project folder name if it exists in URL
        $url = str_replace($this->basePath, '', $url);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = rtrim($url, '/');
        $url = ltrim($url, '/');

        return $url;
    }
}