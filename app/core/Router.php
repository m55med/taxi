<?php

namespace App\Core;

use App\Core\Auth;

class Router
{
    public $routes = [
        'GET' => [],
        'POST' => []
    ];
    private $latestRoute = null;

    public static function load($file)
    {
        $router = new static;
        require $file;
        return $router;
    }

    public function get($uri, $controller)
    {
        return $this->addRoute('GET', $uri, $controller);
    }

    public function post($uri, $controller)
    {
        return $this->addRoute('POST', $uri, $controller);
    }

    private function addRoute($method, $uri, $controller)
    {
        $this->latestRoute = ['method' => $method, 'uri' => $uri];
        $this->routes[$method][$uri] = [
            'controller' => $controller,
            'middleware' => null,
            'permission' => $this->generatePermissionKey($controller)
        ];
        return $this;
    }
    
    private function generatePermissionKey($controllerAction)
    {
        list($controller, $method) = explode('@', $controllerAction);
        
        // This logic is designed to match the one in Permission::discoverPermissions
        $parts = explode('/', str_replace('\\', '/', $controller));
        $className = end($parts);
        
        // Remove "Controller" from the end of the class name
        $permissionKey = preg_replace('/Controller$/', '', $className);

        // Handle dynamic method names like {action}
        if (strpos($method, '{') === 0 && strpos($method, '}') !== false) {
            // Replace dynamic part with a wildcard or a resolvable pattern.
            // For now, let's assume it needs to be resolved during dispatch.
            // For permission generation, we might use a generic name.
            $method = '*';
        }

        return $permissionKey . '/' . $method;
    }

    public function middleware($roles)
    {
        if ($this->latestRoute) {
            $method = $this->latestRoute['method'];
            $uri = $this->latestRoute['uri'];
            if (isset($this->routes[$method][$uri])) {
                $this->routes[$method][$uri]['middleware'] = is_array($roles) ? $roles : [$roles];
            }
        }
        return $this;
    }

    public function dispatch($uri, $requestType)
    {
        $uri = trim($uri, '/');

        // Check for direct static match first
        if (array_key_exists($requestType, $this->routes) && array_key_exists($uri, $this->routes[$requestType])) {
            $route = $this->routes[$requestType][$uri];
            return $this->handleRoute($route);
        }

        // Handle dynamic routes
        foreach ($this->routes[$requestType] as $routeUri => $routeDetails) {
            if (strpos($routeUri, '{') !== false) {
                $pattern = "#^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $routeUri) . "$#";
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);
                    return $this->handleRoute($routeDetails, $matches);
                }
            }
        }

        $this->triggerNotFound('No route defined for this URI: ' . $uri);
    }

    protected function handleRoute($route, $params = [])
    {
        if (!empty($route['middleware'])) {
            Auth::requireAccess($route['middleware'], $route['permission']);
        }
        
        list($controller, $method) = explode('@', $route['controller']);
        
        // Handle dynamic method placeholders like {action}
        if (preg_match('/^\{(.+)\}$/', $method, $method_matches)) {
            // This logic is simplified. It assumes the placeholder name matches the order.
            // A more robust implementation would use named parameters from the route.
            $method = $params[0] ?? 'index'; // Default to 'index' if not found
            array_shift($params); // The first parameter was the method name
        }

        $controllerFullName = 'App\\Controllers\\' . str_replace('/', '\\', $controller);
        
        return $this->callAction($controllerFullName, $method, $params);
    }

    protected function callAction($controller, $method, $params = [])
    {
        if (!class_exists($controller)) {
            $this->triggerNotFound("Controller class {$controller} not found.");
            return;
        }

        $controllerInstance = new $controller;

        if (!method_exists($controllerInstance, $method)) {
            $this->triggerNotFound("Method '{$method}' does not exist on controller {$controller}.");
            return;
        }

        call_user_func_array([$controllerInstance, $method], $params);
    }

    private function triggerNotFound($message = 'Page not found.')
    {
        http_response_code(404);
        error_log("404 Not Found: " . $message);
        require_once '../app/views/errors/404.php';
        exit;
    }
}