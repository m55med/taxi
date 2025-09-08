<?php

if (!function_exists('get_help_video_url')) {
    /**
     * Determines the YouTube video ID for the current page by checking the JSON mapping.
     *
     * @return string|null The YouTube video ID or null if no video is mapped.
     */
    function get_help_video_url()
    {
        static $mappings = null;

        if ($mappings === null) {
            $jsonFilePath = APPROOT . '/cache/help_videos.json';
            if (file_exists($jsonFilePath)) {
                $json = file_get_contents($jsonFilePath);
                $mappings = json_decode($json, true) ?? [];
            } else {
                $mappings = [];
            }
        }
        
        $requestUriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

        $cleanUri = $requestUriPath;
        if (!empty($basePath) && strpos($requestUriPath, $basePath) === 0) {
            $cleanUri = trim(substr($requestUriPath, strlen($basePath)), '/');
        }

        // Direct match
        if (isset($mappings[$cleanUri])) {
            return $mappings[$cleanUri];
        }

        // Dynamic route matching (e.g., admin/users/edit/123)
        foreach ($mappings as $route => $videoId) {
            // Convert route pattern to a regex pattern
            if (strpos($route, '{') !== false) {
                $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
                if (preg_match("#^$pattern$#", $cleanUri)) {
                    return $videoId;
                }
            }
        }

        return null; // No video for this page
    }
}
