<?php

namespace App\Controllers\Logs;

use App\Core\Controller;
use App\Core\Auth;

class LogsController extends Controller {
    private $logModel;

    public function __construct() {
        parent::__construct(); // It's good practice to call parent constructor
        // The login check is now handled by $this->authorize() in the methods.
        $this->logModel = $this->model('logs/Log');
    }

    public function index() {
        // Ensure user has permission to view this page
        $this->authorize(['admin', 'developer', 'quality_manager', 'Team_leader', 'agent', 'employee']);

        $page_main_title = 'سجل الأنشطة';
        
        // Default filters from GET request
        $filters = [
            'activity_type' => $_GET['activity_type'] ?? 'all',
            'user_id' => $_GET['user_id'] ?? 'all',
            'team_id' => $_GET['team_id'] ?? 'all',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Apply role-based restrictions
        $userRole = $_SESSION['role'];
        $userId = $_SESSION['user_id'];

        if ($userRole === 'agent' || $userRole === 'employee') {
            $filters['user_id'] = $userId; // Agents can only see their own activities
        } elseif ($userRole === 'Team_leader') {
            $teamId = $this->logModel->getTeamIdForLeader($userId);
            if ($teamId) {
                // If a team leader tries to view "all teams", restrict to their own team.
                if (!isset($_GET['team_id']) || $_GET['team_id'] === 'all' || $_GET['team_id'] == '') {
                    $filters['team_id'] = $teamId;
                }
            } else {
                // If not a leader of any team, they only see their own activities
                $filters['user_id'] = $userId;
            }
        }
        
        // Data for filter dropdowns
        $users = $this->logModel->getUsers();
        $teams = $this->logModel->getTeams();
        
        // Pagination logic
        $limitOptions = [20, 50, 100, 250, 500];
        $limit = isset($_GET['limit']) && in_array($_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 50;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Get activities with pagination
        $result = $this->logModel->getActivities($filters, $limit, $offset);
        $activities = $result['activities'];
        $totalRecords = $result['total'];
        $totalPages = ceil($totalRecords / $limit);

        $data = [
            'page_main_title' => $page_main_title,
            'activities' => $activities,
            'filters' => $filters,
            'users' => $users,
            'teams' => $teams,
            'userRole' => $userRole,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'limit' => $limit,
                'limitOptions' => $limitOptions,
                'totalRecords' => $totalRecords
            ]
        ];

        $this->view('logs/index', $data);
    }
} 