<?php

namespace App\Controllers\Reports\ReferralVisits;

use App\Core\Controller;
use App\Models\Reports\ReferralVisits\ReferralVisitsReport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Json;

class ReferralVisitsController extends Controller
{
    private $visitModel;

    public function __construct()
    {
        parent::__construct();
        $this->visitModel = new ReferralVisitsReport();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 25;

        $filters = [
            'affiliate_id' => $_GET['affiliate_id'] ?? '',
            'registration_status' => $_GET['registration_status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $result = $this->visitModel->getVisits($filters, $page, $perPage);
        $affiliates = $this->visitModel->getAffiliateMarketers();
        $summaryStats = $this->visitModel->getSummaryStats($filters);

        $data = [
            'title' => 'Referral Visits Report',
            'visits' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'totalPages' => ceil($result['total'] / $perPage),
            'filters' => $filters,
            'affiliates' => $affiliates,
            'summary' => $summaryStats
        ];

        $this->view('reports/ReferralVisits/index', $data);
    }

    private function getFiltersFromRequest()
    {
        return [
            'affiliate_id' => $_GET['affiliate_id'] ?? '',
            'registration_status' => $_GET['registration_status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];
    }

    public function exportExcel()
    {
        $filters = $this->getFiltersFromRequest();
        $result = $this->visitModel->getVisits($filters, 1, 0, true);
        $visits = $result['data'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Visit Time');
        $sheet->setCellValue('C1', 'Affiliate Name');
        $sheet->setCellValue('D1', 'Registration Status');
        $sheet->setCellValue('E1', 'Registered Driver');
        $sheet->setCellValue('F1', 'IP Address');
        $sheet->setCellValue('G1', 'User Agent');
        
        // Populate data
        $row = 2;
        foreach ($visits as $visit) {
            $sheet->setCellValue('A' . $row, $visit['id']);
            $sheet->setCellValue('B' . $row, $visit['visit_recorded_at']);
            $sheet->setCellValue('C' . $row, $visit['affiliate_user_name'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $visit['registration_status']);
            $sheet->setCellValue('E' . $row, $visit['registered_driver_name'] ?? 'N/A');
            $sheet->setCellValue('F' . $row, $visit['ip_address']);
            $sheet->setCellValue('G' . $row, $visit['user_agent']);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="referral_visits_report.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportJson()
    {
        $filters = $this->getFiltersFromRequest();
        $result = $this->visitModel->getVisits($filters, 1, 0, true);
        $visits = $result['data'];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment;filename="referral_visits_report.json"');
        echo json_encode($visits, JSON_PRETTY_PRINT);
        exit;
    }
} 