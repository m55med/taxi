<?php

namespace App\Controllers\Listings;

use App\Core\Auth;
use App\Core\Controller;

class ListingsController extends Controller
{
    private $listingModel;
    private $ticketCategoryModel;
    private $userModel;
    private $platformModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();

        // These models will be created/used in the next steps
        $this->listingModel = $this->model('Listings/ListingModel');
        $this->ticketCategoryModel = $this->model('Tickets/Category');
        $this->userModel = $this->model('User/User');
        $this->platformModel = $this->model('Admin/Platform');
    }

    /**
     * Display the main tickets listing page.
     */
    public function tickets()
    {
        $this->authorize('listings/tickets');

        $data = [
            'page_main_title' => 'All Tickets',
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
            'platforms' => $this->platformModel->getAll(),
            'users' => $this->userModel->getAllUsers(),
        ];

        $this->view('listings/tickets', $data);
    }

    /**
     * API endpoint to fetch filtered tickets.
     */
    public function get_tickets_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/tickets');
        echo json_encode($this->listingModel->getFilteredTickets($_GET));
    }

    /**
     * Display the main calls listing page.
     */
    public function calls()
    {
        $this->authorize('listings/calls');

        $data = [
            'page_main_title' => 'All Calls',
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
            'users' => $this->userModel->getAllUsers(),
        ];

        $this->view('listings/calls', $data);
    }

    /**
     * API endpoint to fetch filtered calls.
     */
    public function get_calls_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/calls');
        echo json_encode($this->listingModel->getFilteredCalls($_GET));
    }

    /**
     * Display the main drivers listing page.
     */
    public function drivers()
    {
        $this->authorize('listings/drivers');

        $driverModel = $this->model('Driver/Driver');
        $carTypeModel = $this->model('Admin/CarType');

        $data = [
            'page_main_title' => 'All Drivers',
            'stats' => $driverModel->getDriverStats(),
            'car_types' => $carTypeModel->getAll(),
        ];

        $this->view('listings/drivers', $data);
    }

    /**
     * API endpoint to fetch filtered drivers.
     */
    public function get_drivers_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/drivers');
        $driverModel = $this->model('Driver/Driver');
        echo json_encode($driverModel->getFilteredDrivers($_GET));
    }

    /**
     * API endpoint for bulk updating drivers.
     */
    public function bulk_update_drivers()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/drivers');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $postData = json_decode(file_get_contents('php://input'), true);
        $driverIds = $postData['driver_ids'] ?? [];
        $field = $postData['field'] ?? '';
        $value = $postData['value'] ?? '';

        if (empty($driverIds) || empty($field) || $value === '') {
            echo json_encode(['status' => false, 'message' => 'Missing required parameters.']);
            return;
        }

        $driverModel = $this->model('Driver/Driver');
        $updatedCount = $driverModel->bulkUpdate($driverIds, $field, $value);

        if ($updatedCount !== false) {
            echo json_encode(['status' => true, 'message' => "Successfully updated {$updatedCount} drivers."]);
        } else {
            echo json_encode(['status' => false, 'message' => 'An error occurred during the update.']);
        }
    }
}