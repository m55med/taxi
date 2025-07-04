<?php

namespace App\Controllers\Reports\TeamLeaderboard;

use App\Core\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TeamLeaderboardController extends Controller
{
    private $leaderboardModel;

    public function __construct()
    {
        parent::__construct();
        // Simple auth check
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $this->leaderboardModel = $this->model('reports/TeamLeaderboard/TeamLeaderboardModel');
    }

    public function index()
    {
        // 1. Set up filters
        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];

        // Default to current month only if no dates are provided at all
        if ($filters['date_from'] === null && $filters['date_to'] === null) {
            $filters['date_from'] = date('Y-m-01');
            $filters['date_to'] = date('Y-m-t');
        }

        // 2. Get data from the model
        $leaderboardData = $this->leaderboardModel->getTeamLeaderboard($filters);

        // 3. Calculate summary stats
        $summaryStats = [
            'total_teams' => count($leaderboardData),
            'total_points' => array_sum(array_column($leaderboardData, 'total_points')),
            'total_calls' => array_sum(array_column($leaderboardData, 'total_calls')),
            'total_tickets' => array_sum(array_column($leaderboardData, 'total_tickets')),
        ];

        // 4. Prepare data for the view
        $data = [
            'page_main_title' => 'Team Leaderboard',
            'leaderboard' => $leaderboardData,
            'summary_stats' => $summaryStats,
            'filters' => $filters,
        ];

        // 5. Load the view
        $this->view('reports/team-leaderboard/index', $data);
    }

    public function export()
    {
        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];

        $leaderboardData = $this->leaderboardModel->getTeamLeaderboard($filters);
        $exportType = $_GET['export_type'] ?? 'excel';

        if ($exportType === 'json') {
            $this->exportToJson($leaderboardData);
        } else {
            $this->exportToExcel($leaderboardData);
        }
    }

    private function flattenTeamData($team)
    {
        return [
            'Team' => $team['team_name'],
            'Members' => $team['member_count'],
            'Total Points' => number_format($team['total_points'], 2),
            'Avg Quality' => number_format($team['avg_quality_score'], 2) . '%',
            'Total Calls' => $team['total_calls'],
            'Total Tickets' => $team['total_tickets'],
        ];
    }

    private function exportToJson(array $data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment;filename="team_leaderboard_' . date('Y-m-d') . '.json"');
        
        $processedData = array_map([$this, 'flattenTeamData'], $data);

        echo json_encode($processedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function exportToExcel(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (empty($data)) {
            $sheet->setCellValue('A1', 'No data to export.');
        } else {
            $processedData = array_map([$this, 'flattenTeamData'], $data);
            $headers = array_keys($processedData[0]);

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']]
            ];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);
            
            $sheet->fromArray($processedData, null, 'A2');

            foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="team_leaderboard_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
} 