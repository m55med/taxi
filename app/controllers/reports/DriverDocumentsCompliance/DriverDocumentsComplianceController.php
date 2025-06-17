<?php

namespace App\Controllers\Reports\DriverDocumentsCompliance;

use App\Core\Controller;

class DriverDocumentsComplianceController extends Controller
{
    private $documentModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->documentModel = $this->model('reports/DriverDocumentsCompliance/DriverDocumentsComplianceReport');
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'document_type_id' => $_GET['document_type_id'] ?? '',
        ];

        $reportData = $this->documentModel->getDocumentsCompliance($filters);

        $data = [
            'title' => 'تقرير امتثال وثائق السائقين',
            'documents' => $reportData['documents'],
            'document_types' => $reportData['document_types'],
            'filters' => $filters
        ];

        $this->view('reports/DriverDocumentsCompliance/index', $data);
    }
} 