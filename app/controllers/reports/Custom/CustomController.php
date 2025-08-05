<?php

namespace App\Controllers\Reports\Custom;

use App\Core\Controller;
use App\Core\Auth;

class CustomController extends Controller
{
    private $customModel;

    public function __construct()
    {
        parent::__construct();
        Auth::check();
        if (!in_array($_SESSION['role_name'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->customModel = $this->model('Reports/Custom/CustomReport');
    }

    public function index()
    {
        $reportData = [];
        $queryDetails = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $table = $_POST['table'] ?? '';
            $columns = $_POST['columns'] ?? [];
            $filters = $_POST['filters'] ?? []; // Array of filter conditions

            if ($table && !empty($columns)) {
                list($reportData, $queryDetails) = $this->customModel->buildAndRunQuery($table, $columns, $filters);
            }
        }

        $data = [
            'title' => 'بناء التقارير المخصصة',
            'tables' => $this->customModel->getTables(),
            'reportData' => $reportData,
            'queryDetails' => $queryDetails
        ];

        $this->view('reports/Custom/index', $data);
    }

    public function getColumns()
    {
        if (isset($_GET['table'])) {
            $columns = $this->customModel->getColumnsForTable($_GET['table']);
            echo json_encode($columns);
        }
    }
}