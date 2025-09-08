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
        $userModel = $this->model('User/User');

        $teams = $teamModel->getAll();
        $users = $userModel->getAvailableForTeamLeadership();
        
        $data = [
            'teams' => $teams,
            'users' => $users
        ];

        $this->view('admin/teams/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['team_leader_id'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            $team_leader_id = filter_input(INPUT_POST, 'team_leader_id', FILTER_VALIDATE_INT);
            
            if (!empty($name) && $team_leader_id) {
                $teamModel = new Team();
                if ($teamModel->create($name, $team_leader_id)) {
                    $_SESSION['team_message'] = 'Team added successfully.';
                    $_SESSION['team_message_type'] = 'success';
                } else {
                    $_SESSION['team_message'] = 'Error: Team could not be added or already exists.';
                    $_SESSION['team_message_type'] = 'error';
                }
            } else {
                $_SESSION['team_message'] = 'Team name and team leader are required.';
                $_SESSION['team_message_type'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/teams');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teamModel = new Team();
            if ($teamModel->delete($id)) {
                $_SESSION['team_message'] = 'Team deleted successfully.';
                $_SESSION['team_message_type'] = 'success';
            } else {
                $_SESSION['team_message'] = 'Error deleting the team.';
                $_SESSION['team_message_type'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/teams');
        exit;
    }

    public function create()
    {
        $teamModel = $this->model('Admin/Team');
        $userModel = $this->model('User/User');

        $members = $userModel->getAvailableForTeamLeadership();

        $data = [
            'teams' => $teamModel->getAll(),
            'users' => $members,
        ];

        $this->view('admin/teams/create', $data);
    }

    public function edit($id)
    {
        $teamModel = $this->model('Admin/Team');
        $userModel = $this->model('User/User');
        $team = $teamModel->getTeamById($id);
        $members = $userModel->getAvailableForTeamLeadership($id);

        $data = [
            'team' => $team,
            'users' => $members,
        ];

        $this->view('admin/teams/edit', $data);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $name = trim(htmlspecialchars($_POST['name'] ?? ''));
            
            // Team leader is optional on update
            $team_leader_id = filter_input(INPUT_POST, 'team_leader_id', FILTER_VALIDATE_INT);
            if ($team_leader_id === false || $team_leader_id === 0) {
                $team_leader_id = null; // Set to null if not provided or empty
            }

            if (!empty($name) && !empty($id)) {
                $teamModel = new Team();
                if ($teamModel->update($id, $name, $team_leader_id)) {
                    $_SESSION['team_message'] = 'Team updated successfully.';
                    $_SESSION['team_message_type'] = 'success';
                } else {
                    $_SESSION['team_message'] = 'Error updating the team or no changes were made.';
                    $_SESSION['team_message_type'] = 'error';
                }
            } else {
                $_SESSION['team_message'] = 'Team name and ID are required.';
                $_SESSION['team_message_type'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/teams');
        exit;
    }

    public function addMember()
    {
        $teamModel = $this->model('Admin/Team');
        $userModel = $this->model('User/User');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $teamId = $_POST['team_id'];
            $userId = $_POST['user_id'];
            if ($teamModel->addMember($teamId, $userId)) {
                $_SESSION['team_message'] = 'Member added to the team successfully.';
                $_SESSION['team_message_type'] = 'success';
            } else {
                $_SESSION['team_message'] = 'Error adding member to the team.';
                $_SESSION['team_message_type'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/teams/edit/' . $teamId);
        exit;
    }

    public function removeMember($teamId, $userId)
    {
        $teamModel = $this->model('Admin/Team');
        $userModel = $this->model('User/User');
        $teamModel->removeMember($teamId, $userId);
        $_SESSION['team_message'] = 'Member removed from the team successfully.';
        $_SESSION['team_message_type'] = 'success';
        header('Location: ' . URLROOT . '/admin/teams/edit/' . $teamId);
        exit;
    }
} 