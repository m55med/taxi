<?php

namespace App\Controllers\Admin;

use App\Models\Admin\TeamMember;
use App\Models\Admin\Team;
use App\Core\Auth;
use App\Core\Controller;

class TeamMembersController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $teamMemberModel = new TeamMember();
        $teamModel = new Team();
        $userModel = $this->model('User');

        $team_members = $teamMemberModel->getAll();
        $teams = $teamModel->getAll();
        $users = $userModel->getActiveStaff();
        
        $data = [
            'team_members' => $team_members,
            'teams' => $teams,
            'users' => $users,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/team_members/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['team_id'])) {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
            
            if ($user_id && $team_id) {
                $teamMemberModel = new TeamMember();
                if ($teamMemberModel->create($user_id, $team_id)) {
                    $_SESSION['message'] = 'تمت إضافة عضو الفريق بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن العضو موجود بالفعل في هذا الفريق.';
                }
            } else {
                $_SESSION['error'] = 'البيانات المدخلة غير صالحة. يرجى اختيار مستخدم وفريق.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/team_members');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teamMemberModel = new TeamMember();
            if ($teamMemberModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف عضو الفريق بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف عضو الفريق.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/team_members');
        exit;
    }
} 