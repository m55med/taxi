<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Admin\BonusModel;

class BonusController extends Controller
{
    private $bonusModel;

    public function __construct()
    {
        // Broaden access control, will handle specific permissions in methods
        if (!Auth::isLoggedIn()) {
            redirect('/auth/login');
        }

        $this->bonusModel = $this->model('Admin/BonusModel');
    }

    public function index()
    {
        $user_role = $_SESSION['user']['role_name'] ?? '';
        $data = [
            'users' => $this->bonusModel->getAllUsers(),
            'bonuses' => $this->bonusModel->getGrantedBonuses(),
            'settings' => $this->bonusModel->getBonusSettings(),
            'is_admin' => ($user_role === 'admin'),
            'can_manage_settings' => in_array($user_role, ['admin', 'developer'])
        ];
    
        $this->view('admin/bonus/index', $data);
    }
    

    public function settings()
    {
        $data = [
            'settings' => $this->bonusModel->getBonusSettings()
        ];
        $this->view('admin/bonus/settings', $data);
    }

    public function updateSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/bonus');
        }

        $fields = [
            'min_bonus_percent' => FILTER_VALIDATE_FLOAT,
            'max_bonus_percent' => FILTER_VALIDATE_FLOAT,
            'predefined_bonus_1' => FILTER_VALIDATE_FLOAT,
            'predefined_bonus_2' => FILTER_VALIDATE_FLOAT,
            'predefined_bonus_3' => FILTER_VALIDATE_FLOAT,
        ];
        $data = [];
        foreach ($fields as $field => $filter) {
            $value = filter_input(INPUT_POST, $field, $filter);
            if ($value === false || $value < 0) {
                flash('bonus_settings_message', "Invalid value provided for " . str_replace('_', ' ', $field) . ".", 'error');
                redirect('/admin/bonus/settings');
                return;
            }
            $data[$field] = $value;
        }

        if ($data['min_bonus_percent'] > $data['max_bonus_percent']) {
            flash('bonus_settings_message', "Minimum bonus cannot be greater than maximum bonus.", 'error');
            redirect('/admin/bonus/settings');
            return;
        }

        $data['updated_by'] = $_SESSION['user_id'];

        if ($this->bonusModel->updateBonusSettings($data)) {
            flash('bonus_settings_message', 'Bonus settings updated successfully.', 'success');
        } else {
            $errorInfo = $this->bonusModel->getLastError();
            $errorMessage = 'Failed to update settings.';
            if ($errorInfo && isset($errorInfo[2])) {
                $errorMessage .= ' Database Error: ' . $errorInfo[2];
            } else {
                $errorMessage .= ' An unknown database error occurred.';
            }
            flash('bonus_settings_message', $errorMessage, 'error');
        }
        redirect('/admin/bonus/settings');
    }

    public function grant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/bonus');
            return;
        }

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

        // --- New Validation Logic ---
        $settings = $this->bonusModel->getBonusSettings();
        $is_admin = ($_SESSION['user']['role_name'] === 'admin');

        if ($is_admin) {
            // Admin validation: check against min/max
            if ($data['bonus_percent'] < $settings['min_bonus_percent'] || $data['bonus_percent'] > $settings['max_bonus_percent']) {
                flash('bonus_message', "Bonus must be between {$settings['min_bonus_percent']}% and {$settings['max_bonus_percent']}%.", 'error');
                redirect('/admin/bonus');
                return;
            }
        } else {
            // Non-admin validation: check against predefined values
            $allowed_bonuses = [
                $settings['predefined_bonus_1'],
                $settings['predefined_bonus_2'],
                $settings['predefined_bonus_3']
            ];
            if (!in_array($data['bonus_percent'], $allowed_bonuses)) {
                flash('bonus_message', 'Invalid bonus value selected.', 'error');
                redirect('/admin/bonus');
                return;
            }
        }
        // --- End New Validation Logic ---

        if (empty($data['user_id']) || empty($data['bonus_percent']) || empty($data['bonus_year'])) {
            flash('bonus_message', 'Please fill out all required fields.', 'error');
            redirect('/admin/bonus');
        }

        // Check if bonus already exists for that month
        if ($this->bonusModel->bonusExists($data['user_id'], $data['bonus_year'], $data['bonus_month'])) {
            flash('bonus_message', 'Failed to grant bonus. The employee has already received a bonus for this month.', 'error');
            redirect('/admin/bonus');
            return;
        }

        if ($this->bonusModel->addBonus($data)) {
            flash('bonus_message', 'Bonus granted successfully.', 'success');
            redirect('/admin/bonus');
        } else {
            // --- DEBUGGING ---
            $errorInfo = $this->bonusModel->getLastError();
            $errorMessage = 'Failed to grant bonus due to a database error.';
            if ($errorInfo) {
                $errorMessage .= ' DB Error: ' . implode(' | ', $errorInfo);
            }
            flash('bonus_message', $errorMessage, 'error');
            redirect('/admin/bonus');
            // --- END DEBUGGING ---
        }
    }

    public function delete($id = null)
    {
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