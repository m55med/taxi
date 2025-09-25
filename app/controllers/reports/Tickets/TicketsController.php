<?php



namespace App\Controllers\Reports\Tickets;



use App\Core\Controller;

use App\Helpers\ExportHelper;

use DateTime;



class TicketsController extends Controller

{

    private $ticketModel;

    private function formatToCairo($date)
    {
        if (empty($date)) {
            return '';
        }
    
        try {
            // إذا كان رقمى timestamp
            if (is_int($date) || ctype_digit((string)$date)) {
                $dt = new \DateTime('@' . $date); // ينشأ UTC من الـ timestamp
            } else {
                $date = trim($date);
    
                // نتحقق إذا السلسلة تحتوي على مؤشّر توقيت (Z أو +00:00 أو +0200 ...)
                $hasTimezone = (bool) preg_match('/[Zz]$|[+\-]\d{2}(:?\d{2})?$/', $date);
    
                if ($hasTimezone) {
                    // السلسلة نفسها تحتوي توقيت — اترك DateTime يقرأها كما هي
                    $dt = new \DateTime($date);
                } else {
                    // لا تحتوي توقيت — نفترض أنها UTC صريحة
                    $dt = new \DateTime($date, new \DateTimeZone('UTC'));
                }
            }
    
            // حول للقاهرة (ستستخدم قاعدة TZDB التاريخية/קרונولوجית الصحيحة)
            $dt->setTimezone(new \DateTimeZone('Africa/Cairo'));
    
            // الصيغة: سنة-شهر-يوم ثم 12 ساعة مع ثواني و AM/PM
            return $dt->format('Y-m-d h:i:s A');
        } catch (\Exception $e) {
            // fallback آمن: رجّع النص الأصلي بدل ما يكسر
            return (string) $date;
        }
    }
    
    

    public function __construct()

    {

        parent::__construct();

        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader']);

        $this->ticketModel = $this->model('Reports/Tickets/TicketsReport');

        if (!$this->ticketModel) {

            die("ticketModel is null – probably model() failed to load class");

        }

        

    }



    public function index()
{
    $filters = $this->get_filters();

    if (isset($_GET['export'])) {
        $this->export($filters, $_GET['export']);
    }

    $records_per_page = 25;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $records_per_page;

    $total_records = $this->ticketModel->getTicketsCount($filters);
    $total_pages = ceil($total_records / $records_per_page);

    $tickets = $this->ticketModel->getTickets($filters, $records_per_page, $offset);

    // تحويل التاريخ للقاهرة بتنسيق 12 ساعة
    $tickets = array_map(function($t) {
        $t['created_at'] = $this->formatToCairo($t['created_at']);
        return $t;
    }, $tickets);

    $data = [
        'title' => 'Detailed Tickets Report',
        'tickets' => $tickets,
        'filters' => $filters,
        'filter_options' => $this->ticketModel->getFilterOptions(),
        'current_page' => $current_page,
        'total_pages' => $total_pages,
    ];

    $this->view('reports/Tickets/index', $data);
}

    

    private function get_filters()

    {

        $filters = [

            'user_id' => $_GET['user_id'] ?? '',

            'team_id' => $_GET['team_id'] ?? '',

            'platform_id' => $_GET['platform_id'] ?? '',

            'category_id' => $_GET['category_id'] ?? '',

            'is_vip' => $_GET['is_vip'] ?? '',

            'search' => $_GET['search'] ?? '',

            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),

            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),

            'period' => $_GET['period'] ?? 'custom',

        ];



        if (!empty($_GET['period']) && $_GET['period'] !== 'custom') {

            // ... (standard date period logic)

        }

        return $filters;

    }



    private function export($filters, $format)

    {

        $tickets = $this->ticketModel->getTickets($filters, 10000, 0);



        $export_data = [

            'headers' => ['ID', 'Ticket Number', 'Created By', 'Platform', 'Category', 'Subcategory', 'Code', 'Phone', 'VIP', 'Date'],

            'rows' => array_map(fn($item) => [

                $item['id'], $item['ticket_number'], $item['created_by_user'], $item['platform_name'],

                $item['category_name'], $item['subcategory_name'], $item['code_name'],

                $item['phone'], $item['is_vip'] ? 'Yes' : 'No', $this->formatToCairo($item['created_at'])

            ], $tickets)

        ];



        if ($format === 'excel') {

            ExportHelper::exportToExcel($export_data, 'detailed_tickets_report');

        } elseif ($format === 'json') {

            ExportHelper::exportToJson($tickets, 'detailed_tickets_report');

        }

    }

} 