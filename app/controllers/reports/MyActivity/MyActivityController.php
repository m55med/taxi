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
        // Determine the user to display. Admins can view others, otherwise view self.
        $user_id = $_SESSION['user_id'];
        if (isset($_GET['user_id']) && in_array($_SESSION['role_name'], ['admin', 'developer', 'quality_manager', 'team_leader'])) {
            $user_id = (int) $_GET['user_id'];
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