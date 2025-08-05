<?php

namespace App\Controllers\Reports\TicketReviews;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class TicketReviewsController extends Controller
{
    private $reviewModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager']);
        $this->reviewModel = $this->model('Reports/TicketReviews/TicketReviewsReport');
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
        
        $total_records = $this->reviewModel->getReviewsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $reviews = $this->reviewModel->getReviews($filters, $records_per_page, $offset);
        
        $data = [
            'title' => 'Ticket Reviews Report',
            'reviews' => $reviews,
            'filters' => $filters,
            'filter_options' => $this->reviewModel->getFilterOptions(),
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/TicketReviews/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'reviewer_id' => $_GET['reviewer_id'] ?? '',
            'agent_id' => $_GET['agent_id'] ?? '',
            'rating_from' => $_GET['rating_from'] ?? '',
            'rating_to' => $_GET['rating_to'] ?? '',
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
        $reviews = $this->reviewModel->getReviews($filters, 10000, 0);

        $export_data = [
            'headers' => ['Ticket #', 'Agent', 'Reviewer', 'Rating', 'Notes', 'Date'],
            'rows' => array_map(fn($item) => [
                $item['ticket_number'], $item['agent_name'], $item['reviewer_name'],
                $item['rating'], $item['review_notes'], $item['reviewed_at']
            ], $reviews)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'ticket_reviews_report');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($reviews, 'ticket_reviews_report');
        }
    }
} 