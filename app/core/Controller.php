<?php

namespace App\Core;

use App\Core\Auth;
use App\Models\Admin\Permission;

class Controller
{
    public function __construct()
    {
        // Constructor is now empty, enforcement is done in App.php
    }

    /**
     * Loads a model file.
     *
     * @param string $model The name of the model in PascalCase, optionally with subdirectories.
     * @return object|null An instance of the model, or null if the file doesn't exist.
     */
    public function model($model)
    {
        // Construct the full path to the model file
        $modelPath = '../app/models/' . $model . '.php';

        // Check if the model file exists before trying to require it
        if (file_exists($modelPath)) {
            require_once $modelPath;
            // Construct the full class name with namespace, capitalizing directory names for namespace
            $parts = explode('/', $model);
            $className = array_pop($parts);
            $namespaceParts = array_map('ucfirst', $parts);
            $namespace = implode('\\', $namespaceParts);
            $modelClass = 'App\\Models\\' . ($namespace ? $namespace . '\\' : '') . $className;
            
            if (class_exists($modelClass)) {
                return new $modelClass();
            }
        }
        
        // In case of any failure, log the error and return null.
        // This prevents fatal errors and helps in debugging.
        error_log("Model not found or class does not exist: " . $model);
        return null;
    }

    /**
     * Loads a view file.
     *
     * @param string $view The name of the view file.
     * @param array $data Data to be extracted for use in the view.
     * @return void
     */
    public function view($view, $data = [])
    {
        // Check for view file
        if (file_exists('../app/views/' . $view . '.php')) {
            // Extract data so it can be used by variable names in the view
            extract($data);
            require_once '../app/views/' . $view . '.php';
        } else {
            // If view does not exist, stop everything and show an error.
            die('View does not exist: ' . $view);
        }
    }

    protected function sendJsonResponse($data, $statusCode = 200)
    {
        // Clean any previous output buffer to prevent corrupting the JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        // Ensure Arabic characters are encoded correctly
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Checks if the current user has the required permission.
     * If not, it shows a 403 Forbidden page and stops execution.
     *
     * @param string|array $requiredPermission The permission string or an array of permissions to check for.
     * @return void
     */
    public function authorize($requiredPermission)
    {
        $hasPermission = false;
        if (is_array($requiredPermission)) {
            foreach ($requiredPermission as $permission) {
                if (Auth::hasPermission($permission)) {
                    $hasPermission = true;
                    break;
                }
            }
        } else {
            $hasPermission = Auth::hasPermission($requiredPermission);
        }

        if (!$hasPermission) {
            http_response_code(403);
            
            // Prepare debug information for the 403 page
            $debug_info = [
                'required_permission' => is_array($requiredPermission) ? implode(', ', $requiredPermission) : $requiredPermission,
                'user_role' => $_SESSION['role'] ?? 'Not Set',
                'user_permissions' => $_SESSION['permissions'] ?? 'Not Set'
            ];
            
            // Pass debug info to the view
            $data['debug_info'] = $debug_info;
            
            require_once '../app/views/errors/403.php';
            exit;
        }
    }
}

