<?php

namespace App\Controllers\Reports\MyActivity;

use App\Core\Controller;
use App\Models\Reports\Users\UsersReport;
use App\Models\User\User;

class MyActivityController extends Controller
{
    private $UsersReportModel;
    private $UserModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer', 'quality_manager', 'team_leader', 'employee']);
        $this->UsersReportModel = new UsersReport();
        $this->UserModel = new User();
    }

    public function index()
    {
        // Modern and safe way to get user info from the session array
        $user_id = $_SESSION['user']['id'] ?? null;
        $user_role = $_SESSION['user']['role_name'] ?? null;

        // Allow privileged users to view other users' reports
        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            if (in_array($user_role, ['admin', 'developer', 'quality_manager', 'team_leader'])) {
                $user_id = (int) $_GET['user_id'];
            } else {
                // Non-privileged users trying to view others are reset to their own report.
                $user_id = $_SESSION['user']['id'] ?? null;
            }
        }
        
        if (!$user_id) {
            // This will trigger if the user session is not properly set.
            die('Error: User not identified. Please login again.');
        }

        // Set up date filters
        $filters = [
            'user_id' => $user_id,
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),
        ];

        $reportData = $this->UsersReportModel->getUsersReportWithPoints($filters);
        $user_activity_data = !empty($reportData['users']) ? $reportData['users'][0] : null;

        $user_info = $this->UserModel->findById($user_id);

        $data = [
            'title' => 'My Activity Report',
            'user_activity' => $user_activity_data,
            'user_info' => $user_info,
            'filters' => $filters,
        ];

        $this->view('reports/MyActivity/index', $data);
    }
}