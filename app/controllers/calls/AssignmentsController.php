<?php

namespace App\Controllers\Calls;

use App\Core\Controller;

class AssignmentsController extends Controller
{
    private $assignmentsModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        $this->assignmentsModel = $this->model('Assignment/Assignment');
    }

    public function assign()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['driver_id']) || empty($_POST['to_user_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'بيانات الطلب غير صالحة.'], 400);
            return;
        }

        try {
            $result = $this->assignmentsModel->assignDriver([
                'driver_id' => $_POST['driver_id'],
                'from_user_id' => $_SESSION['user_id'],
                'to_user_id' => $_POST['to_user_id'],
                'note' => $_POST['note'] ?? ''
            ]);

            if ($result) {
                unset($_SESSION['locked_driver_id']);
                $this->sendJsonResponse(['success' => true, 'message' => 'تم تحويل السائق بنجاح.']);
            } else {
                throw new Exception('فشل تحويل السائق في قاعدة البيانات.');
            }

        } catch (Exception $e) {
            error_log("Assign Driver Controller Error: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'حدث خطأ في الخادم أثناء محاولة تحويل السائق.'], 500);
        }
    }

    public function markAsSeen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'طريقة طلب غير صحيحة'], 405);
            return;
        }

        try {
            if (empty($_POST['assignment_id'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'معرف الإحالة مطلوب.'], 400);
                return;
            }
            $result = $this->assignmentsModel->markAssignmentAsSeen($_POST['assignment_id']);
            $this->sendJsonResponse(['success' => $result]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة المشاهدة'], 500);
        }
    }
} 