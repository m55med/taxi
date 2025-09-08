<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Admin\PointsModel;
class PointsController extends Controller
{
    private $pointsModel;

    public function __construct()
    {
        // Use the centralized admin check for consistency.
        Auth::checkAdmin();
        $this->pointsModel = $this->model('Admin/PointsModel');
    }

    public function index()
    {
        $data = [
            'ticket_codes' => $this->pointsModel->getAllTicketCodes(),
            'ticket_points' => $this->pointsModel->getTicketCodePoints(),
            'call_points' => $this->pointsModel->getCallPoints()
        ];
        $this->view('admin/points/index', $data);
    }

    public function setTicketPoints()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize specific inputs
            $code_id = filter_input(INPUT_POST, 'code_id', FILTER_SANITIZE_NUMBER_INT);
            $points = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_FLOAT);
            $valid_from = filter_input(INPUT_POST, 'valid_from', FILTER_SANITIZE_STRING);
            $is_vip = isset($_POST['is_vip']) ? 1 : 0;

            if ($points === false) {
                flash('points_message', 'Invalid points value. Please enter a valid number.', 'error');
                redirect('/admin/points');
                return;
            }

            $data = [
                'code_id' => $code_id,
                'is_vip' => $is_vip,
                'points' => $points,
                'valid_from' => $valid_from
            ];

            if (empty($data['code_id']) || !isset($data['points']) || empty($data['valid_from'])) {
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

    public function setCallPoints()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize specific inputs
            $points = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_FLOAT);
            $call_type = filter_input(INPUT_POST, 'call_type', FILTER_SANITIZE_STRING);
            $valid_from = filter_input(INPUT_POST, 'valid_from', FILTER_SANITIZE_STRING);

            if ($points === false) {
                flash('points_message', 'Invalid points value. Please enter a valid number.', 'error');
                redirect('/admin/points');
                return;
            }

            $data = [
                'points' => $points,
                'call_type' => $call_type,
                'valid_from' => $valid_from
            ];

            if (!isset($data['points']) || empty($data['valid_from']) || empty($data['call_type'])) {
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