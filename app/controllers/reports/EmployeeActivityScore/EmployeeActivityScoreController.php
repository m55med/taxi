<?php

namespace App\Controllers\Reports\EmployeeActivityScore;

use App\Core\Controller;
use App\Models\Admin\Team;
use App\Models\Role\Role;
use App\Helpers\ExportHelper;
use DateTime;

class EmployeeActivityScoreController extends Controller
{
    private $scoreModel;
    private $teamModel;
    private $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader']);
        $this->scoreModel = $this->model('reports/EmployeeActivityScore/EmployeeActivityScoreModel');
        $this->teamModel = $this->model('admin/Team');
        $this->roleModel = $this->model('role/Role');
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
        
        $total_records = $this->scoreModel->getScoresCount($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $scores = $this->scoreModel->getEmployeeScores($filters, $records_per_page, $offset);
        
        $data = [
            'title' => 'Employee Activity Score',
            'scores' => $scores,
            'filters' => $filters,
            'teams' => $this->teamModel->getAll(),
            'roles' => $this->roleModel->getAll(),
            'current_page' => $current_page,
            'total_pages' => $total_pages,
        ];

        $this->view('reports/employee-activity-score/index', $data);
    }
    
    private function get_filters()
    {
        $filters = [
            'team_id' => $_GET['team_id'] ?? '',
            'role_id' => $_GET['role_id'] ?? '',
            'search' => $_GET['search'] ?? '',
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
        $allScores = $this->scoreModel->getEmployeeScores($filters, 10000, 0);

        $rows = array_map(function($user, $index) {
            return [
                $index + 1, // Rank
                $user['username'],
                $user['team_name'] ?? 'N/A',
                number_format($user['points_details']['final_total_points'] ?? 0, 2),
                number_format(($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0)),
                number_format(($user['call_stats']['total_incoming_calls'] ?? 0) + ($user['call_stats']['total_outgoing_calls'] ?? 0))
            ];
        }, $allScores, array_keys($allScores));

        $export_data = [
            'headers' => ['Rank', 'Employee', 'Team', 'Total Points', 'Total Tickets', 'Total Calls'],
            'rows' => $rows
        ];
        
        if ($format === 'excel') {
            ExportHelper::exportToExcel($export_data, 'employee_activity_scores');
        } elseif ($format === 'json') {
            ExportHelper::exportToJson($allScores, 'employee_activity_scores');
        }
    }
} 