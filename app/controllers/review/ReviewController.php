<?php

namespace App\Controllers\Review;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;

class ReviewController extends Controller
{
    private $ReviewModel;
    private $UserModel;
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        $this->ReviewModel = $this->model('Review/Review');
        $this->UserModel = $this->model('User/User');
        // Load the category model for use in the add method
        $this->categoryModel = $this->model('Tickets/Category');
    }

    public function index($status = 'waiting_chat')
    {
        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
            header('Location: ' . URLROOT . '/auth/login');
            exit();
        }

        // جلب الفلاتر من الـ GET
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // جلب السائقين
        $drivers = $this->ReviewModel->getWaitingDrivers($filters);

        // جلب المستخدمين لنموذج التحويل
        $users = $this->UserModel->getActiveUsers();

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

        $details = $this->ReviewModel->getDriverDetails($driverId);

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

        $result = $this->ReviewModel->updateDriver($data);

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

        $result = $this->ReviewModel->transferDriver($data);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحويل السائق']);
        }
        exit;
    }

    public function add($reviewable_type, $reviewable_id)
    {
        $allowed_roles = ['Quality', 'Team_leader', 'admin', 'developer'];
        if (!in_array(Auth::getUserRole(), $allowed_roles)) {
            http_response_code(403);

            // Forcing a 403 Forbidden page, consistent with Controller::authorize()
            $debug_info = [
                'required_permission' => 'Role must be one of: ' . implode(', ', $allowed_roles),
                'user_role' => $_SESSION['role_name'] ?? 'Not Set',
                'user_permissions' => '(Not checked, role check failed)'
            ];

            // Pass debug info to the view
            $data['debug_info'] = $debug_info;

            // Use a path relative to the project's entry point (public/index.php)
            require_once __DIR__ . '/../../views/errors/403.php';
            exit;
        }

        // 1. Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Basic validation for the new rating system
            if (!isset($_POST['rating']) || !is_numeric($_POST['rating']) || $_POST['rating'] < 0 || $_POST['rating'] > 100) {
                flash('review_error', 'A valid rating between 0 and 100 is required.', 'alert alert-danger');
                redirect($_SERVER['HTTP_REFERER'] ?? '/');
                return;
            }

            $data = [
                'rating' => (int) $_POST['rating'],
                'review_notes' => trim($_POST['review_notes']),
                'ticket_category_id' => !empty($_POST['ticket_category_id']) ? (int) $_POST['ticket_category_id'] : null,
                'ticket_subcategory_id' => !empty($_POST['ticket_subcategory_id']) ? (int) $_POST['ticket_subcategory_id'] : null,
                'ticket_code_id' => !empty($_POST['ticket_code_id']) ? (int) $_POST['ticket_code_id'] : null,
            ];

            $userId = Auth::getUserId();
            $result = $this->ReviewModel->addReview($reviewable_type, $reviewable_id, $userId, $data);

            if ($result) {
                flash('review_success', 'Review added successfully.', 'alert alert-success');
            } else {
                flash('review_error', 'Failed to add review.', 'alert alert-danger');
            }

            // Redirect back to the original entity's page
            $redirectInfo = $this->ReviewModel->getEntityIdForRedirect($reviewable_type, $reviewable_id);
            if ($redirectInfo) {
                if ($redirectInfo['type'] === 'driver') {
                    redirect('drivers/details/' . $redirectInfo['id']);
                } elseif ($redirectInfo['type'] === 'ticket') {
                    redirect('tickets/view/' . $redirectInfo['id']);
                }
            }

            // Fallback redirect
            redirect('');
            return; // Stop execution after POST handling
        }

        // 2. Handle GET request (displaying the form)
        $item_to_review = $this->ReviewModel->getReviewableItemDetails($reviewable_type, $reviewable_id);

        if (!$item_to_review) {
            // Optionally, set a flash message
            flash('error', 'The item you are trying to review does not exist.', 'error');
            redirect('/');
            return;
        }

        $data = [
            'page_main_title' => 'Add Review',
            'item' => $item_to_review,
            'reviewable_type' => $reviewable_type,
            'reviewable_id' => $reviewable_id,
            'ticket_categories' => $this->categoryModel->getAll(), // Pass categories to the view
        ];

        $this->view('review/add', $data);
    }
}