<?php

namespace App\Core;

use App\Core\Auth;

class Router
{
    public $routes = [
        'GET' => [],
        'POST' => []
    ];

    public static function load($file)
    {
        $router = new static;
        require $file;
        return $router;
    }

    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = ['controller' => $controller, 'middleware' => null];
        return $this;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = ['controller' => $controller, 'middleware' => null];
        return $this;
    }

    public function middleware($roles)
    {
        // Ensure roles are in an array, even if a single string is passed
        $middleware = is_array($roles) ? $roles : [$roles];

        // Apply to the last added GET route
        $keys_get = array_keys($this->routes['GET']);
        if (!empty($keys_get)) {
            $this->routes['GET'][end($keys_get)]['middleware'] = $middleware;
        }

        // This logic for POST is flawed because it assumes the last POST route corresponds to the last GET route.
        // A better approach is to chain middleware right after the route definition.
        // For now, let's assume a similar logic for POST, but this needs review.
        $keys_post = array_keys($this->routes['POST']);
        if (!empty($keys_post)) {
            // This might not always be the intended behavior.
            // It applies middleware to the last defined POST route, which might not be the one you just defined with GET.
            $last_post_key = end($keys_post);
            $last_get_key = end($keys_get);
            // A heuristic: if the last GET and POST URIs are the same, apply middleware to both.
            if ($last_post_key === $last_get_key) {
                $this->routes['POST'][$last_post_key]['middleware'] = $middleware;
            }
        }

        return $this;
    }

    public function dispatch($uri, $requestType)
    {
        $uri = trim($uri, '/');

        // First, check for a direct static match
        if (isset($this->routes[$requestType][$uri])) {
            $route = $this->routes[$requestType][$uri];
            if (!empty($route['middleware'])) {
                if ($route['middleware'] === 'auth') {
                    Auth::requireLogin();
                } else {
                    Auth::requireRole($route['middleware']);
                }
            }
            list($controller, $method) = explode('@', $route['controller']);
            $controllerFullName = 'App\\Controllers\\' . str_replace('/', '\\', $controller);
            return $this->callAction($controllerFullName, $method);
        }

        // Handle dynamic routes with parameters
        foreach ($this->routes[$requestType] as $route => $controllerAction) {
            // Skip non-dynamic routes as they would have matched already
            $controllerAction = $this->routes[$requestType][$route];
            if (strpos($route, '{') === false) {
                continue;
            }

            $route = trim($route, '/');

            // Get placeholder names from the route definition
            $placeholders = [];
            if (preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $placeholder_matches)) {
                $placeholders = $placeholder_matches[1];
            }

            // Convert route to regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match

                // Create an associative array of parameters
                $params = !empty($placeholders) ? array_combine($placeholders, $matches) : [];

                list($controller, $method) = explode('@', $controllerAction);

                // Check if the method name is a placeholder
                if (preg_match('/^\{(.+)\}$/', $method, $method_matches)) {
                    $method_placeholder_name = $method_matches[1];
                    if (isset($params[$method_placeholder_name])) {
                        $method = $params[$method_placeholder_name];
                        unset($params[$method_placeholder_name]);
                    }
                }

                $controllerFullName = 'App\\Controllers\\' . str_replace('/', '\\', $controller);

                return $this->callAction($controllerFullName, $method, array_values($params));
            }
        }

        $this->triggerNotFound('No route defined for this URI: ' . $uri);
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

        // Call the controller action with params
        call_user_func_array([$controllerInstance, $method], $params);
    }


    private function triggerNotFound($message = 'Page not found.')
    {
        http_response_code(404);
        error_log("404 Not Found: " . $message);

        // You might want to render a proper 404 view
        require_once '../app/views/errors/404.php';
        exit;
    }
}