<?php

require_once __DIR__ . '/vendor/autoload.php';

$controller = 'App\\Controllers\\reports\\Users\\UsersController';
$model = 'App\\Models\\Reports\\Users\\UsersReport';

echo "Checking Controller: $controller\n";
if (class_exists($controller)) {
    echo "Controller EXISTS.\n";
    $reflector = new ReflectionClass($controller);
    $method = 'export';
    if ($reflector->hasMethod($method)) {
        echo "Method '$method' EXISTS.\n";
    } else {
        echo "Method '$method' DOES NOT EXIST.\n";
    }
} else {
    echo "Controller DOES NOT EXIST.\n";
}

echo "\nChecking Model: $model\n";
if (class_exists($model)) {
    echo "Model EXISTS.\n";
} else {
    echo "Model DOES NOT EXIST.\n";
}
