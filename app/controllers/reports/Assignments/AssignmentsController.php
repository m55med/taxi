<?php

namespace App\Controllers\Reports\Assignments;

use App\Core\Controller;

class AssignmentsController extends Controller
{
    private $assignmentsReportModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->assignmentsReportModel = $this->model('reports/Assignments/AssignmentsReport');
    }

    public function index()
    {
        $filters = [
            'from_staff_id' => $_GET['from_staff_id'] ?? '',
            'to_staff_id' => $_GET['to_staff_id'] ?? '',
            'reason' => $_GET['reason'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $totalRecords = $this->assignmentsReportModel->countAssignments($filters);
        $totalPages = ceil($totalRecords / $limit);
        
        $assignments = $this->assignmentsReportModel->getPaginatedAssignments($limit, $offset, $filters);
        
        $data = [
            'assignments' => $assignments,
            'summary' => $this->assignmentsReportModel->getSummary($filters),
            'staff_members' => $this->assignmentsReportModel->getStaffMembers(),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords
            ],
            'filters' => $filters
        ];

        $this->view('reports/Assignments/index', $data);
    }
} 