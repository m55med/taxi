<?php

namespace App\Controllers\Reports\Analytics;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class AnalyticsController extends Controller
{
    private $analyticsReportModel;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->analyticsReportModel = $this->model('reports/Analytics/AnalyticsReport');
    }

    public function index()
    {
        $filters = $this->get_filters();

        if (isset($_GET['export'])) {
            $this->export($filters, $_GET['export']);
        }

        $driver_conversion = $this->analyticsReportModel->getDriverConversion($filters);
        $call_center_stats = $this->analyticsReportModel->getCallCenterStats($filters);
        $ticketing_stats = $this->analyticsReportModel->getTicketingStats($filters);

        $data = [
            'title' => 'System Analytics Report',
            'filters' => $filters,
            'driver_conversion' => $driver_conversion,
            'call_center_stats' => $call_center_stats,
            'ticketing_stats' => $ticketing_stats,
        ];

        $this->view('reports/Analytics/index', $data);
    }

    private function get_filters()
    {
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),
            'period' => $_GET['period'] ?? 'custom'
        ];

        if (!empty($_GET['period'])) {
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
        $driver_conversion = $this->analyticsReportModel->getDriverConversion($filters);
        $call_center_stats = $this->analyticsReportModel->getCallCenterStats($filters);
        $ticketing_stats = $this->analyticsReportModel->getTicketingStats($filters);

        if ($format === 'excel') {
            // In a real scenario, you'd format each piece of data into a structured array with headers and rows.
            // For this example, we will export a simplified version.
            $export_data = [
                'headers' => ['Source', 'Total Drivers', 'Completed Drivers', 'Conversion Rate (%)'],
                'rows' => array_map(function ($item) {
                    return [
                        $item['data_source'],
                        $item['total_drivers'],
                        $item['completed_drivers'],
                        round($item['conversion_rate'], 2)
                    ];
                }, $driver_conversion)
            ];
            ExportHelper::exportToExcel($export_data, 'analytics_driver_conversion');
        } elseif ($format === 'json') {
            $export_data = [
                'driver_conversion' => $driver_conversion,
                'call_center_stats' => $call_center_stats,
                'ticketing_stats' => $ticketing_stats
            ];
            ExportHelper::exportToJson($export_data, 'analytics_full_report');
        }
    }
}