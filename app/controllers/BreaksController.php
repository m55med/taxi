<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Break\BreakModel;
use App\Models\User\User;

class BreaksController extends Controller
{
    private $breakModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->breakModel = new BreakModel();
        $this->userModel = new User(); // Initialize the UserModel
    }

    /**
     * API endpoint to start a break.
     */
    public function start()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        $ongoingBreak = $this->breakModel->getOngoingBreak($userId);
        if ($ongoingBreak) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Break already in progress.', 'break' => $ongoingBreak]);
            return;
        }

        $breakId = $this->breakModel->start($userId);

        if ($breakId) {
            $break = $this->breakModel->getOngoingBreak($userId);
            $this->sendJsonResponse(['status' => 'success', 'message' => 'Break started.', 'break' => $break]);
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to start break.'], 500);
        }
    }

    /**
     * API endpoint to stop a break.
     */
    public function stop()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $ongoingBreak = $this->breakModel->getOngoingBreak($userId);

        if (!$ongoingBreak) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'No ongoing break found.'], 404);
            return;
        }

        if ($this->breakModel->stop($ongoingBreak->id)) {
            $this->sendJsonResponse(['status' => 'success', 'message' => 'Break stopped.']);
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to stop break.'], 500);
        }
    }
    
    /**
     * API endpoint to get current break status.
     */
    public function status()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $ongoingBreak = $this->breakModel->getOngoingBreak($userId);

        if ($ongoingBreak) {
            $this->sendJsonResponse(['status' => 'success', 'on_break' => true, 'break' => $ongoingBreak]);
        } else {
            $this->sendJsonResponse(['status' => 'success', 'on_break' => false]);
        }
    }

    /**
     * API endpoint to get current ongoing breaks for all users.
     */
    public function current()
    {
        header('Content-Type: application/json');

        $currentBreaks = $this->breakModel->getCurrentOngoingBreaks();

        // Convert objects to arrays for JSON response
        $breaksArray = [];
        foreach ($currentBreaks as $break) {
            $breaksArray[] = [
                'id' => $break['id'] ?? $break->id,
                'user_id' => $break['user_id'] ?? $break->user_id,
                'user_name' => $break['user_name'] ?? $break->user_name,
                'team_name' => $break['team_name'] ?? $break->team_name,
                'start_time' => $break['start_time'] ?? $break->start_time,
                'minutes_elapsed' => $break['minutes_elapsed'] ?? $break->minutes_elapsed,
                'is_long_break' => ($break['minutes_elapsed'] ?? $break->minutes_elapsed) >= 30
            ];
        }

        $this->sendJsonResponse($breaksArray);
    }

    /**
     * API endpoint to get current breaks count only (for live counter).
     */
    public function current_count()
    {
        header('Content-Type: application/json');

        $currentBreaks = $this->breakModel->getCurrentOngoingBreaks();
        $count = count($currentBreaks);
        $longBreaks = 0;

        foreach ($currentBreaks as $break) {
            $minutes = $break['minutes_elapsed'] ?? $break->minutes_elapsed ?? 0;
            if ($minutes >= 30) {
                $longBreaks++;
            }
        }

        $this->sendJsonResponse([
            'total' => $count,
            'long_breaks' => $longBreaks
        ]);
    }

    /**
     * Display the main breaks report page.
     */
    public function report()
    {
        // Default dates to the current month
        $defaultFromDate = date('Y-m-01');
        $defaultToDate = date('Y-m-t');
    
        // Handle quick period filters
        $period = $_GET['period'] ?? 'this_month';
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
    
        if (!$fromDate || !$toDate) {
            switch ($period) {
                case 'today':
                    $fromDate = date('Y-m-d');
                    $toDate = date('Y-m-d');
                    break;
                case 'last7':
                    $fromDate = date('Y-m-d', strtotime('-6 days'));
                    $toDate = date('Y-m-d');
                    break;
                case 'last30':
                    $fromDate = date('Y-m-d', strtotime('-29 days'));
                    $toDate = date('Y-m-d');
                    break;
                case 'all':
                    $fromDate = null;
                    $toDate = null;
                    break;
                case 'this_month':
                default:
                    $fromDate = $defaultFromDate;
                    $toDate = $defaultToDate;
                    break;
            }
        }
    
        $selectedUserId = $_GET['user_id'] ?? null;
        $selectedUserName = '';
        $selectedTeamId = $_GET['team_id'] ?? null;
        $selectedTeamName = '';

        if (!empty($selectedUserId)) {
            $user = $this->userModel->getUserById($selectedUserId);
            if ($user) {
                $selectedUserName = $user->name;
            }
        }

        if (!empty($selectedTeamId)) {
            // Get team name using BreakModel
            $selectedTeamName = $this->breakModel->getTeamNameById($selectedTeamId);
        }
    
        $sortBy = $_GET['sort_by'] ?? 'total_duration_seconds';
        $sortOrder = $_GET['sort_order'] ?? 'desc';

        $filters = [
            'user_id' => $selectedUserId,
            'team_id' => $selectedTeamId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];
    
        // Only add 'search' filter if no specific user is selected from dropdown
        if (empty($selectedUserId) && !empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
    
        $summary = $this->breakModel->getBreaksSummary($filters);
        $stats = $this->breakModel->getOverallSummaryStats($filters);
        $currentBreakCount = $this->breakModel->getCurrentBreakCount();
        
        // Fetch all agents and team leaders to populate the filter dropdown
        $users = $this->userModel->getUsersByRoles(['agent', 'Team_leader', 'admin', 'quality_manager', 'developer']);
        // The `getUsersByRoles` method already orders by name ASC.

        // Fetch all teams for filtering
        $teams = $this->breakModel->getAllTeams();

        // Hardcoded dummy data for testing
        // $users = [
        //     ['id' => 1, 'name' => 'Test User One'],
        //     ['id' => 2, 'name' => 'Test User Two'],
        //     ['id' => 3, 'name' => 'Test User Three'],
        // ];

        $this->view('reports/breaks_summary', [
            'summary' => $summary,
            'stats' => $stats,
            'filters' => $filters,
            'period' => $period,
            'users' => $users, // Pass users to the view
            'teams' => $teams, // Pass teams to the view
            'selected_user_name' => $selectedUserName, // Pass selected user's name
            'selected_team_name' => $selectedTeamName, // Pass selected team's name
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'current_break_count' => $currentBreakCount // Pass current break count
        ]);
    }
    

    /**
     * Display the detailed breaks report for a single user.
     */
    public function userReport($userId)
    {
        $userModel = $this->model('User/User');
        $user = $userModel->getUserById($userId);
        
        if (!$user) {
            die('User not found.');
        }

        // Default dates to the current month
        $defaultFromDate = date('Y-m-01');
        $defaultToDate = date('Y-m-t');

        // Handle quick period filters
        $period = $_GET['period'] ?? 'this_month';
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;

        if (!$fromDate || !$toDate) {
            switch ($period) {
                case 'today':
                    $fromDate = date('Y-m-d');
                    $toDate = date('Y-m-d');
                    break;
                case 'last7':
                    $fromDate = date('Y-m-d', strtotime('-6 days'));
                    $toDate = date('Y-m-d');
                    break;
                case 'last30':
                    $fromDate = date('Y-m-d', strtotime('-29 days'));
                    $toDate = date('Y-m-d');
                    break;
                case 'all':
                    $fromDate = null;
                    $toDate = null;
                    break;
                case 'this_month':
                default:
                    $fromDate = $defaultFromDate;
                    $toDate = $defaultToDate;
                    break;
            }
        }
        
        $filters = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        $breaks = $this->breakModel->getBreaksForUser($userId, $filters);
        $stats = $this->breakModel->getUserSummaryStats($userId, $filters);
        
        $this->view('reports/user_breaks', [
            'breaks' => $breaks, 
            'user' => $user,
            'stats' => $stats,
            'filters' => $filters,
            'period' => $period
        ]);
    }
}
