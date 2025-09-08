<?php

namespace App\Controllers\Documentation;

use App\Core\Controller;

class DocumentationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Show the documentation page.
     */
    public function index()
    {
        $routes = $this->parseRoutes();
        $this->view('documentation/index', [
            'title' => 'System Documentation',
            'routes' => $routes
        ]);
    }

    /**
     * Parses the routes file to extract all defined routes.
     * @return array
     */
    private function parseRoutes(): array
    {
        $routesFilePath = APPROOT . '/routes/web.php';
        if (!file_exists($routesFilePath)) {
            return [];
        }

        $contents = file_get_contents($routesFilePath);
        $pattern = '/\$router->(get|post|put|delete|patch|any)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\);/i';
        
        preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);
        
        $parsedRoutes = [];
        
        foreach ($matches as $match) {
            $isDynamic = strpos($match[2], '{') !== false;
            $parsedRoutes[] = [
                'method' => strtoupper($match[1]),
                'uri' => $match[2],
                'action' => $match[3],
                'type' => $isDynamic ? 'Dynamic' : 'Static'
            ];
        }

        // Handle mapReportRoutes
        $reportRoutesPattern = '/mapReportRoutes\(\$router,\s*[\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]\)/';
        preg_match_all($reportRoutesPattern, $contents, $reportMatches, PREG_SET_ORDER);
        
        foreach ($reportMatches as $match) {
            $uri = $match[1];
            $controller = $match[2];
            $parsedRoutes[] = [ 'method' => 'GET', 'uri' => 'reports/' . $uri, 'action' => $controller . '@index', 'type' => 'Static'];
            $parsedRoutes[] = [ 'method' => 'GET', 'uri' => 'reports/' . $uri . '/data', 'action' => $controller . '@data', 'type' => 'Static'];
        }

        return $parsedRoutes; 
    }
} 