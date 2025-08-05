<?php

namespace App\Controllers\Reports\DriverDocumentsCompliance;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class DriverDocumentsComplianceController extends Controller
{
    private $documentModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager']);
        $this->documentModel = $this->model('Reports/DriverDocumentsCompliance/DriverDocumentsComplianceReport');
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
        
        $total_records = $this->documentModel->getDocumentsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $documents = $this->documentModel->getDocuments($filters, $records_per_page, $offset);
        
        $data = [
            'title' => 'Driver Documents Compliance Report',
            'documents' => $documents,
            'stats' => $this->documentModel->getStats(),
            'filters' => $filters,
            'filter_options' => $this->documentModel->getFilterOptions(),
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/DriverDocumentsCompliance/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'driver_id' => $_GET['driver_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'document_type_id' => $_GET['document_type_id'] ?? '',
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
        $documents = $this->documentModel->getDocuments($filters, 10000, 0);

        $export_data = [
            'headers' => ['Driver', 'Document Type', 'Status', 'Updated By', 'Last Updated', 'Note'],
            'rows' => array_map(fn($item) => [
                $item['driver_name'], $item['document_type_name'], $item['status'],
                $item['updated_by_user'] ?? 'N/A', $item['updated_at'], $item['note']
            ], $documents)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'driver_documents_compliance');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($documents, 'driver_documents_compliance');
        }
    }
} 