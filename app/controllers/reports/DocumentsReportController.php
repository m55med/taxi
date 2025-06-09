<?php

namespace App\Controllers\Reports;

use App\Core\Controller;

class DocumentsReportController extends Controller
{
    private $documentsReportModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->documentsReportModel = $this->model('reports/DocumentsReport');
    }

    public function index()
    {
        $filters = [
            'document_type' => $_GET['document_type'] ?? '',
            'verification_status' => $_GET['verification_status'] ?? '',
            'verified_by' => $_GET['verified_by'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $data = $this->documentsReportModel->getDocumentsReport($filters);
        
        $this->view('reports/documents', $data);
    }
} 