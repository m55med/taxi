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

        $team_members = $teamMemberModel->getAll();
        $teams = $teamModel->getAll();
        $users = $teamMemberModel->getUnassignedUsers();
        
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
                
                if ($teamMemberModel->isUserInAnyTeam($user_id)) {
                    flash('team_member_message', 'This user is already a member of another team.', 'error');
                } elseif ($teamMemberModel->create($user_id, $team_id)) {
                    flash('team_member_message', 'Team member added successfully.');
                } else {
                    flash('team_member_message', 'Failed to add team member. They might already be in this team.', 'error');
                }
            } else {
                flash('team_member_message', 'Invalid data provided. Please select a user and a team.', 'error');
            }
        }
        redirect('/admin/team_members');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teamMemberModel = new TeamMember();
            if ($teamMemberModel->delete($id)) {
                flash('team_member_message', 'Team member removed successfully.');
            } else {
                flash('team_member_message', 'Failed to remove team member.', 'error');
            }
        }
        redirect('/admin/team_members');
    }
} 