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

    private function getNavigationItems()
    {
        // Centralized navigation structure
        return [
            [
                'title' => 'Dashboard',
                'url' => URLROOT . '/dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'permission' => 'dashboard/index'
            ],
            [
                'title' => 'Discussions',
                'url' => URLROOT . '/discussions',
                'icon' => 'fas fa-comments',
                'permission' => 'discussions/index'
            ],
            [
                'title' => 'Tickets',
                'url' => URLROOT . '/tickets/create',
                'icon' => 'fas fa-ticket-alt',
                'permission' => 'tickets/create'
            ],
            [
                'title' => 'Admin',
                'icon' => 'fas fa-cogs',
                'permission' => 'admin/index', // A general permission to see the Admin dropdown
                'children' => [
                    [
                        'title' => 'Users',
                        'url' => URLROOT . '/admin/users',
                        'icon' => 'fas fa-users-cog',
                        'permission' => 'admin/users/index'
                    ],
                    [
                        'title' => 'Teams',
                        'url' => URLROOT . '/admin/teams',
                        'icon' => 'fas fa-users',
                        'permission' => 'admin/teams/index'
                    ],
                    [
                        'title' => 'Permissions',
                        'url' => URLROOT . '/admin/permissions',
                        'icon' => 'fas fa-user-shield',
                        'permission' => 'admin/permissions/index'
                    ],
                     [
                        'title' => 'Telegram Settings',
                        'url' => URLROOT . '/admin/telegram_settings',
                        'icon' => 'fab fa-telegram-plane',
                        'permission' => 'admin/telegram_settings/index'
                    ],
                ]
            ],
            [
                'title' => 'Reports',
                'icon' => 'fas fa-chart-pie',
                'permission' => 'reports/index', // A general permission for the reports section
                'children' => [
                    [
                        'title' => 'Drivers Report',
                        'url' => URLROOT . '/reports/drivers',
                        'icon' => 'fas fa-id-card',
                        'permission' => 'reports/drivers/index'
                    ],
                    [
                        'title' => 'Trips Report',
                        'url' => URLROOT . '/reports/trips',
                        'icon' => 'fas fa-route',
                        'permission' => 'reports/trips/index'
                    ],
                ]
            ]
        ];
    }
    
    private function filterNavigation($navItems)
    {
        $filteredNav = [];
        foreach ($navItems as $item) {
            // If the item has a permission key, check it
            if (isset($item['permission']) && !Auth::hasPermission($item['permission'])) {
                continue; // Skip this item if user doesn't have permission
            }

            // If the item has children, filter them recursively
            if (isset($item['children'])) {
                $item['children'] = $this->filterNavigation($item['children']);
                // If after filtering, no children are left, don't show the parent dropdown
                if (empty($item['children'])) {
                    continue;
                }
            }
            
            $filteredNav[] = $item;
        }
        return $filteredNav;
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
            // Construct the full class name with namespace, using directory names as they are
            $parts = explode('/', $model);
            $className = array_pop($parts);
            $namespace = implode('\\', $parts); // Keep original casing for namespace parts
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
        // Automatically fetch mandatory notifications for any view that is loaded for a logged-in user.
        if (isset($_SESSION['user_id'])) {
            // We create a temporary model instance here to avoid loading it for every single controller.
            $notificationModel = $this->model('notifications/Notification');
            if ($notificationModel) {
                $data['mandatory_notifications'] = $notificationModel->getMandatoryUnreadForUser($_SESSION['user_id']);
            }

            // Prepare navigation
            $allNavItems = $this->getNavigationItems();
            $data['nav_items'] = $this->filterNavigation($allNavItems);
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

