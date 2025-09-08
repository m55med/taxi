<?php

namespace App\Controllers\Reports\SystemLogs;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class SystemLogsController extends Controller
{
    private $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['developer', 'admin']);
        $this->logModel = $this->model('Reports/SystemLogs/SystemLogsReport');
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
        
        $total_records = $this->logModel->getLogsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $logs = $this->logModel->getLogs($filters, $records_per_page, $offset);
        $filterOptions = $this->logModel->getFilterOptions();

        $data = [
            'title' => 'System Event Logs',
            'logs' => $logs,
            'users' => $filterOptions['users'],
            'levels' => $filterOptions['levels'],
            'filters' => $filters,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/SystemLogs/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'level' => $_GET['level'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
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
        $logs = $this->logModel->getLogs($filters, 10000, 0); // Export up to 10,000 records

        $data_to_export = [
            'headers' => ['Timestamp', 'Level', 'User', 'Message', 'Context'],
            'rows' => array_map(function($log) {
                return [
                    $log['created_at'],
                    $log['level'],
                    $log['username'],
                    $log['message'],
                    $log['context']
                ];
            }, $logs)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($data_to_export, 'system_logs');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($logs, 'system_logs');
        }
    }
} 