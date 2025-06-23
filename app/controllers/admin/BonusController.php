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
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $month_year = explode('-', $_POST['bonus_month']);
            $bonus_year = $month_year[0];
            $bonus_month = $month_year[1];

            $data = [
                'user_id' => trim($_POST['user_id']),
                'bonus_percent' => trim($_POST['bonus_percent']),
                'bonus_year' => $bonus_year,
                'bonus_month' => $bonus_month,
                'reason' => trim($_POST['reason']),
                'granted_by' => $_SESSION['user_id']
            ];

            if (empty($data['user_id']) || empty($data['bonus_percent']) || empty($data['bonus_year'])) {
                flash('bonus_message', 'يرجى ملء جميع الحقول المطلوبة.', 'bg-red-500');
                redirect('/admin/bonus');
            }
            
            if ($this->bonusModel->addBonus($data)) {
                flash('bonus_message', 'تم منح البونص بنجاح.');
                redirect('/admin/bonus');
            } else {
                // This could be because of the UNIQUE constraint (bonus already exists)
                flash('bonus_message', 'فشل منح البونص. قد يكون الموظف حصل على بونص لهذا الشهر بالفعل.', 'bg-red-500');
                redirect('/admin/bonus');
            }
        } else {
            redirect('/admin/bonus');
        }
    }
} 