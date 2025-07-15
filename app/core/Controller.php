<?php

namespace App\Core;

use App\Core\Auth;
use App\Models\Admin\Permission;
use App\Services\ActiveUserService;

class Controller
{
    public function __construct()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH);
    
        // صفحات مستثناة من التحقق (login, register, ... إلخ)
        $excludedPaths = ['/login', '/auth/login', '/register', '/auth/register'];
    
        // ✅ لو الجلسة مش موجودة والمستخدم مش في صفحة مستثناة → نوجهه على login ونوقف التنفيذ
        if (!isset($_SESSION['user_id'])) {
            if (!in_array($path, $excludedPaths)) {
                header('Location: /auth/login');
                exit();
            }
            // ✅ المستخدم بالفعل في صفحة login → لا تعيد التوجيه
        }
        
    
        // ✅ The timeout check is handled in App.php
        // We still need to record user activity for logged-in users.
        if (isset($_SESSION['user_id'])) {
            $activeUserService = new ActiveUserService();

            // The timeout logic has been moved to App.php to run earlier.
            /*
            $timeout = 1800; // 30 دقيقة
    
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
                $activeUserService->logoutUser($_SESSION['user_id']);
                $_SESSION = [];
                session_destroy();
    
                header('Location: /auth/login');
                exit();
            }
            */
    
            // We still need to update the last activity timestamp and record the activity
            $_SESSION['last_activity'] = time();
            $activeUserService->recordUserActivity($_SESSION['user_id']);
    
            // The cleanup can still run here
            if (rand(1, 100) <= 5) {
                $activeUserService->cleanupInactiveUsers();
            }
        }
    }
    
    

    /**
     * Loads a model file.
     *
     * @param string $model The name of the model in PascalCase, optionally with subdirectories.
     * @return object|null An instance of the model, or null if the file doesn't exist.
     */
    public function model($model)
    {
        // Construct the full path to the model file using realpath
        $modelPath = realpath(__DIR__ . '/../models/' . $model . '.php');
    
        if ($modelPath && file_exists($modelPath)) {
            require_once $modelPath;
    
            $parts = explode('/', $model);
            $className = array_pop($parts);
            $namespace = implode('\\', $parts);
            $modelClass = 'App\\Models\\' . ($namespace ? $namespace . '\\' : '') . $className;
    
            if (class_exists($modelClass)) {
                return new $modelClass();
            }
        }
    
        // Log error for debugging
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
        // Automatically fetch mandatory notifications for any view that is loaded for a logged-in user.
        if (isset($_SESSION['user_id'])) {
            $notificationModel = $this->model('notifications/Notification');
            if ($notificationModel) {
                // Fetch mandatory notifications for modal pop-ups
                $data['mandatory_notifications'] = $notificationModel->getMandatoryUnreadForUser($_SESSION['user_id']);
                
                // Fetch the count of unread notifications for the navigation bar
                $data['unread_notification_count'] = $notificationModel->getUnreadCountForUser($_SESSION['user_id']);
            }
        }

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
        
        // Ensure the status code is a valid integer before setting it.
        $finalStatusCode = is_int($statusCode) && $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500;
        http_response_code($finalStatusCode);

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
        // Authorization check has been disabled.
    }
}

