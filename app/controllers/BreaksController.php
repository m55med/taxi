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
    
        if (!empty($selectedUserId)) {
            $user = $this->userModel->getUserById($selectedUserId);
            if ($user) {
                $selectedUserName = $user->name;
            }
        }
    
        $filters = [
            'user_id' => $selectedUserId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    
        // Only add 'search' filter if no specific user is selected from dropdown
        if (empty($selectedUserId) && !empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
    
        $summary = $this->breakModel->getBreaksSummary($filters);
        $stats = $this->breakModel->getOverallSummaryStats($filters);
        
        // Fetch all agents and team leaders to populate the filter dropdown
        $users = $this->userModel->getUsersByRoles(['agent', 'Team_leader', 'admin', 'quality_manager', 'developer']);
        // The `getUsersByRoles` method already orders by name ASC.

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
            'selected_user_name' => $selectedUserName // Pass selected user's name
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
