<?php

namespace App\Controllers\Calls;

use App\Core\Controller;
use Exception;

class AssignmentsController extends Controller
{
    private $driverModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'انتهت صلاحية الجلسة', 'redirect' => BASE_PATH . '/auth/login'], 401);
            exit;
        }
        $this->driverModel = $this->model('driver/Driver');
    }

    public function assign()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['driver_id']) || empty($_POST['to_user_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'بيانات الطلب غير صالحة.'], 400);
            return;
        }

        try {
            $driverId = $_POST['driver_id'];
            
            $result = $this->driverModel->assignDriver(
                $driverId,
                $_SESSION['user_id'],
                $_POST['to_user_id'],
                $_POST['note'] ?? ''
            );

            if ($result) {
                if (isset($_SESSION['locked_driver_id']) && $_SESSION['locked_driver_id'] == $driverId) {
                    unset($_SESSION['locked_driver_id']);
                }
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