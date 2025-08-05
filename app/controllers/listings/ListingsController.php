<?php

namespace App\Controllers\Listings;

use App\Core\Auth;
use App\Core\Controller;
use App\Helpers\ExportHelper;

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
     * Display the main tickets listing page or export data.
     */
    public function tickets()
    {
        $this->authorize('listings/tickets');

        $filters = $_GET;
        $tickets = $this->listingModel->getFilteredTickets($filters);

        // Check for export request
        if (isset($filters['export'])) {
            $this->exportTickets($tickets, $filters['export']);
            return; // Stop further execution
        }

        $data = [
            'page_main_title' => 'All Tickets',
            'tickets' => $tickets, // Pass tickets to the view
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
            'platforms' => $this->platformModel->getAll(),
            'users' => $this->userModel->getAllUsers(),
            'filters' => $filters // Pass current filters back to the view
        ];

        $this->view('listings/tickets', $data);
    }

    /**
     * Handle the export of ticket data.
     *
     * @param array $tickets The ticket data to export.
     * @param string $format The export format ('excel' or 'json').
     */
    private function exportTickets(array $tickets, string $format)
    {
        $filename = 'tickets_export';
        $exportData = [];

        if ($format === 'excel') {
            // Prepare data for Excel export
            $exportData['headers'] = [
                'Ticket #', 'Creator', 'Platform', 'Phone', 'Classification', 'Created At', 'VIP'
            ];
            $exportData['rows'] = array_map(function ($ticket) {
                $classification = implode(' > ', array_filter([
                    $ticket['category_name'] ?? '',
                    $ticket['subcategory_name'] ?? '',
                    $ticket['code_name'] ?? ''
                ]));
                return [
                    $ticket['ticket_number'],
                    $ticket['created_by_username'],
                    $ticket['platform_name'],
                    $ticket['phone'] ?? '',
                    $classification,
                    date('Y-m-d H:i', strtotime($ticket['created_at'])),
                    $ticket['is_vip'] == 1 ? 'Yes' : 'No'
                ];
            }, $tickets);

            ExportHelper::exportToExcel($exportData, $filename);
        } elseif ($format === 'json') {
            // For JSON, we can export the raw ticket data
            ExportHelper::exportToJson($tickets, $filename);
        }
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

        // Fetch filtered calls
        $callsData = $this->listingModel->getFilteredCalls($_GET);

        $data = [
            'page_main_title' => 'All Calls',
            'calls' => $callsData['data'] ?? [],
            'pagination' => [
                'total' => $callsData['total'] ?? 0,
                'total_pages' => $callsData['total_pages'] ?? 1,
                'current_page' => $callsData['current_page'] ?? 1,
                'limit' => $callsData['limit'] ?? 25,
            ],
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
            'users' => $this->userModel->getAllUsers(),
            'filters' => $_GET
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
        
        // Fetch filtered drivers
        $driversData = $driverModel->getFilteredDrivers($_GET);

        $data = [
            'page_main_title' => 'All Drivers',
            'stats' => $driverModel->getDriverStats(),
            'car_types' => $carTypeModel->getAll(),
            'drivers' => $driversData['data'] ?? [],
            'pagination' => [
                'total' => $driversData['total'] ?? 0,
                'total_pages' => $driversData['total_pages'] ?? 1,
                'current_page' => $driversData['current_page'] ?? 1,
                'limit' => $driversData['limit'] ?? 15,
            ],
            'filters' => $_GET
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
