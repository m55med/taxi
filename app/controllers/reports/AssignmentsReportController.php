<?php

namespace App\Controllers\Reports;

use App\Core\Controller;

class AssignmentsReportController extends Controller
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
        $this->assignmentsReportModel = $this->model('reports/AssignmentsReport');
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

        $data = [
            'assignments' => $this->assignmentsReportModel->getAssignments($filters),
            'summary' => $this->assignmentsReportModel->getSummary($filters),
            'staff_members' => $this->assignmentsReportModel->getStaffMembers()
        ];

        $this->view('reports/assignments', $data);
    }
} 