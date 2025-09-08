<?php

namespace App\Controllers\Reports\Users;

use App\Core\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UsersController extends Controller
{
    private $usersReportModel;
    private $roleModel;
    private $teamModel;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->usersReportModel = $this->model('Reports/Users/UsersReport');
        $this->roleModel = $this->model('Role/Role');
        $this->teamModel = $this->model('Admin/Team');
    }
    

    public function index()
    {
        $filters = [
            'role_id' => $_GET['role_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'team_id' => $_GET['team_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'user_id' => $_GET['user_id'] ?? ''
        ];

        // Default to current month on initial load
        if ($filters['date_from'] === null && $filters['date_to'] === null) {
            $filters['date_from'] = date('Y-m-01');
            $filters['date_to'] = date('Y-m-t');
        }

        $usersData = $this->usersReportModel->getUsersReportWithPoints($filters);

        $data = [
            'users' => $usersData['users'],
            'summary_stats' => $usersData['summary_stats'],
            'roles' => $this->roleModel->getAll(),
            'teams' => $this->teamModel->getAll(),
            'all_users' => $this->usersReportModel->getAllUsersForFilter(),
            'filters' => $filters,
        ];

        $this->view('reports/users/index', $data);
    }

    public function export()
    {
        $filters = [
            'role_id' => $_GET['role_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'team_id' => $_GET['team_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'user_id' => $_GET['user_id'] ?? ''
        ];

        $usersData = $this->usersReportModel->getUsersReportWithPoints($filters);
        $exportType = $_GET['export_type'] ?? 'excel'; // default to excel

        if ($exportType === 'json') {
            $this->exportUsersToJson($usersData['users']);
        } else { // 'excel' or any other value
            $this->exportUsersToExcel($usersData, 'users_report');
        }
    }

    private function flattenUserData($user)
    {
        return [
            'User' => $user['username'] ?? '',
            'Email' => $user['email'] ?? '',
            'Role' => $user['role_name'] ?? '',
            'Team' => $user['team_name'] ?? 'N/A',
            'Status' => ucfirst($user['status'] ?? ''),
            'Online' => ($user['is_online'] ? 'Online' : 'Offline'),
            'Total Calls' => ($user['incoming_calls'] ?? 0) + ($user['outgoing_calls'] ?? 0),
            'Incoming Calls' => $user['incoming_calls'] ?? 0,
            'Outgoing Calls' => $user['outgoing_calls'] ?? 0,
            'Normal Tickets' => $user['normal_tickets'] ?? 0,
            'VIP Tickets' => $user['vip_tickets'] ?? 0,
            'Quality Score (%)' => number_format($user['quality_score'] ?? 0, 2),
            'Total Reviews' => $user['total_reviews'] ?? 0,
            'Total Points' => number_format($user['total_points'] ?? 0, 2),
        ];
    }

    private function exportUsersToJson($data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment;filename="users_report_' . date('Y-m-d') . '.json"');

        $processedData = array_map([$this, 'flattenUserData'], $data);

        echo json_encode($processedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function exportUsersToExcel($reportData, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $users = $reportData['users'];
        $summary = $reportData['summary_stats'];

        if (empty($users)) {
            $sheet->setCellValue('A1', 'No data to export.');
        } else {
            // Flatten data and get headers
            $processedData = array_map([$this, 'flattenUserData'], $users);
            $headers = array_keys($processedData[0]);

            // Set Header Styles
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']]
            ];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

            // Add Data
            $sheet->fromArray($processedData, null, 'A2');

            // Add Totals Row
            $lastRow = count($processedData) + 2;
            $average_quality = ($summary['total_reviews'] ?? 0) > 0 ? ($summary['total_quality_score'] / $summary['total_reviews']) : 0;
            $summaryRow = [
                'Grand Total',
                '',
                '',
                '',
                '',
                '',
                ($summary['incoming_calls'] ?? 0) + ($summary['outgoing_calls'] ?? 0),
                $summary['incoming_calls'] ?? 0,
                $summary['outgoing_calls'] ?? 0,
                $summary['normal_tickets'] ?? 0,
                $summary['vip_tickets'] ?? 0,
                number_format($average_quality, 2),
                $summary['total_reviews'] ?? 0,
                number_format($summary['total_points'] ?? 0, 2)
            ];
            $sheet->fromArray($summaryRow, null, 'A' . $lastRow);

            // Style Totals Row
            $totalStyle = ['font' => ['bold' => true]];
            $sheet->getStyle('A' . $lastRow . ':' . $sheet->getHighestColumn() . $lastRow)->applyFromArray($totalStyle);
            $sheet->mergeCells('A' . $lastRow . ':F' . $lastRow);


            // Auto-size columns
            foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
