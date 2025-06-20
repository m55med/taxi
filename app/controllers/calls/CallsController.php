<?php

namespace App\Controllers\Calls;

use App\Core\Database;

class CallsController extends BaseCallController
{
    private $callsModel;
    private $documentsModel;
    private $assignmentsModel;
    private $carTypeModel;
    private $countryModel;
    private $driverModel;

    public function __construct()
    {
        parent::__construct();
        $this->callsModel = $this->model('Calls/Call');
        $this->documentsModel = $this->model('Document/Document');
        $this->assignmentsModel = $this->model('Assignment/Assignment');
        $this->carTypeModel = $this->model('Admin/CarType');
        $this->countryModel = $this->model('Admin/Country');
        $this->driverModel = $this->model('Driver/Driver');
    }

    public function index()
    {
        // =================================================================
        // هذا هو الجزء الرئيسي المسؤول عن جلب السائق وعرض الصفحة
        // =================================================================

        // الخطوة 1: تحرير أي سائق كان محجوزًا لهذا المستخدم في جلسة سابقة ولم تتم معالجته
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callsModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }

        $driver = null;
        $phone = filter_input(INPUT_GET, 'phone', FILTER_SANITIZE_NUMBER_INT);
        $phone = $phone ? ltrim($phone, '0') : null;

        // الخطوة 2: البحث عن سائق. إما عن طريق الهاتف أو جلب السائق التالي في الطابور
        if (!empty($phone)) {
            // البحث عن سائق محدد بالهاتف وحجزه
            $driver = $this->callsModel->findAndLockDriverByPhone($phone);
        } else {
            // جلب السائق التالي المتاح حسب الأولوية وحجزه
            $skippedDriverId = $_SESSION['skipped_driver_id'] ?? null;
            $driver = $this->callsModel->findAndLockNextDriver($skippedDriverId);
            if (isset($_SESSION['skipped_driver_id'])) {
                unset($_SESSION['skipped_driver_id']);
            }
        }

        // الخطوة 3: تحضير جميع البيانات اللازمة للعرض في الصفحة
        $data = [
            'driver' => null, // القيمة الافتراضية في حال عدم وجود سائق
            'users' => $this->callsModel->getUsers(),
            'countries' => $this->countryModel->getAll(),
            'car_types' => $this->carTypeModel->getAll(),
            'document_types' => $this->documentsModel->getAllTypes(),
            'required_documents' => [],
            'call_history' => [],
            'today_calls_count' => $this->callsModel->getTodayCallsCount(),
            'total_pending_calls' => $this->callsModel->getTotalPendingCalls(),
            'call_status_text' => [
                'no_answer' => 'لم يتم الرد',
                'answered' => 'تم الرد',
                'busy' => 'مشغول',
                'not_available' => 'غير متاح',
                'wrong_number' => 'رقم خاطئ',
                'rescheduled' => 'معاد جدولته'
            ]
        ];
        
        // الخطوة 4: إذا تم العثور على سائق، يتم تحديث بيانات العرض وحجز السائق في الجلسة
        if ($driver) {
            $_SESSION['locked_driver_id'] = $driver['id'];
            $data['driver'] = $driver;

            // جلب المستندات المطلوبة. البيانات الآن مفلترة وجاهزة من المودل مباشرة.
            $docs = $this->documentsModel->getDriverDocuments($driver['id']);
            $data['required_documents'] = array_column($docs, null, 'document_type_id');
            
            $data['call_history'] = $this->callsModel->getCallHistory($driver['id']);
        }

        // الخطوة 5: عرض الصفحة مع تمرير كافة البيانات
        $this->view('call/index', $data);
    }

    public function getNextDriver()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
            http_response_code(401);
            return;
        }

        $agentId = $_SESSION['user_id'];
        $country_id = isset($_GET['country_id']) && $_GET['country_id'] !== '' ? (int)$_GET['country_id'] : null;
        $car_type_id = isset($_GET['car_type_id']) && $_GET['car_type_id'] !== '' ? (int)$_GET['car_type_id'] : null;

        $driver = $this->callsModel->getDriver($agentId, $country_id, $car_type_id);

        if ($driver) {
            $required_documents = $this->documentsModel->getDriverDocuments($driver->id);
            $call_history = $this->callsModel->getCallHistory($driver->id);
            echo json_encode([
                'success' => true,
                'driver' => $driver,
                'documents' => $required_documents,
                'history' => $call_history
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'لا يوجد سائقين في قائمة الانتظار حالياً']);
        }
    }

    public function skip($driverId = null)
    {
        if ($driverId && isset($_SESSION['user_id'])) {
            $this->callsModel->releaseDriverHold($driverId);
            // Add the skipped driver ID to the session to prevent immediate re-fetching
            $_SESSION['skipped_driver_id'] = $driverId;
        }
        // Redirect to the main call page to get a new driver
        header("Location: " . BASE_PATH . "/calls");
        exit();
    }

    public function record()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'طريقة طلب غير صحيحة'], 405);
            return;
        }

        try {
            // التحقق من وجود البيانات المطلوبة
            if (!isset($_POST['driver_id']) || !isset($_POST['call_status'])) {
                throw new \Exception('بيانات ناقصة: معرف السائق أو حالة المكالمة مفقودة');
            }

            $driverId = filter_var($_POST['driver_id'], FILTER_VALIDATE_INT);
            if (!$driverId) {
                throw new \Exception('معرف السائق غير صالح');
            }

            $callStatus = $_POST['call_status'];
            $validStatuses = ['no_answer', 'answered', 'busy', 'not_available', 'wrong_number', 'rescheduled'];
            if (!in_array($callStatus, $validStatuses)) {
                throw new \Exception('حالة المكالمة غير صالحة');
            }

            $notes = $_POST['notes'] ?? '';
            /* -- تم إلغاء الإجبارية --
            if (empty($notes)) {
                throw new \Exception('الرجاء إدخال ملاحظات المكالمة');
            }
            */

            $nextCallAt = null;
            if (in_array($callStatus, ['no_answer', 'busy', 'not_available', 'rescheduled'])) {
                if (empty($_POST['next_call_at'])) {
                    throw new \Exception('الرجاء تحديد موعد المكالمة التالية');
                }
                $nextCallAt = $_POST['next_call_at'];
            }

            // تسجيل المكالمة
            $callData = [
                'driver_id' => $driverId,
                'call_by' => $_SESSION['user_id'],
                'call_status' => $callStatus,
                'notes' => $notes,
                'next_call_at' => $nextCallAt
            ];

            $result = $this->callsModel->recordCall($callData);
            if (!$result) {
                throw new \Exception('فشل في تسجيل المكالمة في قاعدة البيانات');
            }

            // تحديث حالة السائق في الجدول الرئيسي
            $newStatus = $this->getNewDriverStatus($callStatus);
            if ($newStatus) {
                $this->driverModel->updateStatus($driverId, $newStatus);
            }

            // تحرير القفل عن السائق
            $this->callsModel->releaseDriverHold($driverId);
            if (isset($_SESSION['locked_driver_id'])) {
                unset($_SESSION['locked_driver_id']);
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'تم تسجيل المكالمة بنجاح'
            ]);

        } catch (\Exception $e) {
            error_log('Call recording error: ' . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    protected function getNewDriverStatus($callStatus)
    {
        switch ($callStatus) {
            case 'answered':
                return 'waiting_chat';
            case 'no_answer':
                return 'no_answer';
            case 'rescheduled':
                return 'rescheduled';
            case 'wrong_number':
                return 'blocked';
            default:
                return null;
        }
    }

    public function getHistory($driverId)
    {
        if (!$driverId) {
            http_response_code(400);
            echo json_encode(['error' => 'معرف السائق غير صحيح']);
            return;
        }
        $history = $this->callsModel->getCallHistory($driverId);
        echo json_encode($history);
    }

    public function releaseHold()
    {
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callsModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
            $this->sendJsonResponse(['success' => true]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'No driver was on hold.']);
        }
    }

    public function updateDriverInfo()
    {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON غير صالح.');
            }

            if (empty($data['driver_id'])) {
                throw new \Exception('معرف السائق مفقود.');
            }

            // Rename driver_id to id for the model
            $data['id'] = $data['driver_id'];
            unset($data['driver_id']);

            $result = $this->driverModel->update($data);

            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'تم تحديث بيانات السائق بنجاح.']);
            } else {
                throw new \Exception('فشل تحديث بيانات السائق في قاعدة البيانات.');
            }
        } catch (\Exception $e) {
            error_log('Error in updateDriverInfo: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function updateDocuments()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['driver_id']) || !isset($data['documents'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid data.'], 400);
            return;
        }

        $driverId = $data['driver_id'];
        $documents = $data['documents'];
        $updatedDocsData = [];

        try {
            foreach ($documents as $doc) {
                $this->documentsModel->updateDocument(
                    $driverId,
                    $doc['id'],
                    $doc['status'],
                    $doc['note'] ?? ''
                );
            }

            // Fetch the updated document info to send back to the client
            $updatedDocsData = $this->documentsModel->getDriverDocuments($driverId);

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'تم تحديث المستندات بنجاح',
                'documents' => $updatedDocsData
            ]);

        } catch (\Exception $e) {
            error_log('Update Documents Error: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'فشل تحديث المستندات.'], 500);
        }
    }
} 