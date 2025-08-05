<?php

namespace App\Controllers\Reports\DriverAssignments;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class DriverAssignmentsController extends Controller
{
    private $assignmentModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader']);
        $this->assignmentModel = $this->model('Reports/DriverAssignments/DriverAssignmentsReport');
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
        
        $total_records = $this->assignmentModel->getAssignmentsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $assignments = $this->assignmentModel->getAssignments($filters, $records_per_page, $offset);
        $filterOptions = $this->assignmentModel->getFilterOptions();
        
        $data = [
            'title' => 'Driver Assignments Report',
            'assignments' => $assignments,
            'filters' => $filters,
            'filter_options' => $filterOptions,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/DriverAssignments/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'driver_id' => $_GET['driver_id'] ?? '',
            'from_user_id' => $_GET['from_user_id'] ?? '',
            'to_user_id' => $_GET['to_user_id'] ?? '',
            'is_seen' => $_GET['is_seen'] ?? '',
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
        $assignments = $this->assignmentModel->getAssignments($filters, 10000, 0);

        $export_data = [
            'headers' => ['ID', 'Driver', 'Assigned From', 'Assigned To', 'Seen', 'Note', 'Date'],
            'rows' => array_map(function($item) {
                return [
                    $item['id'],
                    $item['driver_name'],
                    $item['from_user_name'],
                    $item['to_user_name'],
                    $item['is_seen'] ? 'Yes' : 'No',
                    $item['note'],
                    $item['created_at']
                ];
            }, $assignments)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'driver_assignments_report');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($assignments, 'driver_assignments_report');
        }
    }
} 