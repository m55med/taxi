<?php
class App
{
    protected $controller = 'AuthController';  // الكنترولر الافتراضي
    protected $method = 'login';                // الميثود الافتراضية
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();
        $url = is_array($url) ? $url : [];

        // تعيين المتحكم
        if (!empty($url[0])) {
            if ($url[0] === 'reports' && isset($url[1])) {
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
                        require_once $controllerFile;
                        $this->controller = new $controllerName();
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
            } else {
                // التعامل مع باقي المسارات
                $controllerName = ucfirst($url[0]) . 'Controller';
                $controllerFile = '../app/controllers/' . $controllerName . '.php';
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    $this->controller = new $controllerName();
                    $this->method = isset($url[1]) && method_exists($this->controller, $url[1]) ? $url[1] : 'index';
                    unset($url[0]);
                    if (isset($url[1])) unset($url[1]);
                } else {
                    // إذا لم يوجد الملف، استخدم الكنترولر الافتراضي
                    require_once '../app/controllers/AuthController.php';
                    $this->controller = new AuthController();
                    $this->method = 'login';
                }
            }
        } else {
            // المسار الافتراضي
            require_once '../app/controllers/AuthController.php';
            $this->controller = new AuthController();
        }

        $this->params = $url ? array_values($url) : [];

        // تحديد المسارات العامة (غير المحمية)
        $publicRoutes = [
            'AuthController/login',
            'AuthController/register'
        ];

        // تحديد المسارات المسموح بها للمستخدمين المسجلين
        $authenticatedRoutes = [
            'ReviewController/index',
            'ReviewController/getDriverDetails',
            'ReviewController/updateDriver',
            'ReviewController/transferDriver'
        ];

        // تحديد المسارات المقيدة للأدمن فقط
        $adminOnlyRoutes = [
            'UploadController/index',
            'UploadController/upload',
            'DashboardController/users'
        ];

        $currentRoute = get_class($this->controller) . '/' . $this->method;

        // التحقق من تسجيل الدخول للمسارات المحمية
        if (!isset($_SESSION['user_id']) && !in_array($currentRoute, $publicRoutes)) {
            $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        // التحقق من صلاحيات الأدمن
        if (in_array($currentRoute, $adminOnlyRoutes) && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // إذا كان المستخدم مسجل دخوله ويحاول الوصول إلى صفحات تسجيل الدخول/التسجيل
        if (isset($_SESSION['user_id']) && in_array($currentRoute, $publicRoutes)) {
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // التحقق من أن المستخدم مسجل دخوله للمسارات المحمية للمستخدمين المسجلين
        if (in_array($currentRoute, $authenticatedRoutes) && !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function triggerNotFound() {
        // You can create a dedicated 404 controller/method
        http_response_code(404);
        require_once '../app/controllers/AuthController.php'; // Or a dedicated error controller
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
