<?php

namespace App\Controllers\Calls;

use App\Core\Controller;
use App\Core\Auth;

class CallsController extends Controller
{
    public function index()
    {
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }
        $this->authorize('App\Controllers\Calls');

        // Load models only when needed
        $callModel = $this->model('Calls/Call');
        
        // If a model fails to load, it will be null.
        if (!$callModel) {
            die('Error: Call model could not be loaded.');
        }

        // Release any driver that was locked by this user in a previous session
        if (isset($_SESSION['locked_driver_id'])) {
            $callModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }
        
        $driver = null;
        $searchPhone = filter_input(INPUT_GET, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!empty($searchPhone)) {
            // Priority 1: Search by phone number if provided
            $driver = $callModel->findAndLockDriverByPhone($searchPhone);
        } else {
            // Priority 2: Check for an unseen assignment.
            $unseenAssignment = $callModel->getUnseenAssignment($_SESSION['user_id']);
            if ($unseenAssignment) {
                $driver = $callModel->getDriverById($unseenAssignment['driver_id']);
                if ($driver) {
                    $callModel->markAssignmentAsSeen($unseenAssignment['id']);
                    $callModel->setDriverHold($driver['id'], true);
                }
            } 
            
            // Priority 3: Get next driver from the queue.
            if (!$driver) {
                $skippedDrivers = $_SESSION['skipped_drivers'] ?? [];
                $result = $callModel->findAndLockNextDriver($_SESSION['user_id'], $skippedDrivers);
                $driver = $result['driver'];
                $data['debug_info'] = $result['debug_info'] ?? null;
            }
        }

        // Prepare all data required for the view
        $documentModel = $this->model('Document/Document');
        $countryModel = $this->model('Admin/Country');
        $carTypeModel = $this->model('Admin/CarType');

        $data = [
            'driver'               => $driver,
            'users'                => $callModel->getUsers(),
            'countries'            => $countryModel ? $countryModel->getAll() : [],
            'car_types'            => $carTypeModel ? $carTypeModel->getAll() : [],
            'document_types'       => $documentModel ? $documentModel->getAllTypes() : [],
            'required_documents'   => [],
            'call_history'         => [],
            'today_calls_count'    => $callModel->getTodayCallsCount(),
            'total_pending_calls'  => $callModel->getTotalPendingCalls(),
            'call_status_text'     => [
                'no_answer'     => 'لم يتم الرد',
                'answered'      => 'تم الرد',
                'busy'          => 'مشغول',
                'not_available' => 'غير متاح',
                'wrong_number'  => 'رقم خاطئ',
                'rescheduled'   => 'معاد جدولته'
            ]
        ];

        if ($driver && $documentModel) {
            $_SESSION['locked_driver_id'] = $driver['id'];
            $data['required_documents'] = $documentModel->getDriverDocuments($driver['id'], true);
            $data['call_history'] = $callModel->getCallHistory($driver['id']);
        }
        
        $this->view('calls/index', $data);
    }
    
    public function record()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $this->authorize('App\Controllers\Calls');
        
        $callModel = $this->model('Calls/Call');
        if (!$callModel) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error loading call model.']);
            return;
        }
        
        $driverId = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
        $rawCallStatus = $_POST['call_status'] ?? '';
        $allowedStatuses = ['answered', 'no_answer', 'busy', 'not_available', 'wrong_number', 'rescheduled'];
        $callStatus = in_array($rawCallStatus, $allowedStatuses) ? $rawCallStatus : null;
        
        if (!$driverId || !$callStatus) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Driver ID and Call Status are required.']);
            return;
        }

        try {
            $callModel->getDb()->beginTransaction();

            $callData = [
                'driver_id' => $driverId,
                'call_by' => $_SESSION['user_id'],
                'call_status' => $callStatus,
                'notes' => $_POST['notes'] ?? '',
                'next_call_at' => (in_array($callStatus, ['no_answer', 'rescheduled', 'busy', 'not_available'])) ? ($_POST['next_call_at'] ?? null) : null
            ];
            
            $callModel->recordCall($callData);
            $callModel->updateDriverStatusBasedOnCall($driverId, $callStatus);
            $callModel->releaseDriverHold($driverId);
            unset($_SESSION['locked_driver_id']);

            $callModel->getDb()->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Call recorded successfully.']);

        } catch (\Exception $e) {
            $callModel->getDb()->rollBack();
            error_log("Call Record Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
        }
    }
    
    // Any other methods like skip(), transfer() should also be reviewed
    // to use this new pattern of loading models.
    public function skip($driverId = null)
    {
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }
        $this->authorize('App\Controllers\Calls');

        if ($driverId) {
            // Add driver to a skipped list in the session
            if (!isset($_SESSION['skipped_drivers'])) {
                $_SESSION['skipped_drivers'] = [];
            }
            $_SESSION['skipped_drivers'][] = (int)$driverId;
            $_SESSION['skipped_drivers'] = array_unique($_SESSION['skipped_drivers']);

            // Release the hold
            $callModel = $this->model('Calls/Call');
            if ($callModel) {
                $callModel->releaseDriverHold($driverId);
            }
            
            // Ensure the session's locked driver is also cleared
            if (isset($_SESSION['locked_driver_id']) && $_SESSION['locked_driver_id'] == $driverId) {
                unset($_SESSION['locked_driver_id']);
            }
        }

        // Redirect to get the next driver
        redirect('calls');
    }

    public function updateDocuments()
    {
        $this->authorize('calls.documents.update');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'طريقة طلب غير صحيحة'], 405);
            return;
        }

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
             $this->sendJsonResponse([
                'success' => false, 
                'message' => 'بيانات JSON غير صالحة',
                'debug_info' => ['error' => json_last_error_msg()]
            ], 400);
            return;
        }

        if (!isset($data['driver_id']) || !isset($data['documents']) || !is_array($data['documents'])) {
            $this->sendJsonResponse([
                'success' => false, 
                'message' => 'بيانات غير صحيحة',
                'debug_info' => ['received_data' => $data]
            ], 400);
            return;
        }

        $driverId = $data['driver_id'];
        $documents = $data['documents'];
        $notes = $data['notes'] ?? [];

        try {
            $documentsModel = $this->model('Document/Document');
            if (!$documentsModel) {
                throw new \Exception('فشل تحميل نموذج المستندات.');
            }
            
            $result = $documentsModel->updateDriverDocuments($driverId, $documents, $notes);

            if ($result) {
                $updatedDocs = $documentsModel->getDriverDocuments($driverId, true);
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'تم تحديث المستندات بنجاح',
                    'documents' => $updatedDocs
                ]);
            } else {
                throw new \Exception('فشل تحديث المستندات في قاعدة البيانات.');
            }
        } catch (\Exception $e) {
            error_log("Error in updateDocuments: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'message' => 'حدث خطأ في النظام',
                'debug_info' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }
}