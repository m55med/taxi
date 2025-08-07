<?php

namespace App\Controllers\Reports\Custom;

use App\Core\Controller;
use App\Core\Auth;
use App\Helpers\ExportHelper;

class CustomController extends Controller
{
    private $customModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        if (!in_array($_SESSION['user']['role_name'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
    }

    private function getModel()
    {
        if (!isset($this->customModel)) {
            $this->customModel = $this->model('Reports/Custom/CustomReport');
        }
        return $this->customModel;
    }

    public function index()
    {
        // Handle AJAX request for report data
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['export_type'])) {
            header('Content-Type: application/json');
            
            $tables = $_POST['tables'] ?? [];
            $columns = $_POST['columns'] ?? [];
            $filters = $_POST['filters'] ?? [];
            $joins = $_POST['joins'] ?? [];

            if (empty($tables)) {
                echo json_encode(['error' => 'الرجاء اختيار جدول واحد على الأقل.']);
                return;
            }
            if (empty($columns)) {
                echo json_encode(['error' => 'الرجاء اختيار عمود واحد على الأقل.']);
                return;
            }

            list($reportData, $queryDetails) = $this->getModel()->buildAndRunQuery($tables, $columns, $filters, $joins);

            if (is_string($reportData)) { // Model returned an error string
                 echo json_encode(['error' => $reportData, 'queryDetails' => $queryDetails]);
                 return;
            }

            echo json_encode([
                'reportData' => $reportData,
                'queryDetails' => $queryDetails
            ]);
            return;
        }

        // Handle file export request (traditional form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['export_type'])) {
            $tables = $_POST['tables'] ?? [];
            $columns = $_POST['columns'] ?? [];
            $filters = $_POST['filters'] ?? [];
            $joins = $_POST['joins'] ?? [];
            $export_type = $_POST['export_type'];

            if (empty($tables) || empty($columns)) {
                 // Should not happen with client-side validation, but as a fallback
                $_SESSION['error'] = 'بيانات التقرير غير مكتملة للتصدير.';
                header('Location: ' . URLROOT . '/reports/custom');
                exit;
            }
            
            list($reportData, $queryDetails) = $this->getModel()->buildAndRunQuery($tables, $columns, $filters, $joins);

            if (!empty($reportData) && is_array($reportData)) {
                $exportData = [
                    'headers' => array_keys($reportData[0]),
                    'rows' => array_map('array_values', $reportData)
                ];
                $fileName = "custom_report_" . date('Y-m-d');
                
                switch ($export_type) {
                    case 'excel':
                        ExportHelper::exportToExcel($exportData, $fileName);
                        break;
                    case 'json':
                        ExportHelper::exportToJson($reportData, $fileName);
                        break;
                    case 'csv':
                        ExportHelper::exportToCsv($exportData, $fileName);
                        break;
                    case 'pdf':
                        ExportHelper::exportToPdf($exportData, $fileName);
                        break;
                }
                return; // Stop execution after export
            } else {
                $_SESSION['error'] = 'لا يمكن تصدير تقرير فارغ أو به خطأ.';
                header('Location: ' . URLROOT . '/reports/custom');
                exit;
            }
        }
        
        // Initial page load (GET request)
        $data = [
            'title' => 'بناء التقارير المخصصة',
            'tables' => $this->getModel()->getTables(),
        ];

        $this->view('reports/Custom/index', $data);
    }

    public function getColumns()
    {
        if (isset($_GET['tables']) && is_array($_GET['tables'])) {
            $tableNames = $_GET['tables'];
            $columnsByTable = [];

            foreach($tableNames as $tableName) {
                $allColumns = $this->getModel()->getColumnsForTable($tableName);
                
                // Exclude sensitive columns from being shown in the selection list
                if ($tableName === 'users') {
                     $sensitiveColumns = ['password'];
                     $allColumns = array_diff($allColumns, $sensitiveColumns);
                }
                $columnsByTable[$tableName] = array_values($allColumns);
            }
            
            header('Content-Type: application/json');
            echo json_encode($columnsByTable);
        } else if (isset($_GET['table'])) { // Backwards compatibility for single table
             $tableName = $_GET['table'];
             $allColumns = $this->getModel()->getColumnsForTable($tableName);
            
             // Exclude sensitive columns from being shown in the selection list
             if ($tableName === 'users') {
                  $sensitiveColumns = ['password'];
                  $allColumns = array_diff($allColumns, $sensitiveColumns);
             }

             header('Content-Type: application/json');
             echo json_encode(array_values($allColumns));
        }
    }
}
