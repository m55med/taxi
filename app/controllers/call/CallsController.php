<?php

namespace App\Controllers\Call;

use App\Core\Database;

class CallsController extends BaseCallController
{
    private $callsModel;
    private $documentsModel;
    private $assignmentsModel;

    public function __construct()
    {
        parent::__construct();
        $this->callsModel = $this->model('call/Calls');
        $this->documentsModel = $this->model('call/Documents');
        $this->assignmentsModel = $this->model('call/Assignments');
    }

    public function index()
    {
        // التحقق من تجاوز معدل المكالمات
        $waitTime = $this->checkRateLimit();
        if ($waitTime > 0) {
            $data = [
                'rate_limit_exceeded' => true,
                'wait_time' => $waitTime,
                'driver' => null,
                'users' => [],
                'call_history' => [],
                'required_documents' => [],
                'nationalities' => [],
                'call_status_text' => [],
                'today_calls_count' => $this->callsModel->getTodayCallsCount(),
                'total_pending_calls' => $this->callsModel->getTotalPendingCalls()
            ];
            
            $this->view('call/index', $data);
            exit();
        }

        // إحصائيات اليوم والاجماليات
        $today_calls_count = $this->callsModel->getTodayCallsCount();
        $total_pending_calls = $this->callsModel->getTotalPendingCalls();

        // جلب السائق التالي حسب الأولوية أو بالبحث بالهاتف
        $driver = null;
        $skippedDriverId = $_SESSION['skipped_driver_id'] ?? null;

        // أولاً، حرر دائمًا السائق الذي كان مقفلاً في الجلسة السابقة
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callsModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }

        // ثانيًا، تحقق مما إذا كان هناك طلب لسائق معين عبر الهاتف
        if (isset($_GET['phone']) && !empty($_GET['phone'])) {
            $driver = $this->callsModel->findAndLockDriverByPhone($_GET['phone']);
            
            // إذا لم نتمكن من قفل السائق المطلوب (لأنه مقفول من قبل موظف آخر)
            if (!$driver) {
                $_SESSION['error'] = 'السائق المطلوب أصبح يعمل عليه موظف آخر. تم جلب السائق التالي.';
                // حاول جلب السائق التالي في الطابور بدلاً منه
                $driver = $this->callsModel->findAndLockNextDriver($skippedDriverId);
            }
        } else {
            // إذا لم يتم طلب سائق معين، فقط اجلب السائق التالي في الطابور
            $driver = $this->callsModel->findAndLockNextDriver($skippedDriverId);
        }

        // ثالثًا، إذا حصلنا على سائق، قم بتسجيل القفل الجديد في الجلسة
        if ($driver) {
            $_SESSION['locked_driver_id'] = $driver['id'];

            // تسجيل الطابع الزمني للمكالمة الجديدة لتحديد المعدل
            $_SESSION['call_timestamps'][] = time();

            // إذا كان هذا السائق من تحويل، قم بتحديث حالة المشاهدة
            if (!empty($driver['assignment_id'])) {
                $this->assignmentsModel->markAssignmentAsSeen($driver['assignment_id']);
            }
        }

        // رابعاً، قم بإزالة معرّف السائق المتخطى من الجلسة بعد استخدامه
        if (isset($_SESSION['skipped_driver_id'])) {
            unset($_SESSION['skipped_driver_id']);
        }

        // جلب الموظفين لنموذج التحويل
        $users = $this->callsModel->getUsers();
        $countries = $this->callsModel->getCountries();

        // سجل المكالمات والمستندات المطلوبة
        $call_history = [];
        $document_types = $this->documentsModel->getAllTypes(); // Always get all document types
        $required_documents = []; // Initialize as empty

        if ($driver) {
            $call_history = $this->callsModel->getCallHistory($driver['id']);
            // If a driver exists, get their specific document statuses
            $required_documents = $this->documentsModel->getDriverDocuments($driver['id']);
        }

        // بيانات ثابتة
        $call_status_text = [
            'no_answer' => 'لم يتم الرد',
            'answered' => 'تم الرد',
            'busy' => 'مشغول',
            'not_available' => 'غير متاح',
            'wrong_number' => 'رقم خاطئ',
            'rescheduled' => 'معاد جدولته'
        ];

        $data = [
            'driver' => $driver,
            'users' => $users,
            'countries' => $countries,
            'call_history' => $call_history,
            'document_types' => $document_types,
            'required_documents' => $required_documents,
            'call_status_text' => $call_status_text,
            'today_calls_count' => $today_calls_count,
            'total_pending_calls' => $total_pending_calls
        ];

        $this->view('call/index', $data);
    }

    public function skip($driverId = null)
    {
        if ($driverId && isset($_SESSION['user_id'])) {
            $this->callsModel->releaseDriverHold($driverId);
            // Add the skipped driver ID to the session to prevent immediate re-fetching
            $_SESSION['skipped_driver_id'] = $driverId;
        }
        // Redirect to the main call page to get a new driver
        header("Location: " . BASE_PATH . "/call");
        exit();
    }

    public function record()
    {
        header('Content-Type: application/json');
        ob_clean();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            return;
        }

        try {
            $driverId = $_POST['driver_id'];
            $callStatus = $_POST['call_status'];
            $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

            // إذا كان الوضع "لم يتم الرد"، نقوم بجدولة المكالمة تلقائياً بعد ساعة
            if ($callStatus === 'no_answer') {
                $_POST['next_call_at'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
            }

            // إذا تم تحديد موعد المكالمة القادمة يدوياً
            $nextCallAt = null;
            if (isset($_POST['next_call_at']) && !empty($_POST['next_call_at'])) {
                $nextCallAt = $_POST['next_call_at'];
            }

            $result = $this->callsModel->recordCall([
                'driver_id' => $driverId,
                'user_id' => $_SESSION['user_id'],
                'status' => $callStatus,
                'notes' => $notes,
                'next_call_at' => $nextCallAt
            ]);

            if ($result) {
                // تحديث حالة السائق في الجدول الرئيسي
                $newStatus = $this->getNewDriverStatus($callStatus);
                if ($newStatus) {
                    $this->callsModel->updateDriverStatus($driverId, $newStatus);
                }

                // تحرير القفل عن السائق
                $this->callsModel->releaseDriverHold($driverId);
                unset($_SESSION['locked_driver_id']);

                echo json_encode(['success' => true]);
                return;
            }

            echo json_encode(['success' => false, 'message' => 'فشل في تسجيل المكالمة']);

        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تسجيل المكالمة']);
        }
    }

    public function getHistory($driverId)
    {
        if (!$driverId) {
            http_response_code(400);
            echo json_encode(['error' => 'معرف السائق غير صحيح']);
            exit();
        }

        $history = $this->callsModel->getCallHistory($driverId);
        echo json_encode($history);
        exit();
    }

    public function releaseHold()
    {
        // تم استدعاؤها بواسطة sendBeacon
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callsModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }
    }

    public function updateDriverInfo()
    {
        // This method assumes the existence of sendJsonResponse in a parent controller
        header('Content-Type: application/json');
        ob_clean();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'طريقة طلب غير صحيحة'], 405);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($payload['driver_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'بيانات غير صالحة.'], 400);
            return;
        }

        try {
            $result = $this->callsModel->updateDriverInfo($payload);

            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'تم تحديث بيانات السائق بنجاح.']);
            } else {
                throw new \Exception('فشل تحديث بيانات السائق في قاعدة البيانات.');
            }
        } catch (\Exception $e) {
            error_log('Update Driver Info Error: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'حدث خطأ في الخادم أثناء تحديث البيانات.'], 500);
        }
    }

    public function updateDocuments()
    {
        header('Content-Type: application/json');
        
        $payload = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['driver_id']) || !isset($payload['documents'])) {
            $this->sendJsonResponse(['error' => 'بيانات غير صالحة.'], 400);
            return;
        }

        try {
            $result = $this->documentsModel->updateDriverDocuments(
                $payload['driver_id'],
                $payload['documents']
            );

            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'تم تحديث المستندات بنجاح.']);
            } else {
                throw new Exception('فشل تحديث المستندات في قاعدة البيانات.');
            }
        } catch (Exception $e) {
            error_log('Update Documents Error: ' . $e->getMessage());
            $this->sendJsonResponse(['error' => 'حدث خطأ في الخادم أثناء تحديث المستندات.'], 500);
        }
    }
} 