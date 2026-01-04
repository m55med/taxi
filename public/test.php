<?php
// Full debug - check everything
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Full Debug - Tasks Route</h1>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;} .box{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} pre{background:#f9f9f9;padding:10px;border-left:3px solid #007bff;overflow-x:auto;} .success{color:green;} .error{color:red;}</style>";

$base = dirname(__DIR__);
define('APPROOT', $base . '/app');

// Load autoloader
require_once $base . '/vendor/autoload.php';

// Load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable($base);
    $dotenv->load();
} catch (\Exception $e) {}

// Define BASE_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$base_url = rtrim(dirname($scriptName), '/\\');
$base_url = preg_replace('/\/public$/', '', $base_url);
define('URLROOT', $protocol . $host . $base_url);
define('BASE_URL', URLROOT);

// Load helpers
require_once APPROOT . '/helpers/url_helper.php';
require_once APPROOT . '/helpers/session_helper.php';
require_once APPROOT . '/helpers/view_helper.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load App
require_once APPROOT . '/Core/App.php';

echo "<div class='box'><h2>1. Server Info</h2><pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "BASE_URL: " . BASE_URL . "\n";
echo "APPROOT: " . APPROOT . "\n";
echo "</pre></div>";

echo "<div class='box'><h2>2. URL Parsing Test</h2><pre>";
// Simulate /tasks request
$_SERVER['REQUEST_URI'] = '/tasks';
$url = App\Core\App::parseUrl();
$uri = empty($url) ? '' : implode('/', $url);
echo "REQUEST_URI set to: /tasks\n";
echo "Parsed URL Array: ";
print_r($url);
echo "Final URI: '$uri'\n";
echo "Expected: 'tasks'\n";
if ($uri === 'tasks') {
    echo "<span class='success'>✅ URL parsing works correctly!</span>\n";
} else {
    echo "<span class='error'>❌ URL parsing failed! Got '$uri' instead of 'tasks'</span>\n";
}
echo "</pre></div>";

echo "<div class='box'><h2>3. Routes Loading Test</h2><pre>";
try {
    $router = new App\Core\Router();
    $routesFile = APPROOT . '/routes/web.php';
    $router->loadRoutes($routesFile);
    
    echo "✅ Routes loaded successfully\n";
    echo "Total GET Routes: " . count($router->routes['GET']) . "\n\n";
    
    // Check for tasks route
    if (isset($router->routes['GET']['tasks'])) {
        echo "<span class='success'>✅ Route 'tasks' EXISTS!</span>\n";
        echo "Controller: " . $router->routes['GET']['tasks']['controller'] . "\n";
    } else {
        echo "<span class='error'>❌ Route 'tasks' NOT FOUND!</span>\n";
        echo "Searching for routes with 'task':\n";
        foreach ($router->routes['GET'] as $routeUri => $routeData) {
            if (stripos($routeUri, 'task') !== false) {
                echo "  - $routeUri\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo "</pre></div>";

echo "<div class='box'><h2>4. Controller Check</h2><pre>";
$controllerFile = APPROOT . '/controllers/TasksController.php';
$controllerClass = 'App\\Controllers\\TasksController';

echo "Controller File: $controllerFile\n";
echo "File Exists: " . (file_exists($controllerFile) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "\n";
echo "Controller Class: $controllerClass\n";
echo "Class Exists: " . (class_exists($controllerClass) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "\n";

if (class_exists($controllerClass)) {
    echo "Method 'index' Exists: " . (method_exists($controllerClass, 'index') ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "\n";
    
    // Try to instantiate
    try {
        $controller = new $controllerClass();
        echo "<span class='success'>✅ Controller can be instantiated!</span>\n";
    } catch (\Exception $e) {
        echo "<span class='error'>❌ Cannot instantiate controller: " . $e->getMessage() . "</span>\n";
    }
}
echo "</pre></div>";

echo "<div class='box'><h2>5. Test Full Dispatch</h2><pre>";
try {
    // Reset REQUEST_URI for test
    $_SERVER['REQUEST_URI'] = '/tasks';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    $url = App\Core\App::parseUrl();
    $uri = empty($url) ? '' : implode('/', $url);
    
    echo "Testing dispatch with URI: '$uri'\n";
    echo "Request Method: GET\n\n";
    
    if ($uri === 'tasks') {
        $router = new App\Core\Router();
        $router->loadRoutes(APPROOT . '/routes/web.php');
        
        if (isset($router->routes['GET']['tasks'])) {
            echo "<span class='success'>✅ Route found!</span>\n";
            
            // Try to get controller info
            list($controller, $method) = explode('@', $router->routes['GET']['tasks']['controller']);
            $controllerFullName = 'App\\Controllers\\' . str_replace('/', '\\', $controller);
            
            echo "Controller: $controllerFullName\n";
            echo "Method: $method\n";
            echo "Class Exists: " . (class_exists($controllerFullName) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "\n";
            
            if (class_exists($controllerFullName)) {
                echo "Method Exists: " . (method_exists($controllerFullName, $method) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "\n";
                
                echo "\n<span class='success'>✅ Everything looks good! The route should work.</span>\n";
                echo "\nIf you still get 404, the problem is likely:\n";
                echo "1. .htaccess not working (document root issue)\n";
                echo "2. Request not reaching index.php\n";
                echo "3. URL parsing issue in actual request\n";
            }
        } else {
            echo "<span class='error'>❌ Route not found in routes array!</span>\n";
        }
    } else {
        echo "<span class='error'>❌ URL parsing failed! Got '$uri' instead of 'tasks'</span>\n";
        echo "This is the main problem - fix parseUrl() first!\n";
    }
} catch (\Exception $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}
echo "</pre></div>";

echo "<div class='box'><h2>6. Test Actual Request</h2><pre>";
echo "Try accessing: <a href='" . BASE_URL . "/tasks' target='_blank'>" . BASE_URL . "/tasks</a>\n";
echo "\nIf you get 404, check:\n";
echo "1. Is the request reaching index.php? (check error logs)\n";
echo "2. What does parseUrl() return for actual /tasks request?\n";
echo "3. Is .htaccess working? (GET['url'] should be set)\n";
echo "</pre></div>";

echo "<hr><p><strong>⚠️ Delete this file after debugging!</strong></p>";
?>

