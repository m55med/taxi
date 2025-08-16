<?php

// routes/api.php

use App\Controllers\Api\ApiController;
use App\Controllers\Driver\DriverController;

// This function will be called from the main router
function handle_api_routes($url) {
    if (empty($url[0]) || $url[0] !== 'api') {
        return false; // Not an API route
    }

    // Shift the 'api' part off the URL array
    array_shift($url);

    // Manually load controllers as needed for the routes below
    require_once APPROOT . '/Controllers/Api/ApiController.php';
    require_once APPROOT . '/Controllers/driver/DriverController.php';
    $controller = new ApiController();
    $driverController = new DriverController();

    // Route: /api/heartbeat
    if (!empty($url[0]) && $url[0] === 'heartbeat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->heartbeat();
        return true;
    }

    // Route: /api/discussions
    if (!empty($url[0]) && $url[0] === 'discussions' && empty($url[1]) && $_SERVER['REQUEST_METHOD'] === 'GET') {
        require_once APPROOT . '/controllers/discussions/DiscussionsController.php';
        $discussionsController = new \App\Controllers\Discussions\DiscussionsController();
        $discussionsController->getDiscussionsApi();
        return true;
    }

    // Route: /api/agents
    if (!empty($url[0]) && $url[0] === 'agents' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getAgents();
        return true; // Route was handled
    }

    // Route: /api/drivers/search
    if (!empty($url[0]) && $url[0] === 'drivers' && !empty($url[1]) && $url[1] === 'search' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $driverController->search();
        return true; // Route was handled
    }

    // Route: /api/restaurants/create
    if (!empty($url[0]) && $url[0] === 'restaurants' && !empty($url[1]) && $url[1] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->createRestaurant();
        return true; // Route was handled
    }

    // Route: /api/restaurants/upload-pdf/{id}
    if (!empty($url[0]) && $url[0] === 'restaurants' && !empty($url[1]) && $url[1] === 'upload-pdf' && !empty($url[2]) && is_numeric($url[2]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$url[2];
        $controller->updateRestaurantPdf($id);
        return true; // Route was handled
    }

    // Route: /api/restaurants/referred-by/{marketerId}
    if (!empty($url[0]) && $url[0] === 'restaurants' && !empty($url[1]) && $url[1] === 'referred-by' && !empty($url[2]) && is_numeric($url[2]) && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $marketerId = (int)$url[2];
        $controller->getReferredRestaurants($marketerId);
        return true; // Route was handled
    }

    // Route: /api/discussions/{id}/replies
    if (!empty($url[0]) && $url[0] === 'discussions' && isset($url[1]) && is_numeric($url[1]) && isset($url[2]) && $url[2] === 'replies' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Manually include and instantiate the DiscussionsController just for this route
        require_once APPROOT . '/controllers/discussions/DiscussionsController.php';
        $discussionsController = new \App\Controllers\Discussions\DiscussionsController();
        
        $discussionId = (int)$url[1];
        $discussionsController->addReplyApi($discussionId);
        return true; // Route was handled
    }

    // Route: /api/discussions/{id}/mark-as-read
    if (!empty($url[0]) && $url[0] === 'discussions' && isset($url[1]) && is_numeric($url[1]) && isset($url[2]) && $url[2] === 'mark-as-read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Manually include and instantiate the DiscussionsController if not already done
        if (!isset($discussionsController)) {
            require_once APPROOT . '/controllers/discussions/DiscussionsController.php';
            $discussionsController = new \App\Controllers\Discussions\DiscussionsController();
        }
        
        $discussionId = (int)$url[1];
        $discussionsController->markAsReadApi($discussionId);
        return true; // Route was handled
    }

    // Add other API routes here in the future
    // if (!empty($url[0]) && $url[0] === 'another_endpoint') { ... }

    // If no specific API route matched
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    return true; // Handled as a 404
} 