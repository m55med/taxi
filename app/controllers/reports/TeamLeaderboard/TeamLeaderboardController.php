<?php

namespace App\Controllers\Reports\TeamLeaderboard;

use App\Core\Controller;
use App\Helpers\ExportHelper;
use DateTime;

class TeamLeaderboardController extends Controller
{
    private $leaderboardModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader']);
        $this->leaderboardModel = $this->model('Reports/TeamLeaderboard/TeamLeaderboardModel');
    }

    public function index()
    {
        $filters = $this->get_filters();

        if (isset($_GET['export'])) {
            $this->export($filters, $_GET['export']);
        }
        
        // Pagination
        $records_per_page = 25;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $records_per_page;
        
        $total_records = $this->leaderboardModel->getTeamsCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $leaderboardData = $this->leaderboardModel->getTeamLeaderboard($filters, $records_per_page, $offset);
        
        // Summary stats should be calculated on the full, unfiltered (by pagination) dataset
        $fullLeaderboard = $total_records > 0 ? $this->leaderboardModel->getTeamLeaderboard($filters, $total_records, 0) : [];

        $summaryStats = [
            'total_teams' => $total_records,
            'total_points' => array_sum(array_column($fullLeaderboard, 'total_points')),
            'total_calls' => array_sum(array_column($fullLeaderboard, 'total_calls')),
            'total_tickets' => array_sum(array_column($fullLeaderboard, 'total_tickets')),
        ];
        
        $data = [
            'title' => 'Team Leaderboard',
            'leaderboard' => $leaderboardData,
            'summary_stats' => $summaryStats,
            'filters' => $filters,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/team-leaderboard/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),
            'period' => $_GET['period'] ?? 'custom',
        ];

        if (!empty($_GET['period']) && $_GET['period'] !== 'custom') {
            $period = $_GET['period'];
            $today = new DateTime();
            switch ($period) {
                case 'today':
                    $filters['date_from'] = $today->format('Y-m-d');
                    $filters['date_to'] = $today->format('Y-m-d');
                    break;
                case '7days':
                    $filters['date_from'] = $today->modify('-6 days')->format('Y-m-d');
                    $filters['date_to'] = (new DateTime())->format('Y-m-d');
                    break;
                case '30days':
                    $filters['date_from'] = $today->modify('-29 days')->format('Y-m-d');
                    $filters['date_to'] = (new DateTime())->format('Y-m-d');
                    break;
                case 'all':
                    $filters['date_from'] = null;
                    $filters['date_to'] = null;
                    break;
            }
        }
        return $filters;
    }

    private function export($filters, $format)
    {
        $allData = $this->leaderboardModel->getTeamLeaderboard($filters, 10000, 0);

        if ($format === 'excel') {
            $export_data = [
                'headers' => ['Rank', 'Team', 'Members', 'Total Points', 'Avg Quality', 'Total Calls', 'Total Tickets'],
                'rows' => array_map(function($team, $index) {
                    return [
                        $index + 1,
                        $team['team_name'],
                        $team['member_count'],
                        number_format($team['total_points'], 2),
                        number_format($team['avg_quality_score'], 2) . '%',
                        $team['total_calls'],
                        $team['total_tickets'],
                    ];
                }, $allData, array_keys($allData))
            ];
            ExportHelper::exportToExcel($export_data, 'team_leaderboard');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($allData, 'team_leaderboard');
        }
    }
} 