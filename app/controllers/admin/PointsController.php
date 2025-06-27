<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Admin\PointsModel;

class PointsController extends Controller {
    private $pointsModel;

    public function __construct() {
        // Auth check for admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            Auth::isLoggedIn() ? redirect('/unauthorized') : redirect('/auth/login');
        }
        
        $this->pointsModel = $this->model('admin/PointsModel');
    }

    public function index() {
        $data = [
            'ticket_codes' => $this->pointsModel->getAllTicketCodes(),
            'ticket_points' => $this->pointsModel->getTicketCodePoints(),
            'call_points' => $this->pointsModel->getCallPoints()
        ];
        $this->view('admin/points/index', $data);
    }

    public function setTicketPoints() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'code_id' => trim($_POST['code_id']),
                'is_vip' => isset($_POST['is_vip']) ? 1 : 0,
                'points' => trim($_POST['points']),
                'valid_from' => trim($_POST['valid_from'])
            ];

            if (empty($data['code_id']) || empty($data['points']) || empty($data['valid_from'])) {
                flash('points_message', 'Please fill in all required fields.', 'error');
                redirect('/admin/points');
            }
            
            $this->pointsModel->endPreviousPointRule('ticket_code_points', [
                'code_id' => $data['code_id'], 
                'is_vip' => $data['is_vip']
            ], $data['valid_from']);


            if ($this->pointsModel->addTicketCodePoint($data)) {
                flash('points_message', 'Ticket code points set successfully.');
                redirect('/admin/points');
            } else {
                flash('points_message', 'Something went wrong, please try again.', 'error');
                redirect('/admin/points');
            }
        } else {
            redirect('/admin/points');
        }
    }

    public function setCallPoints() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'points' => trim($_POST['points']),
                'call_type' => trim($_POST['call_type']),
                'valid_from' => trim($_POST['valid_from'])
            ];
            
            if (empty($data['points']) || empty($data['valid_from']) || empty($data['call_type'])) {
                flash('points_message', 'Please fill in all required fields.', 'error');
                redirect('/admin/points');
            }

            if (!in_array($data['call_type'], ['incoming', 'outgoing'])) {
                flash('points_message', 'Invalid call type specified.', 'error');
                redirect('/admin/points');
            }

            $this->pointsModel->endPreviousPointRule('call_points', ['call_type' => $data['call_type']], $data['valid_from']);
            
            if ($this->pointsModel->addCallPoint($data)) {
                flash('points_message', 'Call points set successfully.');
                redirect('/admin/points');
            } else {
                flash('points_message', 'Something went wrong, please try again.', 'error');
                redirect('/admin/points');
            }
        } else {
            redirect('/admin/points');
        }
    }
} 