<?php

namespace App\Controllers\Reports\EmployeeActivityScore;

use App\Core\Controller;
use App\Models\Reports\EmployeeActivityScore\EmployeeActivityScoreModel;
use App\Models\Admin\Team; // For team filter dropdown
use App\Models\Role\Role;   // For role filter dropdown

class EmployeeActivityScoreController extends Controller
{
    private $scoreModel;
    private $teamModel;
    private $roleModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $this->scoreModel = $this->model('reports/EmployeeActivityScore/EmployeeActivityScoreModel');
        $this->teamModel = $this->model('admin/Team');
        $this->roleModel = $this->model('role/Role');
    }

    public function index()
    {
        // 1. Set up filters from GET request
        $filters = [
            'team_id' => $_GET['team_id'] ?? '',
            'role_id' => $_GET['role_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),
        ];

        // 2. Get sorted employee data from the model
        $employeeScores = $this->scoreModel->getEmployeeScores($filters);

        // 3. Prepare data for the view
        $data = [
            'page_main_title' => 'Employee Activity Score',
            'scores' => $employeeScores,
            'filters' => $filters,
            'teams' => $this->teamModel->getAll(), // For the filter dropdown
            'roles' => $this->roleModel->getAll(), // For the filter dropdown
        ];

        // 4. Load the view
        $this->view('reports/employee-activity-score/index', $data);
    }
} 