<?php


class Router
{

    private $routes = [];


    public function get($url, $controller, $method){
        $this->registerRoute('GET', $url, $controller, $method);
    }


    public function post($url, $controller, $method){
        $this->registerRoute('POST', $url, $controller, $method);
    }


    private function registerRoute($httpMethod, $url, $controller, $method){

        $url = '/' . trim($url, '/');

        $this->routes[$httpMethod][$url] = [
            'controller' => $controller,
            'method'     => $method
        ];
    }




    public function dispatch($url, $httpMethod)
    {

        $url = '/' . trim($url, '/');


        if (isset($this->routes[$httpMethod][$url])) {
            $route = $this->routes[$httpMethod][$url];
            $this->callController($route['controller'], $route['method']);
            return; 
        }


       
        if (isset($this->routes[$httpMethod])) {
            foreach ($this->routes[$httpMethod] as $routePattern => $route) {

                if (strpos($routePattern, '{') === false) {
                    continue;
                }

                $pattern = preg_replace(
                    '/\{([a-zA-Z]+)\}/',    
                    '([a-zA-Z0-9_-]+)',         
                    $routePattern
                );

                $pattern = '#^' . $pattern . '$#';


                if (preg_match($pattern, $url, $matches)) {

                    array_shift($matches);


                    $this->callController($route['controller'], $route['method'], $matches);
                    return; 
                }
            }
        }

        $this->handleNotFound($url);
    }


    private function callController($controller, $method, $params = []) {

        $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';

        if (!file_exists($controllerFile)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Controller File Not Found</h2>"
                . "<p>Looking for: <code>controllers/{$controller}.php</code></p>"
                . "<p><strong>Fix:</strong> Create this file in the controllers/ folder.</p>"
                . "</div>"
            );
        }


        require_once $controllerFile;

        if (!class_exists($controller)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Controller Class Not Found</h2>"
                . "<p>File <code>controllers/{$controller}.php</code> was loaded,</p>"
                . "<p>but no class named <code>{$controller}</code> was found inside.</p>"
                . "<p><strong>Fix:</strong> Make sure your class name matches: "
                . "<code>class {$controller} extends BaseController</code></p>"
                . "</div>"
            );
        }


        $controllerObject = new $controller();


        if (!method_exists($controllerObject, $method)) {
            die(
                "<div style='font-family:Arial; padding:20px;'>"
                . "<h2>⚠️ LabSync Error: Method Not Found</h2>"
                . "<p>Class <code>{$controller}</code> doesn't have method "
                . "<code>{$method}()</code></p>"
                . "<p><strong>Available methods:</strong> "
                . implode(', ', get_class_methods($controllerObject))
                . "</p>"
                . "</div>"
            );
        }

        call_user_func_array([$controllerObject, $method], $params);
    }


    private function handleNotFound($url)
    {
        http_response_code(404);
        $errorPage = __DIR__ . '/../views/errors/404.php';
        
        if (file_exists($errorPage)) {
            require_once $errorPage;
        } else {
            die("<h2 style='color:red;'>404 Not Found</h2><p>The route <b>'{$url}'</b> is not defined in routes.php</p>");
        }
    }
}