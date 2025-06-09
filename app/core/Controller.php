<?php

namespace App\Core;

class Controller
{
    public function __construct()
    {
        // Constructor is empty but available for inheritance
    }

    protected function model($model)
    {
        // Handle both namespaced and non-namespaced models
        if (strpos($model, '/') !== false) {
            // For models in subdirectories (e.g., 'call/Calls')
            $parts = explode('/', $model);
            $className = "\\App\\Models\\" . ucfirst($parts[0]) . "\\" . $parts[1];
        } else {
            // For models in the root models directory
            $className = "\\App\\Models\\" . $model;
        }

        if (!class_exists($className)) {
            die('Model class "' . $className . '" does not exist. Attempted to load: ' . $className);
        }

        return new $className();
    }

    protected function view($view, $data = [])
    {
        $viewPath = APPROOT . '/app/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            die('View does not exist');
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
}
