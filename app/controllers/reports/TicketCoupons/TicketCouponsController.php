<?php

namespace App\Controllers\Reports\TicketCoupons;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class TicketCouponsController extends Controller
{
    private $couponModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager']);
        $this->couponModel = $this->model('Reports/TicketCoupons/TicketCouponsReport');
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
        
        $total_records = $this->couponModel->getTicketCouponsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $ticket_coupons = $this->couponModel->getTicketCoupons($filters, $records_per_page, $offset);
        
        $data = [
            'title' => 'Ticket-Coupon Usage Report',
            'ticket_coupons' => $ticket_coupons,
            'filters' => $filters,
            'filter_options' => $this->couponModel->getFilterOptions(),
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/TicketCoupons/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
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
        $ticket_coupons = $this->couponModel->getTicketCoupons($filters, 10000, 0);

        $export_data = [
            'headers' => ['Ticket #', 'Coupon Code', 'Created By', 'Date'],
            'rows' => array_map(fn($item) => [
                $item['ticket_number'], $item['coupon_code'], $item['created_by_user'], $item['created_at']
            ], $ticket_coupons)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'ticket_coupons_report');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($ticket_coupons, 'ticket_coupons_report');
        }
    }
} 