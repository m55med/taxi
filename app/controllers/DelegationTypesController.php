<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Admin\DelegationType;

class DelegationTypesController extends Controller
{
    private $delegationTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize('DelegationTypes/index');
        $this->delegationTypeModel = $this->model('Admin/DelegationType');
    }

    public function index()
    {
        $delegationTypes = $this->delegationTypeModel->getAll();
        $this->view('admin/delegation_types/index', ['delegationTypes' => $delegationTypes]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $percentage = trim($_POST['percentage'] ?? '');

            if (empty($name) || !is_numeric($percentage)) {
                flash('error', 'Please fill in all fields correctly.');
                redirect('delegation-types');
                return;
            }

            if ($this->delegationTypeModel->create($name, $percentage)) {
                flash('success', 'Delegation type created successfully.');
            } else {
                flash('error', 'Failed to create delegation type.');
            }
            redirect('delegation-types');
        } else {
            redirect('delegation-types');
        }
    }
    
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $percentage = trim($_POST['percentage'] ?? '');

            if (empty($name) || !is_numeric($percentage) || empty($id)) {
                flash('error', 'Invalid data provided.');
                redirect('delegation-types');
                return;
            }

            if ($this->delegationTypeModel->update($id, $name, $percentage)) {
                flash('success', 'Delegation type updated successfully.');
            } else {
                flash('error', 'Failed to update delegation type.');
            }
             redirect('delegation-types');
        } else {
             redirect('delegation-types');
        }
    }


    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (empty($id)) {
                flash('error', 'Invalid ID.');
                redirect('delegation-types');
                return;
            }

            if ($this->delegationTypeModel->delete($id)) {
                flash('success', 'Delegation type deleted successfully.');
            } else {
                flash('error', 'Failed to delete delegation type. It might be in use.');
            }
            redirect('delegation-types');
        } else {
            redirect('delegation-types');
        }
    }
} 