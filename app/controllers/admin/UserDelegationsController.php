<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\UserDelegation;
use App\Models\Admin\DelegationType;
use App\Models\User\User;

class UserDelegationsController extends Controller
{
    private $userDelegationModel;
    private $delegationTypeModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();

        $this->userDelegationModel = $this->model('Admin/UserDelegation');
        $this->delegationTypeModel = $this->model('Admin/DelegationType');
        $this->userModel = $this->model('User/User');
    }

    public function index()
    {
        $users = $this->userModel->getAllUsers(); 
        $delegationTypes = $this->delegationTypeModel->getAll();
        $userDelegations = $this->userDelegationModel->getAllWithDetails();

        $this->view('admin/user_delegations/index', [
            'users' => $users,
            'delegationTypes' => $delegationTypes,
            'userDelegations' => $userDelegations
        ]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $delegationTypeId = $_POST['delegation_type_id'] ?? null;
            $month = $_POST['month'] ?? null;
            $year = $_POST['year'] ?? null;
            $reason = trim($_POST['reason'] ?? '');
            $assignedBy = $_SESSION['user_id'];

            if (empty($userId) || empty($delegationTypeId) || empty($month) || empty($year)) {
                flash('error', 'Please fill in all required fields.');
                redirect('admin/user-delegations');
                return;
            }

            $result = $this->userDelegationModel->create($userId, $delegationTypeId, $reason, $month, $year, $assignedBy);

            if ($result === true) {
                flash('success', 'Delegation assigned successfully.');
            } elseif ($result === 'duplicate') {
                flash('error', 'This user already has a delegation for the selected month and year.');
            } else {
                flash('error', 'Failed to assign delegation.');
            }
            redirect('admin/user-delegations');
        } else {
            redirect('admin/user-delegations');
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            if (empty($id)) {
                flash('error', 'Invalid ID.');
                redirect('admin/user-delegations');
                return;
            }

            if ($this->userDelegationModel->delete($id)) {
                flash('success', 'Delegation deleted successfully.');
            } else {
                flash('error', 'Failed to delete delegation.');
            }
            redirect('admin/user-delegations');
        } else {
            redirect('admin/user-delegations');
        }
    }
} 