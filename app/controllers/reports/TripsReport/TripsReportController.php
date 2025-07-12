<?php

namespace App\Controllers\Reports\TripsReport;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class TripsReportController extends Controller
{
    private $tripsReportModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager']);
        $this->tripsReportModel = $this->model('reports/TripsReportModel');
    }

    public function index()
    {
        $filters = $this->get_filters();

        if (isset($_GET['export'])) {
            $this->export($filters, $_GET['export']);
        }

        // Pagination for the detailed trip list
        $records_per_page = 25;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $records_per_page;
        
        $total_records = $this->tripsReportModel->getTripsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);
        
        $data = [
            'title' => 'Trips Report & Dashboard',
            'dashboard' => $this->tripsReportModel->getDashboardData($filters),
            'trips_list' => $this->tripsReportModel->getTripsList($filters, $records_per_page, $offset),
            'filter_options' => $this->tripsReportModel->getFilterOptions(),
            'filters' => $filters,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/TripsReport/index', $data);
    }

    private function get_filters() {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-t'),
            'order_status' => $_GET['order_status'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'driver_name' => $_GET['driver_name'] ?? '',
            'passenger_name' => $_GET['passenger_name'] ?? '',
            'period' => $_GET['period'] ?? 'custom',
        ];

        if (!empty($_GET['period']) && $_GET['period'] !== 'custom') {
            // ... (standard date period logic)
        }
        return $filters;
    }

    private function export($filters, $format)
    {
        $export_type = $_GET['type'] ?? 'list'; // 'list' or 'kpi'

        if ($export_type === 'list') {
            $data = $this->tripsReportModel->getTripsList($filters, 10000, 0);
            $headers = !empty($data) ? array_keys($data[0]) : [];
            $rows = $data;
        } else {
            $data = $this->tripsReportModel->getDashboardData($filters);
            $headers = ['KPI', 'Value'];
            $rows = [];
            foreach ($data as $kpi_group => $kpis) {
                foreach($kpis as $kpi => $value) {
                    $rows[] = [$kpi, is_array($value) ? json_encode($value) : $value];
                }
            }
        }
        
        $filename = 'trips_report_' . $export_type;
        $export_data = ['headers' => $headers, 'rows' => $rows];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, $filename);
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($data, $filename);
        }
    }
} 