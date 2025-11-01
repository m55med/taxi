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
    private $teamModel;

    /**
     * Convert UTC datetime to Cairo timezone safely
     */
/**
 * Convert UTC datetime to Cairo timezone
 */
    private function convertToCairoTime($utcDateTime)
    {
        if (empty($utcDateTime)) return $utcDateTime;

        try {
            $utc = new \DateTimeImmutable($utcDateTime, new \DateTimeZone('UTC'));
            $cairo = new \DateTimeZone('Africa/Cairo');
            $cairoTime = $utc->setTimezone($cairo);

            // 12-hour format مع AM/PM للعرض
            return $cairoTime->format('Y-m-d h:i:s A');

        } catch (\Exception $e) {
            error_log('Error converting datetime to Cairo timezone: ' . $e->getMessage());
            return $utcDateTime;
        }
    }

/**
 * Convert Cairo datetime to UTC for filtering
 */
private function convertCairoToUTC($cairoDateTime)
{
    if (empty($cairoDateTime)) return $cairoDateTime;

    try {
        $cairo = new \DateTimeImmutable($cairoDateTime, new \DateTimeZone('Africa/Cairo'));
        $utc = new \DateTimeZone('UTC');
        $utcTime = $cairo->setTimezone($utc);

        return $utcTime->format('Y-m-d H:i:s');

    } catch (\Exception $e) {
        error_log('Error converting Cairo datetime to UTC: ' . $e->getMessage());
        return $cairoDateTime;
    }
}


    

    /**
     * Convert datetime fields in array from UTC to Cairo timezone
     */
    private function convertArrayTimesToCairo(&$tickets, $fields = ['ticket_created_at','ticket_updated_at','detail_created_at','detail_updated_at'])
    {
        foreach ($tickets as &$ticket) {
            foreach ($fields as $field) {
                if (!empty($ticket[$field])) {
                    $ticket[$field] = $this->convertToCairoTime($ticket[$field]);
                }
            }
        }
    }
    

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();

        // These models will be created/used in the next steps
        $this->listingModel = $this->model('Listings/ListingModel');
        $this->ticketCategoryModel = $this->model('Tickets/Category');
        $this->userModel = $this->model('User/User');
        $this->platformModel = $this->model('Admin/Platform');
        $this->teamModel = $this->model('Admin/Team');
    }

    /**
     * Display the main tickets listing page or export data.
     */
    public function tickets()
{
    $this->authorize('listings/tickets');

    $filters = $_GET;

    // Debug logging: الفلاتر الأصلية
    error_log('ListingsController::tickets - Filters received: ' . json_encode($filters));

    // إذا المستخدم agent، نحدّد created_by تلقائياً
    if (Auth::hasRole('agent')) {
        $filters['created_by'] = Auth::getUserId();
        error_log('ListingsController::tickets - User is agent, forced created_by: ' . $filters['created_by']);
    }

    // ======= تحويل التواريخ من Cairo إلى UTC قبل الاستعلام =======
    // الحل الأمثل: احفظ التاريخ الأصلي للمقارنة في قاعدة البيانات
    if (!empty($filters['start_date'])) {
        // احفظ التاريخ الأصلي الذي أرسله المستخدم
        $filters['original_start_date'] = $filters['start_date'];

        $startInput = trim($filters['start_date']);
        $hasTime = (bool)preg_match('/\d{1,2}:\d{2}(:\d{2})?/', $startInput);
        $startString = $hasTime ? $startInput : ($startInput . ' 00:00:00');

        // للتحويل إلى UTC
        $startCairo = new \DateTimeImmutable($startString, new \DateTimeZone('Africa/Cairo'));
        $filters['start_date'] = $startCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        // إذا لم يتم تحديد end_date، اجعلها تغطي يوم كامل (حسب تاريخ البداية)
        if (empty($filters['end_date'])) {
            $originalDate = $startCairo->setTimezone(new \DateTimeZone('Africa/Cairo'))->format('Y-m-d');
            $endCairo = new \DateTimeImmutable($originalDate . ' 23:59:59', new \DateTimeZone('Africa/Cairo'));
            $filters['end_date'] = $endCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        }
    }

    // إذا تم تحديد start_date و end_date معًا، حوّل end_date كذلك إلى UTC
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $filters['original_end_date'] = $filters['end_date'];
        $endInputBoth = trim($filters['end_date']);
        $endHasTimeBoth = (bool)preg_match('/\d{1,2}:\d{2}(:\d{2})?/', $endInputBoth);
        $endStringBoth = $endHasTimeBoth ? $endInputBoth : ($endInputBoth . ' 23:59:59');
        $endCairoBoth = new \DateTimeImmutable($endStringBoth, new \DateTimeZone('Africa/Cairo'));
        $filters['end_date'] = $endCairoBoth->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    if (!empty($filters['end_date']) && empty($filters['start_date'])) {
        $filters['original_end_date'] = $filters['end_date'];
        $endInput = trim($filters['end_date']);
        $endHasTime = (bool)preg_match('/\d{1,2}:\d{2}(:\d{2})?/', $endInput);

        // اضبط نهاية اليوم إذا لم يكن هناك وقت
        $endString = $endHasTime ? $endInput : ($endInput . ' 23:59:59');
        $endCairo = new \DateTimeImmutable($endString, new \DateTimeZone('Africa/Cairo'));
        $filters['end_date'] = $endCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        // اجعل البداية بداية نفس اليوم في حال لم تُحدد
        $startDateOnly = $endCairo->setTimezone(new \DateTimeZone('Africa/Cairo'))->format('Y-m-d');
        $startCairo = new \DateTimeImmutable($startDateOnly . ' 00:00:00', new \DateTimeZone('Africa/Cairo'));
        $filters['start_date'] = $startCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }


    // Debug logging: الفلاتر بعد التحويل
    error_log('ListingsController::tickets - Filters after Cairo->UTC conversion: ' . json_encode($filters));
    // ============================================================

    // طلب تصدير
    if (isset($filters['export'])) {
        $tickets = $this->listingModel->getFilteredTickets($filters, false); // كل الداتا للتصدير
        $this->exportTickets($tickets['data'], $filters['export']);
        return;
    }

    // جلب البيانات مع pagination
    $ticketsData = $this->listingModel->getFilteredTickets($filters, true);

    // تحويل كل التواريخ من UTC للقاهرة للعرض
    $this->convertArrayTimesToCairo($ticketsData['data'], [
        'ticket_created_at',
        'ticket_updated_at',
        'detail_created_at',
        'detail_updated_at'
    ]);

    $stats = $this->listingModel->getTicketStats($filters);

    // Debug logging: عرض البيانات المسترجعة قبل عرض الفيو
    error_log('ListingsController::tickets - Tickets data: ' . json_encode([
        'total' => $ticketsData['total'] ?? 0,
        'count' => count($ticketsData['data'] ?? []),
        'first_ticket' => !empty($ticketsData['data']) ? $ticketsData['data'][0]['ticket_number'] : 'N/A'
    ]));

    $data = [
        'page_main_title' => 'All Tickets',
        'tickets' => $ticketsData['data'] ?? [],
        'pagination' => [
            'total' => $ticketsData['total'] ?? 0,
            'total_pages' => $ticketsData['total_pages'] ?? 1,
            'current_page' => $ticketsData['current_page'] ?? 1,
            'limit' => $ticketsData['limit'] ?? 25,
        ],
        'stats' => $stats,
        'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
        'platforms' => $this->platformModel->getAll(),
        'users' => $this->userModel->getAllUsers(),
        'teams' => $this->teamModel->getAll(),
        'listingModel' => $this->listingModel,
        'filters' => $filters
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

        // Keep original datetime format without timezone conversion

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
                    $ticket['created_at'], // بعد convertArrayTimesToCairo,
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
     * API endpoint to fetch filtered tickets with pagination.
     */
    public function get_tickets_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/tickets');

        $filters = $_GET;
        
        // If the user is an agent, force filter by their user ID
        if (Auth::hasRole('agent')) {
            $filters['created_by'] = Auth::getUserId();
        }

        echo json_encode($this->listingModel->getFilteredTickets($filters, true));
    }

    /**
     * API endpoint for search suggestions.
     */
    public function search_suggestions_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/tickets');

        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'ticket'; // ticket, phone, user

        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        $suggestions = $this->listingModel->getSearchSuggestions($query, $type);
        echo json_encode($suggestions);
    }

    /**
     * API endpoint for user search.
     */
    public function search_users_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/tickets');

        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        $users = $this->userModel->searchUsers($query);
        echo json_encode($users);
    }

    /**
     * Display the main calls listing page.
     */
    public function calls()
    {
        $this->authorize('listings/calls');

        $filters = $_GET;

        // If the user is an agent, force filter by their user ID
        if (Auth::hasRole('agent')) {
            $filters['user_id'] = Auth::getUserId();
        }

        // Check for export request
        if (isset($filters['export'])) {
            $callsData = $this->listingModel->getFilteredCalls($filters, false); // Fetch all matching data for export
            $this->exportCalls($callsData['data'], $filters['export']);
            return; // Stop further execution
        }
        
        // Fetch paginated calls for display
        $callsData = $this->listingModel->getFilteredCalls($filters);

        // Convert datetime fields to Cairo timezone
        $this->convertArrayTimesToCairo($callsData['data']);

        $data = [
            'page_main_title' => 'All Calls',
            'stats' => $this->listingModel->getCallStats($filters),
            'calls' => $callsData['data'] ?? [],
            'pagination' => [
                'total' => $callsData['total'] ?? 0,
                'total_pages' => $callsData['total_pages'] ?? 1,
                'current_page' => $callsData['current_page'] ?? 1,
                'limit' => $callsData['limit'] ?? 25,
            ],
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
            'users' => $this->userModel->getAllUsers(),
            'filters' => $filters
        ];

        $this->view('listings/calls', $data);
    }

    private function exportCalls(array $calls, string $format)
    {
        // Convert datetime fields to Cairo timezone before export
        $this->convertArrayTimesToCairo($calls);

        $filename = 'calls_export';
        $headers = ['Type', 'Contact Name', 'Contact Phone', 'User', 'Status', 'Details', 'Call Time'];
        $rows = array_map(function ($call) {
            return [
                $call['call_type'],
                $call['contact_name'],
                $call['contact_phone'],
                $call['user_name'],
                $call['status'],
                '', // Details placeholder
                $call['call_time'] // بعد convertArrayTimesToCairo,
            ];
        }, $calls);

        $data = ['headers' => $headers, 'rows' => $rows];

        switch ($format) {
            case 'excel':
                ExportHelper::exportToExcel($data, $filename);
                break;
            case 'csv':
                ExportHelper::exportToCsv($data, $filename);
                break;
            case 'json':
                ExportHelper::exportToJson($calls, $filename); // Export raw data
                break;
            case 'pdf':
                ExportHelper::exportToPdf($data, $filename);
                break;
            case 'txt':
                ExportHelper::exportToTxt($data, $filename);
                break;
        }
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
        
        $filters = $_GET;
        $driversData = $driverModel->getFilteredDrivers($filters);

        // Convert datetime fields to Cairo timezone
        if (isset($driversData['data'])) {
            $this->convertArrayTimesToCairo($driversData['data']);
        } else {
            $this->convertArrayTimesToCairo($driversData);
        }
    
        if (isset($filters['export'])) {
            $this->exportDrivers($driversData['data'], $filters['export']);
            return;
        }

        // build pagination data
        $pagination = [
            'total' => $driversData['total'] ?? 0,
            'total_pages' => $driversData['total_pages'] ?? 1,
            'current_page' => $driversData['current_page'] ?? 1,
            'limit' => $driversData['limit'] ?? 25,
        ];
    
        $data = [
            'page_main_title' => 'All Drivers',
            'stats' => $driverModel->getDriverStats($filters),
            'car_types' => $carTypeModel->getAll() ?? [],
            'drivers' => $driversData,
            'filters' => $filters,
            'pagination' => $pagination, 
        ];
    
        $this->view('listings/drivers', $data);
    }

    private function exportDrivers(array $drivers, string $format)
    {
        // Convert datetime fields to Cairo timezone before export
        $this->convertArrayTimesToCairo($drivers);

        $filename = 'drivers_export';
        $headers = ['ID', 'Name', 'Phone', 'Email', 'Main Status', 'App Status', 'Car Type', 'Call Count', 'Missing Docs'];
        $rows = array_map(function ($driver) {
            return [
                $driver['id'],
                $driver['name'],
                $driver['phone'],
                $driver['email'] ?? 'N/A',
                $driver['main_system_status'],
                $driver['app_status'],
                $driver['car_type_name'] ?? 'N/A',
                $driver['call_count'],
                $driver['missing_documents_count']
            ];
        }, $drivers);

        $data = ['headers' => $headers, 'rows' => $rows];

        switch ($format) {
            case 'excel':
                ExportHelper::exportToExcel($data, $filename);
                break;
            case 'csv':
                ExportHelper::exportToCsv($data, $filename);
                break;
            case 'json':
                ExportHelper::exportToJson($drivers, $filename); // Export raw data for JSON
                break;

        }
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

    /**
     * API endpoint for deleting ticket details.
     */
    public function delete_ticket_detail()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/tickets');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
            return;
        }

        $postData = json_decode(file_get_contents('php://input'), true);
        $ticketDetailId = $postData['ticket_detail_id'] ?? null;

        if (!$ticketDetailId || !is_numeric($ticketDetailId)) {
            echo json_encode(['success' => false, 'message' => 'معرف تفصيلة التذكرة مطلوب']);
            return;
        }

        try {
            $result = $this->listingModel->deleteTicketDetail($ticketDetailId, Auth::getUserId());
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log('ListingsController::delete_ticket_detail Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء معالجة الطلب']);
        }
    }
}