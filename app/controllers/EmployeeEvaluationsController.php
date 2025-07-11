<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User\User;
use App\Models\Admin\EmployeeEvaluation;
use PDOException;

class EmployeeEvaluationsController extends Controller {

    private $userModel;
    private $evaluationModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->evaluationModel = new EmployeeEvaluation();
        // A 'team_leader' or 'admin' can access this page.
        // We can create a new permission or reuse an existing one.
        // For now, let's assume a permission like 'manage_evaluations' is required.
        // The authorize method should be flexible enough.
    }

    /**
     * Display the main page for managing employee evaluations.
     */
    public function index() {
        $this->authorize('EmployeeEvaluations/index');

        $users = $this->userModel->getAllUsers();
        $evaluations = $this->evaluationModel->findAllWithDetails();

        $this->view('admin/employee_evaluations/index', [
            'users' => $users,
            'evaluations' => $evaluations
        ]);
    }

    /**
     * Handle the creation of a new employee evaluation.
     */
    public function create() {
        $this->authorize('EmployeeEvaluations/index');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT),
                'evaluator_id' => $_SESSION['user_id'], // The logged-in user is the evaluator
                'score' => filter_input(INPUT_POST, 'score', FILTER_VALIDATE_FLOAT),
                'comment' => filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'month' => filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT),
                'year' => filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT)
            ];

            // Basic validation
            if (empty($data['user_id']) || $data['score'] === false || $data['score'] < 0 || $data['score'] > 10 || empty($data['month']) || empty($data['year'])) {
                flash('error', 'Please fill in all required fields correctly.');
                redirect('/employee-evaluations');
                return;
            }

            try {
                if ($this->evaluationModel->create($data)) {
                    flash('success', 'Employee evaluation has been added successfully.');
                } else {
                    flash('error', 'Failed to add employee evaluation.');
                }
            } catch (PDOException $e) {
                // Check for duplicate entry error
                if ($e->errorInfo[1] == 1062) { // 1062 is the MySQL error code for duplicate entry
                    flash('error', 'An evaluation for this user already exists for the selected month and year.');
                } else {
                    flash('error', 'An unexpected database error occurred. ' . $e->getMessage());
                }
            }
        }

        redirect('/employee-evaluations');
    }

    /**
     * Handle the deletion of an employee evaluation.
     */
    public function delete() {
        $this->authorize('EmployeeEvaluations/index');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            
            if ($id) {
                $evaluation = $this->evaluationModel->findById($id);
                // Optional: Add an extra check to ensure only the person who created it or an admin can delete.
                // For now, any user with 'manage_evaluations' can delete.
                if ($evaluation) {
                    if ($this->evaluationModel->deleteById($id)) {
                        flash('success', 'The evaluation has been deleted successfully.');
                    } else {
                        flash('error', 'Failed to delete the evaluation.');
                    }
                } else {
                    flash('error', 'Evaluation not found.');
                }
            }
        }
        redirect('/employee-evaluations');
    }
} 