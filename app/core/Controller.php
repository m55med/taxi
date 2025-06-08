<?php
require_once __DIR__ . '/Database.php';


class Controller
{
    public function __construct()
    {
        // Constructor is empty but available for inheritance
    }

    protected function model($model)
    {
        // Construct the full path to the model file
        $modelPath = APPROOT . '/models/' . $model . '.php';

        if (!file_exists($modelPath)) {
            die('Model file not found at path: ' . $modelPath);
        }

        require_once $modelPath;

        // Extract class name from the model path, handling subdirectories
        $className = basename(str_replace('\\', '/', $model));

        if (!class_exists($className)) {
            die('Model class "' . $className . '" does not exist in file: ' . $modelPath);
        }

        return new $className();
    }

    protected function view($view, $data = [])
    {
        $viewPath = APPROOT . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            die('View does not exist');
        }
    }
}
