<?php

namespace App\Controllers\ReportsMain;

use App\Core\Controller;

class ReportsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // تقرير السائقين حسب الحالة
    public function drivers()
    {
        $driverModel = $this->model('Driver/Driver');
        $userModel = $this->model('User/User');
        
        $filters = [
            'main_system_status' => $_GET['main_system_status'] ?? '',
            'data_source' => $_GET['data_source'] ?? '',
            'added_by' => $_GET['added_by'] ?? '',
            'has_missing_documents' => isset($_GET['has_missing_documents']) ? (int)$_GET['has_missing_documents'] : null,
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];

        $data = [
            'drivers' => $driverModel->getDriversReport($filters),
            'staff' => $userModel->getActiveStaff(),
            'filters' => $filters
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($data['drivers'], 'drivers_report');
            exit;
        }

        $this->view('reports/drivers', $data);
    }

    // تقرير المستندات
    public function documents()
    {
        $documentModel = $this->model('Document/Document');
        $userModel = $this->model('User/User');
        
        $filters = [
            'document_type_id' => $_GET['document_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'updated_by' => $_GET['updated_by'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];

        $data = [
            'documents' => $documentModel->getDocumentsReport($filters),
            'document_types' => $documentModel->getDocumentTypes(),
            'staff' => $userModel->getActiveStaff(),
            'filters' => $filters
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($data['documents'], 'documents_report');
            exit;
        }

        $this->view('reports/documents', $data);
    }

    // تقرير المكالمات
    public function calls()
    {
        $callModel = $this->model('Call/Call');
        $userModel = $this->model('User/User');
        
        $filters = [
            'call_status' => $_GET['call_status'] ?? '',
            'call_by' => $_GET['call_by'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];

        $data = [
            'calls' => $callModel->getCallsReport($filters),
            'staff' => $userModel->getActiveStaff(),
            'filters' => $filters
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($data['calls'], 'calls_report');
            exit;
        }

        $this->view('reports/calls', $data);
    }

    // تقرير التحويلات
    public function assignments()
    {
        $assignmentModel = $this->model('Assignment/Assignment');
        $userModel = $this->model('User/User');
        
        $filters = [
            'from_user_id' => $_GET['from_user_id'] ?? '',
            'to_user_id' => $_GET['to_user_id'] ?? '',
            'is_seen' => isset($_GET['is_seen']) ? (int)$_GET['is_seen'] : null,
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];

        $data = [
            'assignments' => $assignmentModel->getAssignmentsReport($filters),
            'staff' => $userModel->getActiveStaff(),
            'filters' => $filters
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($data['assignments'], 'assignments_report');
            exit;
        }

        $this->view('reports/assignments', $data);
    }

    // تقرير التحليلات
    public function analytics()
    {
        $driverModel = $this->model('Driver/Driver');
        $callModel = $this->model('Call/Call');
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];

        $data = [
            'conversion_rates' => $driverModel->getConversionRates($filters),
            'call_analysis' => $callModel->getCallAnalysis($filters),
            'filters' => $filters
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($data['conversion_rates'], 'analytics_report');
            exit;
        }

        $this->view('reports/analytics', $data);
    }

    // تصدير إلى إكسل
    private function exportToExcel($data, $filename)
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');

        echo '<table border="1">';
        
        // رأس الجدول
        if (!empty($data)) {
            echo '<tr>';
            foreach (array_keys($data[0]) as $key) {
                echo '<th>' . htmlspecialchars($key) . '</th>';
            }
            echo '</tr>';
        }

        // بيانات الجدول
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                if (is_array($value)) {
                    echo '<td>' . htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE)) . '</td>';
                } else {
                    echo '<td>' . htmlspecialchars($value) . '</td>';
                }
            }
            echo '</tr>';
        }

        echo '</table>';
    }
} 