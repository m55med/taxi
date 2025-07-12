<?php

namespace App\Controllers\Reports\TicketsSummary;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class TicketsSummaryController extends Controller
{
    private $summaryModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager']);
        $this->summaryModel = $this->model('reports/TicketsSummary/TicketsSummaryReport');
    }

    public function index()
    {
        $filters = $this->get_filters();
        
        if (isset($_GET['export'])) {
            $this->export($filters, $_GET['export']);
        }

        $summary = $this->summaryModel->getSummary($filters);

        $data = [
            'title' => 'Tickets Summary Report',
            'summary' => $summary,
            'filters' => $filters
        ];

        $this->view('reports/TicketsSummary/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
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
        $summary = $this->summaryModel->getSummary($filters);
        
        if ($format === 'excel') {
            $export_data = [
                'headers' => ['Category', 'Value'],
                'rows' => []
            ];
            foreach($summary as $key => $values) {
                $export_data['rows'][] = [str_replace('_', ' ', strtoupper($key)), ''];
                foreach($values as $label => $count) {
                     $export_data['rows'][] = [$label, $count];
                }
                 $export_data['rows'][] = []; // Spacer
            }
            ExportHelper::exportToExcel($export_data, 'tickets_summary');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($summary, 'tickets_summary');
        }
    }
} 