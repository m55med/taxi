<?php

namespace App\Controllers\Reports\Analytics;

use App\Core\Controller;

class AnalyticsController extends Controller
{
    private $analyticsReportModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->analyticsReportModel = $this->model('reports/Analytics/AnalyticsReport');
    }

    public function index()
    {
        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $data = [
            'conversion_rates' => $this->analyticsReportModel->getConversionRates($filters),
            'call_analysis' => $this->analyticsReportModel->getCallAnalysis($filters)
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($data, 'analytics_report');
            exit;
        }

        $this->view('reports/Analytics/index', $data);
    }

    private function exportToExcel($data, $filename)
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // You may need to format this export based on the data structure
        echo '<h1>Analytics Report</h1>';
        echo '<table border="1">';
        echo '<tr><th colspan="2">Conversion Rates</th></tr>';
        foreach($data['conversion_rates'] as $rate) {
            echo '<tr><td>' . htmlspecialchars($rate['data_source']) . '</td><td>' . htmlspecialchars($rate['conversion_rate']) . '%</td></tr>';
        }
        echo '</table>';
        
        echo '<br>';

        echo '<table border="1">';
        echo '<tr><th colspan="2">Call Analysis</th></tr>';
        // Add more details as needed
        echo '</table>';
    }
} 