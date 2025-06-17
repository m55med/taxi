<?php

namespace App\Controllers\Admin;

use App\Models\Admin\Team;
use App\Core\Auth;
use App\Core\Controller;

class TeamsController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $teamModel = new Team();
        $userModel = $this->model('User');

        $teams = $teamModel->getAll();
        $users = $userModel->getAvailableForTeamLeadership();
        
        $data = [
            'teams' => $teams,
            'users' => $users,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/teams/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['team_leader_id'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            $team_leader_id = filter_input(INPUT_POST, 'team_leader_id', FILTER_VALIDATE_INT);
            
            if (!empty($name) && $team_leader_id) {
                $teamModel = new Team();
                if ($teamModel->create($name, $team_leader_id)) {
                    $_SESSION['message'] = 'تمت إضافة الفريق بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن الفريق موجود بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم الفريق وقائد الفريق حقول إلزامية.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/teams');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teamModel = new Team();
            if ($teamModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف الفريق بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف الفريق.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/teams');
        exit;
    }
} 