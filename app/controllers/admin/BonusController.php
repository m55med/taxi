<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Admin\BonusModel;

class BonusController extends Controller {
    private $bonusModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            Auth::isLoggedIn() ? redirect('/unauthorized') : redirect('/auth/login');
        }
        
        $this->bonusModel = $this->model('admin/BonusModel');
    }

    public function index() {
        $data = [
            'users' => $this->bonusModel->getAllUsers(),
            'bonuses' => $this->bonusModel->getGrantedBonuses()
        ];
        $this->view('admin/bonus/index', $data);
    }

    public function grant() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $bonus_month_raw = filter_input(INPUT_POST, 'bonus_month', FILTER_UNSAFE_RAW);
            $month_year = explode('-', $bonus_month_raw);

            $data = [
                'user_id' => filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT),
                'bonus_percent' => filter_input(INPUT_POST, 'bonus_percent', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'bonus_year' => $month_year[0] ?? null,
                'bonus_month' => $month_year[1] ?? null,
                'reason' => htmlspecialchars($_POST['reason'] ?? '', ENT_QUOTES, 'UTF-8'),
                'granted_by' => $_SESSION['user_id']
            ];

            if (empty($data['user_id']) || empty($data['bonus_percent']) || empty($data['bonus_year'])) {
                flash('bonus_message', 'Please fill out all required fields.', 'error');
                redirect('/admin/bonus');
            }
            
            // Check if bonus already exists for that month
            if ($this->bonusModel->bonusExists($data['user_id'], $data['bonus_year'], $data['bonus_month'])) {
                flash('bonus_message', 'Failed to grant bonus. The employee has already received a bonus for this month.', 'error');
                redirect('/admin/bonus');
            }
            
            if ($this->bonusModel->addBonus($data)) {
                flash('bonus_message', 'Bonus granted successfully.');
                redirect('/admin/bonus');
            } else {
                flash('bonus_message', 'Failed to grant bonus due to a database error.', 'error');
                redirect('/admin/bonus');
            }
        } else {
            redirect('/admin/bonus');
        }
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($id)) {
            if ($this->bonusModel->deleteBonus($id)) {
                flash('bonus_message', 'Bonus entry deleted successfully.', 'success');
            } else {
                flash('bonus_message', 'Failed to delete bonus entry.', 'error');
            }
            redirect('/admin/bonus');
        } else {
            // Prevent direct access to delete URL
            redirect('/admin/bonus');
        }
    }
} 