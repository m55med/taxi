<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Models\User;

class App
{
    protected $controller = 'AuthController';  // الكنترولر الافتراضي
    protected $method = 'login';                // الميثود الافتراضية
    protected $params = [];

    public function __construct()
    {
        // Force logout check
        if (isset($_SESSION['user_id'])) {
            $forceLogoutFile = APPROOT . '/database/force_logout/' . $_SESSION['user_id'];
            if (file_exists($forceLogoutFile)) {
                $userModel = new User();
                $logoutMessage = trim(file_get_contents($forceLogoutFile));
                
                // Unlink the file first to prevent loop issues
                unlink($forceLogoutFile);
                
                $userModel->logout($_SESSION['user_id']);
                
                session_unset();
                session_destroy();
                
                session_start();
                if (!empty($logoutMessage) && $logoutMessage !== '1') {
                    $_SESSION['error'] = 'تم تسجيل خروجك بواسطة مسؤول: ' . htmlspecialchars($logoutMessage);
                } else {
                    $_SESSION['error'] = 'تم تسجيل خروجك بواسطة مسؤول.';
                }
                header('Location: ' . BASE_PATH . '/auth/login');
                exit;
            }

            // Update user's online status periodically
            $userModel = new User();
            $userModel->updateOnlineStatus($_SESSION['user_id'], 1); // Set as online
        }

        $url = $this->parseUrl();

        // Handle Telegram Webhook - Check if the last part of the URL is 'telegram'
        if (!empty($url) && end($url) === 'telegram') {
            $controller = new \App\Controllers\Telegram\TelegramController();
            $controller->handleWebhook();
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
                $controllerFile = '../app/controllers/referral/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $controllerClass = '\\App\\Controllers\\Referral\\' . $controllerName;
                    $this->controller = new $controllerClass();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) {
                        unset($url[1]);
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
                        $controllerName = 'UserReportController';
                        break;
                    case 'driver':
                    case 'drivers':
                        $controllerName = 'DriversReportController';
                        break;
                    case 'call':
                    case 'calls':
                        $controllerName = 'CallsReportController';
                        break;
                    case 'assignment':
                    case 'assignments':
                        $controllerName = 'AssignmentsReportController';
                        break;
                    case 'analytic':
                    case 'analytics':
                        $controllerName = 'AnalyticsReportController';
                        break;
                    case 'document':
                    case 'documents':
                        $controllerName = 'DocumentsReportController';
                        break;
                }

                if ($controllerName) {
                    $controllerPath = 'reports/' . $controllerName;
                    $controllerFile = '../app/controllers/' . $controllerPath . '.php';

                    if (file_exists($controllerFile)) {
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
                $controllerFile = '../app/controllers/' . $controllerName . '.php';
                if (file_exists($controllerFile)) {
                    $this->controller = new \App\Controllers\DiscussionsController();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) unset($url[1]);
                } else {
                    $this->triggerNotFound();
                }
            } else {
                // التعامل مع باقي المسارات
                $controllerName = ucfirst($url[0]) . 'Controller';
                
                // Check for modular controllers first (e.g. call/CallsController)
                if ($url[0] === 'call') {
                    $subController = isset($url[1]) ? ucfirst($url[1]) : 'Calls';
                    $controllerClass = '\\App\\Controllers\\Call\\' . $subController . 'Controller';
                    $controllerFile = '../app/controllers/call/' . $subController . 'Controller.php';
                    
                    if (file_exists($controllerFile)) {
                        $this->controller = new $controllerClass();
                        $this->method = isset($url[2]) && method_exists($this->controller, $url[2]) ? $url[2] : 'index';
                        unset($url[0], $url[1]);
                        if (isset($url[2])) unset($url[2]);
                    } else {
                        // If specific controller not found, use main CallsController
                        $controllerClass = '\\App\\Controllers\\Call\\CallsController';
                        $controllerFile = '../app/controllers/call/CallsController.php';
                        $this->controller = new $controllerClass();
                        $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                        unset($url[0]);
                        if (isset($url[1])) unset($url[1]);
                    }
                } else {
                    // Handle 'drivers' route specifically to map to 'DriverController'
                    if (strtolower($url[0]) === 'drivers') {
                        $controllerName = 'DriverController';
                    }

                    // Regular controllers
                    $controllerClass = '\\App\\Controllers\\' . $controllerName;
                    $controllerFile = '../app/controllers/' . $controllerName . '.php';
                    
                    if (file_exists($controllerFile)) {
                        $this->controller = new $controllerClass();
                        $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                        unset($url[0]);
                        if (isset($url[1])) unset($url[1]);
                    } else {
                        // إذا لم يوجد الملف، استخدم الكنترولر الافتراضي
                        $this->controller = new AuthController();
                        $this->method = 'login';
                    }
                }
            }
        } else {
            // المسار الافتراضي
            $this->controller = new AuthController();
        }

        $this->params = $url ? array_values($url) : [];

        // تحديد المسارات العامة (غير المحمية)
        $publicRoutes = [
            'App\\Controllers\\AuthController/login',
            'App\\Controllers\\AuthController/register',
            'App\\Controllers\\Referral\\ReferralController/index',
            'App\\Controllers\\Telegram\\TelegramController/handleWebhook'
        ];

        $currentRoute = get_class($this->controller) . '/' . $this->method;

        // التحقق من تسجيل الدخول للمسارات المحمية
        if (!isset($_SESSION['user_id']) && !in_array($currentRoute, $publicRoutes)) {
            $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        // إذا كان المستخدم مسجل دخوله ويحاول الوصول إلى صفحات تسجيل الدخول/التسجيل
        if (isset($_SESSION['user_id']) && in_array($currentRoute, [
            'App\\Controllers\\AuthController/login',
            'App\\Controllers\\AuthController/register'
        ])) {
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function triggerNotFound() {
        // You can create a dedicated 404 controller/method
        http_response_code(404);
        $this->controller = new AuthController();
        $this->method = 'login'; // Or a 'notFound' method
        // To prevent further errors, we should ensure this fallback path is always valid.
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
