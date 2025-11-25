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

    // Route: /api/contracts/generate
    if (!empty($url[0]) && $url[0] === 'contracts' && !empty($url[1]) && $url[1] === 'generate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->generateContract();
        return true;
    }

    // Route: /api/downloadContract?file=...&expires=...&signature=...
    if (!empty($url[0]) && $url[0] === 'downloadContract' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->downloadContract();
        return true;
    }

    // Route: /api/establishments/create
    if (!empty($url[0]) && $url[0] === 'establishments' && !empty($url[1]) && $url[1] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once APPROOT . '/Controllers/Establishments/EstablishmentsController.php';
        $establishmentsController = new \App\Controllers\Establishments\EstablishmentsController();
        $establishmentsController->createEstablishment();
        return true;
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

    // Route: /api/extension/me
    if (!empty($url[0]) && $url[0] === 'extension' && !empty($url[1]) && $url[1] === 'me' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getExtensionUser();
        return true; // Route was handled
    }

    // Route: /api/extension/tickets/create
    if (!empty($url[0]) && $url[0] === 'extension' && !empty($url[1]) && $url[1] === 'tickets' && !empty($url[2]) && $url[2] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->createTicketFromExtension();
        return true; // Route was handled
    }

    // Route: /api/extension/tickets/{ticketNumber}/params
    if (!empty($url[0]) && $url[0] === 'extension' && !empty($url[1]) && $url[1] === 'tickets' && isset($url[2]) && is_numeric($url[2]) && !empty($url[3]) && $url[3] === 'params' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticketNumber = $url[2];
        $controller->getTicketParams($ticketNumber);
        return true; // Route was handled
    }

    // Route: /api/extension/options
    if (!empty($url[0]) && $url[0] === 'extension' && !empty($url[1]) && $url[1] === 'options' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getExtensionOptions();
        return true; // Route was handled
    }

    // Add other API routes here in the future
    // if (!empty($url[0]) && $url[0] === 'another_endpoint') { ... }

    // If no specific API route matched
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    return true; // Handled as a 404
} 