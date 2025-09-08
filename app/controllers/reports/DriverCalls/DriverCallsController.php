<?php

namespace App\Controllers\Reports\DriverCalls;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class DriverCallsController extends Controller
{
    private $callsReportModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader']);
        $this->callsReportModel = $this->model('Reports/DriverCalls/DriverCallsReport');
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
        
        $total_records = $this->callsReportModel->getCallsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $calls = $this->callsReportModel->getCalls($filters, $records_per_page, $offset);
        $filterOptions = $this->callsReportModel->getFilterOptions();
        
        $data = [
            'title' => 'Driver Calls Report',
            'calls' => $calls,
            'filters' => $filters,
            'filter_options' => $filterOptions,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/DriverCalls/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'driver_id' => $_GET['driver_id'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'team_id' => $_GET['team_id'] ?? '',
            'call_status' => $_GET['call_status'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),
            'period' => $_GET['period'] ?? 'custom',
        ];

        if (!empty($_GET['period']) && $_GET['period'] !== 'custom') {
            $period = $_GET['period'];
            $today = new DateTime();
            switch ($period) {
                case 'today':
                    $filters['date_from'] = $today->format('Y-m-d');
                    $filters['date_to'] = $today->format('Y-m-d');
                    break;
                case '7days':
                    $filters['date_from'] = $today->modify('-6 days')->format('Y-m-d');
                    $filters['date_to'] = (new DateTime())->format('Y-m-d');
                    break;
                case '30days':
                    $filters['date_from'] = $today->modify('-29 days')->format('Y-m-d');
                    $filters['date_to'] = (new DateTime())->format('Y-m-d');
                    break;
                case 'all':
                    $filters['date_from'] = null;
                    $filters['date_to'] = null;
                    break;
            }
        }
        return $filters;
    }

    private function export($filters, $format)
    {
        $calls = $this->callsReportModel->getCalls($filters, 10000, 0);

        $export_data = [
            'headers' => ['ID', 'Driver', 'Called By', 'Status', 'Notes', 'Date'],
            'rows' => array_map(fn($item) => [
                $item['id'], $item['driver_name'], $item['user_name'], 
                $item['call_status'], $item['notes'], $item['created_at']
            ], $calls)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'driver_calls_report');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($calls, 'driver_calls_report');
        }
    }
} 