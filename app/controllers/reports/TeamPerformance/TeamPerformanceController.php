<?php

namespace App\Controllers\Reports\TeamPerformance;

use App\Core\Controller;

class TeamPerformanceController extends Controller
{
    private $teamPerformanceModel;
    private $teamModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        // Authorization: Only Team Leaders and Admins/Developers can access
        if (!in_array($_SESSION['role'], ['Team_leader', 'admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->teamPerformanceModel = $this->model('reports/TeamPerformance/TeamPerformanceReport');
        $this->teamModel = $this->model('Admin/Team');
    }

    public function index()
    {
        $leaderId = $_SESSION['user_id'];
        
        // Admin can view any team's report, so we might get team_id from GET
        $teamId = $_GET['team_id'] ?? null;
        if ($_SESSION['role'] === 'admin' && $teamId) {
             $team = $this->teamModel->findById($teamId);
             $leaderId = $team ? $team['team_leader_id'] : null;
        } else {
            $team = $this->teamModel->findByLeaderId($leaderId);
        }

        if (!$team) {
            $_SESSION['error'] = 'أنت لا تقود فريقًا أو الفريق المحدد غير موجود.';
            $this->view('reports/TeamPerformance/index', ['error' => $_SESSION['error']]);
            unset($_SESSION['error']);
            return;
        }

        $filters = [
            'member_id' => $_GET['member_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $reportData = $this->teamPerformanceModel->getReportData($team['id'], $filters);

        $data = [
            'title' => 'تقرير أداء الفريق: ' . htmlspecialchars($team['name']),
            'team' => $team,
            'members' => $reportData['members_performance'],
            'summary' => $reportData['team_summary'],
            'filters' => $filters,
            'all_teams' => ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer') ? $this->teamModel->getAll() : []
        ];

        $this->view('reports/TeamPerformance/index', $data);
    }
} 