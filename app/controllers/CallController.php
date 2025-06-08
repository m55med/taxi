<?php

class CallController extends Controller
{
    protected $db;
    protected $callModel;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->callModel = $this->model('Call');
    }

    public function index()
    {
        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit();
        }

        // --- نظام تحديد معدل المكالمات ---
        if (!isset($_SESSION['call_timestamps'])) {
            $_SESSION['call_timestamps'] = [];
        }

        $limit = 10; // 10 مكالمات
        $window = 60; // خلال 60 ثانية
        $currentTime = time();

        // تصفية الطوابع الزمنية التي تجاوزت المدة المحددة
        $_SESSION['call_timestamps'] = array_filter(
            $_SESSION['call_timestamps'],
            fn($ts) => ($currentTime - $ts) < $window
        );

        // التحقق من تجاوز الحد
        if (count($_SESSION['call_timestamps']) >= $limit) {
            $oldestTimestamp = min($_SESSION['call_timestamps']);
            $waitTime = ($oldestTimestamp + $window) - $currentTime;

            $data = [
                'rate_limit_exceeded' => true,
                'wait_time' => $waitTime,
                'driver' => null,
                'users' => [],
                'call_history' => [],
                'required_documents' => [],
                'nationalities' => [],
                'call_status_text' => [],
                'today_calls_count' => $this->callModel->getTodayCallsCount(),
                'total_pending_calls' => $this->callModel->getTotalPendingCalls()
            ];
            
            $this->view('call/index', $data);
            exit();
        }
        // --- نهاية نظام تحديد المعدل ---

        // إحصائيات اليوم والاجماليات
        $today_calls_count = $this->callModel->getTodayCallsCount();
        $total_pending_calls = $this->callModel->getTotalPendingCalls();

        // جلب السائق التالي حسب الأولوية أو بالبحث بالهاتف
        $driver = null;

        // أولاً، حرر دائمًا السائق الذي كان مقفلاً في الجلسة السابقة
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }

        // ثانيًا، تحقق مما إذا كان هناك طلب لسائق معين عبر الهاتف
        if (isset($_GET['phone']) && !empty($_GET['phone'])) {
            $driver = $this->callModel->findAndLockDriverByPhone($_GET['phone']);
            
            // إذا لم نتمكن من قفل السائق المطلوب (لأنه مقفول من قبل موظف آخر)
            if (!$driver) {
                $_SESSION['error'] = 'السائق المطلوب أصبح يعمل عليه موظف آخر. تم جلب السائق التالي.';
                // حاول جلب السائق التالي في الطابور بدلاً منه
                $driver = $this->callModel->findAndLockNextDriver();
            }
        } else {
            // إذا لم يتم طلب سائق معين، فقط اجلب السائق التالي في الطابور
            $driver = $this->callModel->findAndLockNextDriver();
        }

        // ثالثًا، إذا حصلنا على سائق، قم بتسجيل القفل الجديد في الجلسة
        if ($driver) {
            $_SESSION['locked_driver_id'] = $driver['id'];

            // تسجيل الطابع الزمني للمكالمة الجديدة لتحديد المعدل
            $_SESSION['call_timestamps'][] = time();

            // إذا كان هذا السائق من تحويل، قم بتحديث حالة المشاهدة
            if (!empty($driver['assignment_id'])) {
                $this->callModel->markAssignmentAsSeen($driver['assignment_id']);
            }
        }

        // جلب الموظفين لنموذج التحويل
        $users = $this->callModel->getUsers();

        // سجل المكالمات والمستندات المطلوبة إن وجد سائق
        $call_history = [];
        $required_documents = [];
        if ($driver) {
            $call_history = $this->callModel->getCallHistory($driver['id']);

            // جلب المستندات المطلوبة وحالتها
            $documentsQuery = "
                SELECT 
                    dt.id,
                    dt.name AS document_name,
                    COALESCE(ddr.status, 'missing') AS status,
                    ddr.note,
                    ddr.updated_at,
                    u.username AS updated_by_name
                FROM document_types dt
                LEFT JOIN driver_documents_required ddr 
                    ON dt.id = ddr.document_type_id AND ddr.driver_id = :driver_id
                LEFT JOIN users u ON ddr.updated_by = u.id
                ORDER BY dt.name ASC
            ";
            $stmt = $this->db->prepare($documentsQuery);
            $stmt->execute([':driver_id' => $driver['id']]);
            $required_documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

            $result = $this->callModel->recordCall([
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
                    $this->callModel->updateDriverStatus($driverId, $newStatus);
                }

                // تحرير القفل عن السائق
                $this->callModel->releaseDriverHold($driverId);
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

    private function getNewDriverStatus($callStatus)
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

        $history = $this->callModel->getCallHistory($driverId);
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

            $result = $this->callModel->assignDriver([
                'driver_id' => $driverId,
                'from_user_id' => $_SESSION['user_id'],
                'to_user_id' => $toUserId,
                'note' => $note
            ]);

            if ($result) {
                // تحديث حالة السائق إلى 'transferred' عند التحويل
                $this->callModel->updateDriverStatus($driverId, 'transferred');
                
                // تسجيل المكالمة كتحويل
                $this->callModel->recordCall([
                    'driver_id' => $driverId,
                    'user_id' => $_SESSION['user_id'],
                    'status' => 'transferred',
                    'notes' => "تم التحويل إلى موظف آخر. " . $note,
                    'next_call_at' => null
                ]);

                // تحرير القفل عن السائق بعد تحويله
                $this->callModel->releaseDriverHold($driverId);
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
            $result = $this->callModel->markAssignmentAsSeen($assignmentId);
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
            $this->callModel->releaseDriverHold($_SESSION['locked_driver_id']);
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

        $documents = $this->callModel->getRequiredDocuments($driverId);
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