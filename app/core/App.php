<?php

namespace App\Core;

use App\Controllers\Auth\AuthController;
use App\Models\User\User;

class App
{
    protected $controller = 'AuthController';  // الكنترولر الافتراضي
    protected $method = 'login';                // الميثود الافتراضية
    protected $params = [];
    protected $currentControllerName = 'Home'; // Default
    protected $currentMethodName = 'index';   // Default

    public function __construct()
    {
        // Force logout check
        if (isset($_SESSION['user_id'])) {
            $this->checkAndHandleForceLogout($_SESSION['user_id']);
            $this->checkAndRefreshPermissions($_SESSION['user_id']); // New permission refresh check

            // Update user's online status periodically
            $userModel = new User();
            $userModel->updateOnlineStatus($_SESSION['user_id'], 1); // Set as online
        }

        $url = $this->parseUrl();

        // New Permission Check Logic
        if (!empty($url[0])) {
            // Simplified logic - this needs to match your app's routing structure
            $this->currentControllerName = ucwords($url[0]);
            if (isset($url[1])) {
                $this->currentMethodName = $url[1];
            }
        }

        // Handle Telegram Webhook - Check if the last part of the URL is 'telegram'
        if (!empty($url) && end($url) === 'telegram') {
            $controller = new \App\Controllers\Telegram\WebhookController();
            $controller->handle();
            return; // Stop further processing
        }

        $url = is_array($url) ? $url : [];

        // تعيين المتحكم
        if (!empty($url[0])) {
            if ($url[0] === 'admin' && isset($url[1])) {
                // Correctly convert snake_case URL to PascalCase controller name
                $controllerName = str_replace('_', '', ucwords($url[1], '_')) . 'Controller';
                $controllerFile = '../app/controllers/admin/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Admin\\' . $controllerName;
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[2]) && method_exists($this->controller, $url[2]) ? $url[2] : 'index';
                    unset($url[0], $url[1]);
                    if (isset($url[2])) {
                        unset($url[2]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'referral') {
                $controllerName = 'ReferralController';
                $methodName = 'index'; // Default method

                if (isset($url[1])) {
                    // Explicitly map URL segments to methods
                    switch ($url[1]) {
                        case 'dashboard':
                            $methodName = 'dashboard';
                            break;
                        case 'saveAgentProfile':
                            $methodName = 'saveAgentProfile';
                            break;
                        case 'register':
                        default:
                            $methodName = 'index';
                            break;
                    }
                }

                $controllerFile = '../app/controllers/referral/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Referral\\' . $controllerName;
                    $this->controller = new $controllerClass();

                    if (method_exists($this->controller, $methodName)) {
                        $this->method = $methodName;
                        unset($url[0], $url[1]);
                    } else {
                        // Fallback to index if method doesn't exist
                        $this->method = 'index';
                        unset($url[0]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'reports' && isset($url[1])) {
                $reportType = $url[1];
                $controllerName = '';

                // استخدام switch لتحديد اسم الكنترولر بشكل صريح ودقيق
                switch ($reportType) {
                    case 'user':
                    case 'users':
                        $controllerName = 'Users/UsersController';
                        break;
                    case 'driver':
                    case 'drivers':
                        $controllerName = 'Drivers/DriversController';
                        break;
                    case 'call':
                    case 'calls':
                        $controllerName = 'Calls/CallsController';
                        break;
                    case 'driver-calls':
                        $controllerName = 'DriverCalls/DriverCallsController';
                        break;
                    case 'assignment':
                    case 'assignments':
                        $controllerName = 'Assignments/AssignmentsController';
                        break;
                    case 'analytic':
                    case 'analytics':
                        $controllerName = 'Analytics/AnalyticsController';
                        break;
                    case 'document':
                    case 'documents':
                        $controllerName = 'Documents/DocumentsController';
                        break;
                    case 'myactivity':
                        $controllerName = 'MyActivity/MyActivityController';
                        break;
                    case 'teamperformance':
                        $controllerName = 'TeamPerformance/TeamPerformanceController';
                        break;
                    case 'coupons':
                        $controllerName = 'Coupons/CouponsController';
                        break;
                    case 'logs':
                        $controllerName = 'Logs/LogsController';
                        break;
                    case 'tickets':
                        $controllerName = 'Tickets/TicketsController';
                        break;
                    case 'driver-documents-compliance':
                        $controllerName = 'DriverDocumentsCompliance/DriverDocumentsComplianceController';
                        break;
                    case 'driver-assignments':
                        $controllerName = 'DriverAssignments/DriverAssignmentsController';
                        break;
                    case 'tickets-summary':
                        $controllerName = 'TicketsSummary/TicketsSummaryController';
                        break;
                    case 'ticket-reviews':
                        $controllerName = 'TicketReviews/TicketReviewsController';
                        break;
                    case 'ticket-discussions':
                        $controllerName = 'TicketDiscussions/TicketDiscussionsController';
                        break;
                    case 'ticket-coupons':
                        $controllerName = 'TicketCoupons/TicketCouponsController';
                        break;
                    case 'referral-visits':
                        $controllerName = 'ReferralVisits/ReferralVisitsController';
                        break;
                    case 'marketer-summary':
                        $controllerName = 'MarketerSummary/MarketerSummaryController';
                        break;
                    case 'review-quality':
                        $controllerName = 'ReviewQuality/ReviewQualityController';
                        break;
                    case 'ticket-rework':
                        $controllerName = 'TicketRework/TicketReworkController';
                        break;
                    case 'system-logs':
                        $controllerName = 'SystemLogs/SystemLogsController';
                        break;
                    case 'employee-activity-score':
                        $controllerName = 'EmployeeActivityScore/EmployeeActivityScoreController';
                        break;
                    case 'team-leaderboard':
                        $controllerName = 'TeamLeaderboard/TeamLeaderboardController';
                        break;
                    case 'custom':
                        $controllerName = 'Custom/CustomController';
                        break;
                    case 'trips':
                        $controllerName = 'TripsReport/TripsReportController';
                        break;
                }

                if ($controllerName) {
                    $controllerPath = 'reports/' . $controllerName;
                    $controllerFile = '../app/controllers/' . $controllerPath . '.php';

                    if (file_exists($controllerFile)) {
                        $controllerName = str_replace('/', '\\', $controllerName);
                        $controllerClass = '\\App\\Controllers\\Reports\\' . $controllerName;
                        $this->controller = new $controllerClass();
                        $this->method = isset($url[2]) && method_exists($this->controller, $url[2]) ? $url[2] : 'index';
                        unset($url[0], $url[1]);
                        if (isset($url[2])) {
                            unset($url[2]);
                        }
                    } else {
                        // Fallback in case controller file is missing
                        $this->triggerNotFound();
                    }
                } else {
                    // Fallback for unknown report types
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'call' || $url[0] === 'calls') {
                $controllerName = 'CallsController';
                $controllerFile = '../app/controllers/calls/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $this->controller = new \App\Controllers\Calls\CallsController();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'driver') {
                $controllerName = 'DriverController';
                $controllerFile = '../app/controllers/driver/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $this->controller = new \App\Controllers\Driver\DriverController();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'trips') { // Handle trips upload route
                $controllerName = 'TripsController';
                $controllerFile = '../app/controllers/trips/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Trips\\' . $controllerName;
                    $this->controller = new $controllerClass();

                    // Determine method: /trips/upload or /trips/process
                    $methodName = 'upload'; // Default method if only /trips is provided
                    if (isset($url[1])) {
                        if ($url[1] === 'upload') {
                            $methodName = 'upload';
                        } elseif ($url[1] === 'process') {
                            $methodName = 'process';
                        }
                    }

                    if (method_exists($this->controller, $methodName)) {
                        $this->method = $methodName;
                    } else {
                        // Fallback to upload form if an invalid method is specified
                        $this->method = 'upload';
                    }

                    unset($url[0], $url[1], $url[2]);

                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'ticket' || $url[0] === 'tickets') {
                $controllerName = 'Ticket'; // Default controller
                $methodName = 'index';     // Default method

                if (isset($url[1])) {
                    // Route to the new TicketsController for details, reviews, discussions, etc.
                    if (in_array($url[1], ['details', 'addReview', 'addDiscussion', 'addObjection', 'closeDiscussion'])) {
                        $controllerName = 'Tickets'; // The new controller with the 's'
                        $methodName = $url[1];
                        unset($url[0], $url[1]);
                    } elseif ($url[1] === 'data' && isset($url[2])) {
                        // Handle existing data routes
                        $controllerName = 'Data';
                        $methodName = $url[2];
                        unset($url[0], $url[1], $url[2]);
                    } else {
                        // Fallback to the old TicketController for other methods like 'create', 'index' etc.
                        $methodName = $url[1];
                        unset($url[0], $url[1]);
                    }
                } else {
                    // Just /tickets -> goes to old TicketController@index
                    unset($url[0]);
                }

                $controllerClass = '\\App\\Controllers\\Tickets\\' . ucfirst($controllerName) . 'Controller';
                $controllerFile = '../app/controllers/tickets/' . ucfirst($controllerName) . 'Controller.php';

                if (file_exists($controllerFile)) {
                    $this->controller = new $controllerClass();
                    if (method_exists($this->controller, $methodName)) {
                        $this->method = $methodName;
                    } else {
                        $this->triggerNotFound();
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'logs') { // Handle logs route
                $controllerName = 'LogsController';
                $controllerFile = '../app/controllers/logs/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Logs\\' . $controllerName;
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'discussions') { // Handle discussions route
                $controllerName = 'DiscussionsController';
                $controllerFile = '../app/controllers/discussions/' . $controllerName . '.php';
                if (file_exists($controllerFile)) {
                    $this->controller = new \App\Controllers\Discussions\DiscussionsController();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1]))
                        unset($url[1]);
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'review') { // Handle review route
                $controllerName = 'ReviewController';
                $controllerFile = '../app/controllers/review/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Review\\' . $controllerName;
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'dashboard') { // Handle dashboard route
                $controllerName = 'DashboardController';
                $controllerFile = '../app/controllers/dashboard/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Dashboard\\' . $controllerName;
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'auth') {
                $controllerName = 'AuthController';
                $controllerFile = '../app/controllers/Auth/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Auth\\' . $controllerName;
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'login';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
                    }
                } else {
                    $this->triggerNotFound();
                }
            } elseif ($url[0] === 'calls' || $url[0] === 'call') {
                $url[0] = 'calls';
                $controllerName = 'Calls';
                $methodName = 'index';
                $paramsOffset = 1;

                // Check for sub-controllers like assignments or documents
                if (isset($url[1]) && in_array($url[1], ['assignments', 'documents'])) {
                    $controllerName = ucfirst($url[1]);
                    $methodName = $url[2] ?? 'index';
                    $paramsOffset = 3;
                } elseif (isset($url[1])) {
                    // It's a method on the main CallsController
                    $methodName = $url[1];
                    $paramsOffset = 2;
                }

                $controllerFile = '../app/controllers/calls/' . $controllerName . 'Controller.php';
                $controllerClass = '\\App\\Controllers\\Calls\\' . $controllerName . 'Controller';

                if (file_exists($controllerFile)) {
                    $this->controller = new $controllerClass();
                    if (method_exists($this->controller, $methodName)) {
                        $this->method = $methodName;
                        // Unset controller and method parts from URL array
                        for ($i = 0; $i < $paramsOffset; $i++) {
                            unset($url[$i]);
                        }
                    } else {
                        $this->triggerNotFound("Method {$methodName} not found in controller {$controllerClass}.");
                    }
                } else {
                    $this->triggerNotFound("Controller {$controllerClass} not found at {$controllerFile}.");
                }
            } else {
                // التعامل مع باقي المسارات
                $controllerName = ucfirst($url[0]) . 'Controller';

                // Handle 'drivers' route specifically to map to 'DriverController'
                if (strtolower($url[0]) === 'drivers') {
                    $controllerName = 'Driver/DriverController';
                } else {
                    // Handle other controllers in subdirectories
                    if (in_array(strtolower($url[0]), ['upload'])) {
                        $controllerName = strtolower($url[0]) . '/' . ucfirst($url[0]) . 'Controller';
                    } else {
                        $controllerName = ucfirst($url[0]) . 'Controller';
                    }
                }

                // Regular controllers
                $controllerNameParts = explode('/', $controllerName);
                $controllerClassName = implode('\\', array_map('ucfirst', $controllerNameParts));
                $controllerClass = '\\App\\Controllers\\' . $controllerClassName;
                $controllerFile = '../app/controllers/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1]))
                        unset($url[1]);
                } else {
                    $this->triggerNotFound('Controller not found: ' . $controllerFile);
                }
            }
        } else {
            // المسار الافتراضي
            $this->controller = new \App\Controllers\Auth\AuthController();
        }

        $this->params = $url ? array_values($url) : [];

        // If the user is logged in and trying to access login/register pages
        if (
            isset($_SESSION['user_id']) && in_array($this->currentControllerName . '/' . $this->currentMethodName, [
                'Auth/login',
                'Auth/register'
            ])
        ) {
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Centralized Permission Check at the end of the constructor
        $this->checkPermissions();

        // Call the method on the controller with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Centralized method to check user permissions.
     */
    protected function checkPermissions()
    {
        // Public pages that do not require a login or permission check
        $publicRoutes = [
            'Auth/login',
            'Auth/register',
            'Referral/register'
            // Note: Telegram webhook has its own entry point and is handled before this
        ];
        
        // Get the short name of the controller class (e.g., AuthController -> Auth)
        $reflector = new \ReflectionClass($this->controller);
        $controllerShortName = str_replace('Controller', '', $reflector->getShortName());
        
        $permissionKey = $controllerShortName . '/' . $this->method;

        // If the route is public, skip the check
        if (in_array($permissionKey, $publicRoutes)) {
            return;
        }

        // If user is not logged in, redirect to login page
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
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
            require_once APPROOT . '/app/views/errors/403.php';
                        exit;
                    }
                }

    /**
     * Checks for a force-logout signal and handles it.
     * @param int $userId
     */
    private function checkAndHandleForceLogout(int $userId)
    {
        $forceLogoutFile = APPROOT . '/database/force_logout/' . $userId;
        if (file_exists($forceLogoutFile)) {
            $userModel = new User();
            $logoutMessage = trim(file_get_contents($forceLogoutFile));
            unlink($forceLogoutFile);
            $userModel->logout($userId);
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['error'] = 'تم تسجيل خروجك بواسطة مسؤول' . (!empty($logoutMessage) && $logoutMessage !== '1' ? ': ' . htmlspecialchars($logoutMessage) : '.');
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
    }

    /**
     * Checks for a permission-refresh signal and updates the session.
     * @param int $userId
     */
    private function checkAndRefreshPermissions(int $userId)
    {
        $refreshFile = APPROOT . '/app/cache/refresh_permissions/' . $userId;
        if (file_exists($refreshFile)) {
            // Re-fetch permissions from the database
            $permissionModel = new \App\Models\Admin\Permission();
            $_SESSION['permissions'] = $permissionModel->getPermissionsByUser($userId);
            
            // Clean up the signal file
            unlink($refreshFile);
        }
    }

    private function triggerNotFound($message = 'Page not found.')
    {
        http_response_code(404);
        // Log the detailed error message for debugging
        error_log("404 Not Found: " . $message);

        $data = [];
        // Prepare diagnostics for development environment
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $data['debug_message'] = $message;
            $data['diagnostics'] = [
                'requested_url' => $_GET['url'] ?? 'Not set',
                'parsed_url'    => $this->parseUrl(),
                'controller'    => is_object($this->controller) ? get_class($this->controller) : $this->controller,
                'method'        => $this->method,
                'params'        => $this->params
            ];
        }
        
        // Extract data so it's available in the view
        if (!empty($data)) {
            extract($data);
        }
        
        // Show the user-friendly 404 page (now with potential debug data)
        require_once '../app/views/errors/404.php';
        exit;
    }

    public function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}
