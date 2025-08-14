<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class HelpController extends Controller
{
    private $jsonFilePath;

    public function __construct()
    {
        $this->authorize(['admin', 'developer']);
        $this->jsonFilePath = APPROOT . '/cache/help_videos.json';
    }

    public function index()
    {
        echo "<pre>Reached HelpController@index ✅</pre>";
        die;
        $routes = $this->getIndexRoutes();
        $mappings = $this->loadMappings();

        $this->view('admin/help/index', [
            'page_main_title' => 'Manage Help Videos',
            'routes' => $routes,
            'mappings' => $mappings
        ]);
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/help-videos');
        }

        $mappings = $_POST['mappings'] ?? [];
        $sanitizedMappings = [];

        foreach ($mappings as $route => $videoId) {
            $videoId = trim($videoId);
            if (!empty($videoId) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                $sanitizedMappings[urldecode($route)] = $videoId;
            }
        }

        // Ensure the cache directory exists
        if (!is_dir(dirname($this->jsonFilePath))) {
            mkdir(dirname($this->jsonFilePath), 0755, true);
        }

        if (file_put_contents($this->jsonFilePath, json_encode($sanitizedMappings, JSON_PRETTY_PRINT))) {
            flash('help_videos_success', 'Help video links have been saved successfully.');
        } else {
            flash('help_videos_error', 'Failed to save help video links. Please check file permissions on the app/cache directory.', 'error');
        }

        redirect('admin/help-videos');
    }

    private function getIndexRoutes()
    {
        $routesFilePath = APPROOT . '/routes/web.php';
        if (!file_exists($routesFilePath)) {
            return [];
        }

        $contents = file_get_contents($routesFilePath);
        preg_match_all('/\$router->get\s*\(\s*[\'"]([^\'"{}\s]+)[\'"]\s*,\s*[\'"]([a-zA-Z0-9\/_]+Controller@index)[\'"]\s*\)/', $contents, $matches);
        
        $indexRoutes = [];
        if (!empty($matches[1])) {
            $indexRoutes = array_filter($matches[1], function($route) {
                return $route !== ''; // Exclude the root route
            });
        }
        
        sort($indexRoutes);
        return array_unique($indexRoutes);
    }

    private function loadMappings()
    {
        if (!file_exists($this->jsonFilePath)) {
            return [];
        }
        $json = file_get_contents($this->jsonFilePath);
        return json_decode($json, true) ?? [];
    }
}
