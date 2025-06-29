<?php

namespace App\Controllers\Reports\Users;

use App\Core\Controller;

class UsersController extends Controller
{
    private $usersReportModel;
    private $roleModel;
    private $teamModel;

    public function __construct()
    {
        parent::__construct();

        // Authorization check
        if (!isset($_SESSION['user_id'])) {
            flash('auth_error', 'You must be logged in to view this page.', 'error');
            redirect('auth/login');
        }

        // Specific permissions for this page
        if (!in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager', 'team_leader'])) {
            flash('auth_error', 'You are not authorized to access this page.', 'error');
            redirect('dashboard');
        }

        $this->usersReportModel = $this->model('reports/Users/UsersReport');
        $this->roleModel = $this->model('role/Role');
        $this->teamModel = $this->model('admin/Team');
    }

    public function index()
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

        $data = [
            'users' => $usersData['users'],
            'summary_stats' => $usersData['summary_stats'],
            'roles' => $this->roleModel->getAll(),
            'teams' => $this->teamModel->getAll(),
            'all_users' => $this->usersReportModel->getAllUsersForFilter(),
            'filters' => $filters,
        ];

        $this->view('reports/Users/index', $data);
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

        $this->exportUsersToExcel($usersData['users'], 'users_report');
    }


    private function exportUsersToExcel($data, $filename)
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        // Define headers in English
        $headers = [
            'username' => 'User',
            'email' => 'Email',
            'role_name' => 'Role',
            'team_name' => 'Team',
            'status' => 'Status',
            'is_online' => 'Online',
            'total_calls' => 'Total Calls',
            'answered' => 'Answered Calls',
            'no_answer' => 'No-Answer Calls',
            'busy' => 'Busy Calls',
            'answered_rate' => 'Answer Rate (%)',
            'today_total' => 'Today\'s Total Calls',
            'today_answered' => 'Today\'s Answered Calls',
            'normal_tickets' => 'Normal Tickets',
            'vip_tickets' => 'VIP Tickets',
            'assignments_count' => 'Assignments'
        ];

        echo '<table border="1">';
        
        // Print headers
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';

        // Print data rows
        foreach ($data as $row) {
            echo '<tr>';
            
            // Manually map each column to ensure correct order and handling
            echo '<td>' . htmlspecialchars($row['username'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['email'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['role_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['team_name'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($row['status'] ?? '')) . '</td>';
            echo '<td>' . (isset($row['is_online']) && $row['is_online'] ? 'Online' : 'Offline') . '</td>';
            
            // Call stats from the nested array
            $stats = $row['call_stats'] ?? [];
            echo '<td>' . htmlspecialchars($stats['total_calls'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars($stats['answered'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars($stats['no_answer'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars($stats['busy'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars(number_format($stats['answered_rate'] ?? 0, 1)) . '</td>';
            echo '<td>' . htmlspecialchars($stats['today_total'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars($stats['today_answered'] ?? '0') . '</td>';

            // New stats from the main row
            echo '<td>' . htmlspecialchars($row['normal_tickets'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars($row['vip_tickets'] ?? '0') . '</td>';
            echo '<td>' . htmlspecialchars($row['assignments_count'] ?? '0') . '</td>';
            
            echo '</tr>';
        }

        echo '</table>';
    }
}