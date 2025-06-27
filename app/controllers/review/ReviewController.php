<?php

namespace App\Controllers\Review;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;

class ReviewController extends Controller
{
    private $reviewModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        $this->reviewModel = $this->model('review/Review');
        $this->userModel = $this->model('user/User');
    }

    public function index($status = 'waiting_chat')
    {
        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit();
        }

        // جلب الفلاتر من الـ GET
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // جلب السائقين
        $drivers = $this->reviewModel->getWaitingDrivers($filters);
        
        // جلب المستخدمين لنموذج التحويل
        $users = $this->userModel->getActiveUsers();

        // نص حالات السائقين
        $status_text = [
            'waiting_chat' => 'في انتظار المراجعة',
            'completed' => 'مكتمل',
            'reconsider' => 'إعادة النظر'
        ];

        $data = [
            'drivers' => $drivers,
            'users' => $users,
            'status_text' => $status_text
        ];

        $this->view('review/index', $data);
    }

    public function getDriverDetails($driverId)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'غير مصرح']);
            exit;
        }

        $details = $this->reviewModel->getDriverDetails($driverId);
        
        if ($details) {
            echo json_encode($details);
        } else {
            echo json_encode(['error' => 'لم يتم العثور على السائق']);
        }
        exit;
    }

    public function updateDriver()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            exit;
        }

        $data = [
            'driver_id' => $_POST['driver_id'],
            'status' => $_POST['status'],
            'notes' => $_POST['notes'],
            'documents' => $_POST['documents'] ?? []
        ];

        $result = $this->reviewModel->updateDriver($data);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث البيانات']);
        }
        exit;
    }

    public function transferDriver()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            exit;
        }

        $data = [
            'driver_id' => $_POST['driver_id'],
            'to_user_id' => $_POST['to_user_id'],
            'note' => $_POST['note']
        ];

        $result = $this->reviewModel->transferDriver($data);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحويل السائق']);
        }
        exit;
    }

    public function add($type, $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(''); // Redirect home or show error
            return;
        }

        // Basic validation
        if (empty($_POST['review_result'])) {
            flash('review_error', 'Review result is required.', 'alert alert-danger');
            redirect($_SERVER['HTTP_REFERER'] ?? '');
            return;
        }

        $data = [
            'review_result' => $_POST['review_result'],
            'review_notes' => trim($_POST['review_notes'])
        ];

        $userId = $_SESSION['user_id'];
        $result = $this->reviewModel->addReview($type, $id, $userId, $data);

        if ($result) {
            flash('review_success', 'Review added successfully.', 'alert alert-success');
        } else {
            flash('review_error', 'Failed to add review.', 'alert alert-danger');
        }

        // Redirect back to the original entity's page (ticket or driver)
        $redirectInfo = $this->reviewModel->getEntityIdForRedirect($type, $id);
        if ($redirectInfo) {
            if ($redirectInfo['type'] === 'ticket') {
                redirect('tickets/view/' . $redirectInfo['id']);
            } elseif ($redirectInfo['type'] === 'driver') {
                redirect('drivers/details/' . $redirectInfo['id']);
            }
        }
        
        // Fallback redirect
        redirect('');
    }
} 