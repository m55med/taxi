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
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch($uri, $requestType)
    {
        $uri = trim($uri, '/');

        // First, check for a direct static match
        if (isset($this->routes[$requestType][$uri])) {
            list($controller, $method) = explode('@', $this->routes[$requestType][$uri]);
            $controllerFullName = 'App\\Controllers\\' . str_replace('/', '\\', $controller);
            return $this->callAction($controllerFullName, $method);
        }

        // Handle dynamic routes with parameters
        foreach ($this->routes[$requestType] as $route => $controllerAction) {
            // Skip non-dynamic routes as they would have matched already
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

        $this->checkPermissions($controller, $method);

        return call_user_func_array([$controllerInstance, $method], $params);
    }

    protected function checkPermissions($controller, $method)
    {
        // Public pages that do not require a login or permission check
        $publicRoutes = [
            'Auth/login',
            'Auth/register',
            'Referral/index',
            'PasswordReset/showRequestForm',
            'PasswordReset/handleRequestForm',
            'PasswordReset/showResetForm',
            'PasswordReset/handleReset'
        ];

        // Get the short name of the controller class
        $reflector = new \ReflectionClass($controller);
        $controllerShortName = str_replace('Controller', '', $reflector->getShortName());

        $permissionKey = $controllerShortName . '/' . $method;

        // If the route is public, skip the check
        if (in_array($permissionKey, $publicRoutes)) {
            return;
        }

        // If user is not logged in, redirect to login page
        if (!isset($_SESSION['user_id'])) {
            // Start session if not already started to handle flash messages
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
        
        // Routes that require login but not a specific permission key.
        $loggedInButNoPermissionNeeded = [
            'Discussions/addReply',
            'Discussions/close',
        ];
        if (in_array($permissionKey, $loggedInButNoPermissionNeeded, true)) {
            return; // Skip specific permission check
        }

        // Final check for the permission
        if (!Auth::hasPermission($permissionKey)) {
            http_response_code(403);
            $debug_info = [
                'required_permission' => $permissionKey,
                'user_role' => $_SESSION['role'] ?? 'Not Set',
                'user_permissions' => $_SESSION['permissions'] ?? []
            ];
            $data['debug_info'] = $debug_info;
            require_once APPROOT . '/views/errors/403.php';
            exit;
        }
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