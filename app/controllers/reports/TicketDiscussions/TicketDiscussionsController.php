<?php

namespace App\Controllers\Reports\TicketDiscussions;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class TicketDiscussionsController extends Controller
{
    private $discussionModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager']);
        $this->discussionModel = $this->model('Reports/TicketDiscussions/TicketDiscussionsReport');
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
        
        $total_records = $this->discussionModel->getDiscussionsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $discussions = $this->discussionModel->getDiscussions($filters, $records_per_page, $offset);
        
        $data = [
            'title' => 'Ticket Discussions Report',
            'discussions' => $discussions,
            'filters' => $filters,
            'filter_options' => $this->discussionModel->getFilterOptions(),
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/TicketDiscussions/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'ticket_id' => $_GET['ticket_id'] ?? '',
            'opened_by' => $_GET['opened_by'] ?? '',
            'status' => $_GET['status'] ?? '',
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
        $discussions = $this->discussionModel->getDiscussions($filters, 10000, 0);

        $export_data = [
            'headers' => ['Ticket #', 'Opened By', 'Status', 'Reason', 'Notes', 'Date'],
            'rows' => array_map(fn($item) => [
                $item['ticket_number'], $item['opened_by_user'], $item['status'],
                $item['reason'], $item['notes'], $item['created_at']
            ], $discussions)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'ticket_discussions_report');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($discussions, 'ticket_discussions_report');
        }
    }
} 