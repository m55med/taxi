<?php

namespace App\Controllers;

use App\Controllers\Call\BaseCallController;

class CallController extends BaseCallController
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

        // سجل المكالمات والمستندات المطلوبة إن وجد سائق
        $call_history = [];
        $required_documents = [];
        if ($driver) {
            $call_history = $this->callsModel->getCallHistory($driver['id']);
            $required_documents = $this->documentsModel->getDriverDocuments($driver['id']);
        }

        // بيانات ثابتة
        $nationalities = [
            'سعودي', 'يمني', 'مصري', 'سوداني', 'باكستاني',
            'هندي', 'بنجلاديشي', 'فلبيني', 'أردني', 'سوري',
            'فلسطيني', 'لبناني', 'عراقي', 'مغربي', 'تونسي',
            'جزائري', 'صومالي', 'إثيوبي', 'إريتري', 'أخرى'
        ];
        
        $call_status_text = [
            'no_answer' => 'لم يتم الرد',
            'answered' => 'تم الرد',
            'busy' => 'مشغول',
            'not_available' => 'غير متاح',
            'wrong_number' => 'رقم خاطئ',
            'rescheduled' => 'معاد جدولته',
            'transferred' => 'تم التحويل'
        ];

        $data = [
            'driver' => $driver,
            'users' => $users,
            'call_history' => $call_history,
            'required_documents' => $required_documents,
            'document_types' => $this->documentsModel->getAllTypes(),
            'nationalities' => $nationalities,
            'call_status_text' => $call_status_text,
            'today_calls_count' => $today_calls_count,
            'total_pending_calls' => $total_pending_calls
        ];

        $this->view('call/index', $data);
    }

    public function recordCall()
    {
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

    protected function getNewDriverStatus($callStatus)
    {
        $statusMap = [
            'no_answer' => 'pending',
            'busy' => 'pending',
            'not_available' => 'pending',
            'wrong_number' => 'blocked',
            'rescheduled' => 'pending',
            'answered' => 'waiting_chat',
            'transferred' => 'transferred'
        ];

        return isset($statusMap[$callStatus]) ? $statusMap[$callStatus] : null;
    }

    public function getCallHistory($driverId)
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

    public function assignDriver()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            return;
        }

        try {
            $driverId = $_POST['driver_id'];
            $toUserId = $_POST['to_user_id'];
            $note = isset($_POST['note']) ? $_POST['note'] : '';

            $result = $this->callsModel->assignDriver([
                'driver_id' => $driverId,
                'from_user_id' => $_SESSION['user_id'],
                'to_user_id' => $toUserId,
                'note' => $note
            ]);

            if ($result) {
                // تحديث حالة السائق إلى 'transferred' عند التحويل
                $this->callsModel->updateDriverStatus($driverId, 'transferred');
                
                // تسجيل المكالمة كتحويل
                $this->callsModel->recordCall([
                    'driver_id' => $driverId,
                    'user_id' => $_SESSION['user_id'],
                    'status' => 'transferred',
                    'notes' => "تم التحويل إلى موظف آخر. " . $note,
                    'next_call_at' => null
                ]);

                // تحرير القفل عن السائق بعد تحويله
                $this->callsModel->releaseDriverHold($driverId);
                unset($_SESSION['locked_driver_id']);
            }

            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحويل السائق']);
        }
    }

    public function markAsSeen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            return;
        }

        try {
            $assignmentId = $_POST['assignment_id'];
            $result = $this->assignmentsModel->markAssignmentAsSeen($assignmentId);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة المشاهدة']);
        }
    }

    public function releaseHold()
    {
        // تم استدعاؤها بواسطة sendBeacon
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callsModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }
        // لا ترسل أي شيء كرد، لأن sendBeacon لا يتوقع ردًا
    }

    public function getRequiredDocuments($driverId)
    {
        if (!$driverId) {
            http_response_code(400);
            echo json_encode(['error' => 'معرف السائق غير صحيح']);
            exit();
        }

        $documents = $this->documentsModel->getDriverDocuments($driverId);
        echo json_encode($documents);
        exit();
    }

    public function updateDocuments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            return;
        }

        try {
            $driverId = $_POST['driver_id'];
            $documents = isset($_POST['documents']) ? $_POST['documents'] : [];

            // تحديث حالة المستندات في قاعدة البيانات
            $updateQuery = "
                UPDATE driver_documents_required 
                SET status = 'missing',
                    updated_at = NOW(),
                    updated_by = :user_id 
                WHERE driver_id = :driver_id
            ";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([
                ':driver_id' => $driverId,
                ':user_id' => $_SESSION['user_id']
            ]);

            if (!empty($documents)) {
                $updateSubmittedQuery = "
                    UPDATE driver_documents_required 
                    SET status = 'submitted',
                        updated_at = NOW(),
                        updated_by = :user_id 
                    WHERE driver_id = :driver_id 
                    AND document_type_id IN (" . implode(',', array_map('intval', $documents)) . ")
                ";
                $stmt = $this->db->prepare($updateSubmittedQuery);
                $stmt->execute([
                    ':driver_id' => $driverId,
                    ':user_id' => $_SESSION['user_id']
                ]);
            }

            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث المستندات']);
        }
    }
}