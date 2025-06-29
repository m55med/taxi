<?php

// routes/api.php

use App\Controllers\Api\ApiController;

// This function will be called from the main router
function handle_api_routes($url) {
    if (empty($url[0]) || $url[0] !== 'api') {
        return false; // Not an API route
    }

    // Shift the 'api' part off the URL array
    array_shift($url);

    $controller = new ApiController();

    // Route: /api/agents
    if (!empty($url[0]) && $url[0] === 'agents' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getAgents();
        return true; // Route was handled
    }

    // Add other API routes here in the future
    // if (!empty($url[0]) && $url[0] === 'another_endpoint') { ... }

    // If no specific API route matched
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    return true; // Handled as a 404
} 