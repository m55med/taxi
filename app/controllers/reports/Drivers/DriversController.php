<?php

namespace App\Controllers\Reports\Drivers;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class DriversController extends Controller
{
    private $driversReportModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader']);
        $this->driversReportModel = $this->model('reports/Drivers/DriversReport');
    }

    public function index()
    {
        $filters = $this->get_filters();

        if (isset($_GET['export'])) {
            $this->export($filters, $_GET['export']);
        }

        // Pagination
        $records_per_page = 25;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $records_per_page;

        $totalRecords = $this->driversReportModel->countDrivers($filters);
        $totalPages = ceil($totalRecords / $records_per_page);
        
        $drivers = $this->driversReportModel->getPaginatedDrivers($records_per_page, $offset, $filters);
        $stats = $this->driversReportModel->getDriversStats($filters);
        $filter_options = $this->driversReportModel->getFilterOptions();

        $data = [
            'title' => 'Drivers Report',
            'drivers' => $drivers,
            'stats' => $stats,
            'filters' => $filters,
            'filter_options' => $filter_options,
            'current_page' => $current_page,
            'total_pages' => $totalPages,
        ];
        
        $this->view('reports/Drivers/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'main_system_status' => $_GET['main_system_status'] ?? '',
            'data_source' => $_GET['data_source'] ?? '',
            'has_missing_documents' => $_GET['has_missing_documents'] ?? '',
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
        $drivers = $this->driversReportModel->getPaginatedDrivers(10000, 0, $filters);

        $export_data = [
            'headers' => ['ID', 'Name', 'Phone', 'System Status', 'App Status', 'Data Source', 'Country', 'Added By', 'Registered At'],
            'rows' => array_map(function($driver) {
                return [
                    $driver['id'],
                    $driver['name'],
                    $driver['phone'],
                    $driver['main_system_status'],
                    $driver['app_status'],
                    $driver['data_source'],
                    $driver['country_name'] ?? 'N/A',
                    $driver['added_by_name'] ?? 'System',
                    $driver['created_at']
                ];
            }, $drivers)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'drivers_report');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($drivers, 'drivers_report');
        }
    }
}