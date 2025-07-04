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
        $debug_info = []; // Initialize debug_info to prevent view errors

        if (!empty($searchPhone)) {
            // Priority 1: Search by phone number if provided
            $driver = $callModel->findAndLockDriverByPhone($searchPhone, $_SESSION['user_id']);
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
                $debug_info = $result['debug_info'] ?? []; // Assign debug info from model
            }
        }

        // Prepare all data required for the view
        $documentModel = $this->model('Document/Document');
        $countryModel = $this->model('Admin/Country');
        $carTypeModel = $this->model('Admin/CarType');

        $data = [
            'driver'               => $driver,
            'debug_info'           => $debug_info,
            'users'                => $callModel->getUsers(),
            'countries'            => $countryModel ? $countryModel->getAll() : [],
            'car_types'            => $carTypeModel ? $carTypeModel->getAll() : [],
            'document_types'       => $documentModel ? $documentModel->getAllDocumentTypes() : [],
            'required_documents'   => $driver && $documentModel ? $documentModel->getDriverDocuments($driver['id']) : [],
            'call_history'         => $driver ? $callModel->getCallHistory($driver['id']) : [],
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

        if ($driver) {
            $_SESSION['locked_driver_id'] = $driver['id'];
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

            // Fetch the next driver
            $skippedDrivers = $_SESSION['skipped_drivers'] ?? [];
            $result = $callModel->findAndLockNextDriver($_SESSION['user_id'], $skippedDrivers);
            $nextDriver = $result['driver'];
            
            if ($nextDriver) {
                $_SESSION['locked_driver_id'] = $nextDriver['id'];
            }

            $callModel->getDb()->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Call recorded successfully.', 'next_driver' => $nextDriver]);

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
        $this->authorize('Calls/updateDocuments');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
            return;
        }

        $driverId = $data['driver_id'] ?? null;
        $submittedDocs = $data['documents'] ?? null;
    
        if (!$driverId || !is_array($submittedDocs)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
            return;
        }

        $documentModel = $this->model('Document/Document');
        $db = \App\Core\Database::getInstance();
    
        try {
            $db->beginTransaction();
    
            // Get IDs of documents currently in the DB for this driver
            $currentDbDocs = $documentModel->getDriverDocuments($driverId);
            $currentDbDocIds = array_column($currentDbDocs, 'id');
    
            // Get IDs of documents submitted from the frontend
            $submittedDocIds = array_column($submittedDocs, 'id');
    
            // 1. Documents to REMOVE: In DB but not in submission
            $docsToRemove = array_diff($currentDbDocIds, $submittedDocIds);
            foreach ($docsToRemove as $docTypeId) {
                $documentModel->removeDriverDocument($driverId, $docTypeId);
            }
    
            // 2. Documents to UPSERT: All submitted documents
            foreach ($submittedDocs as $doc) {
                $documentModel->upsertDriverDocument(
                    $driverId,
                    $doc['id'], // document_type_id
                    $doc['status'] ?? 'submitted',
                    $doc['note'] ?? ''
                );
            }
    
            // 3. Update the master flag for the driver based on the new state
            $documentModel->updateDriverMissingDocsFlag($driverId);
    
            $db->commit();
    
            $updatedDocs = $documentModel->getDriverDocuments($driverId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Documents updated successfully!',
                'documents' => $updatedDocs
            ]);
    
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error in CallsController::updateDocuments: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'A server error occurred while updating documents.',
                'debug' => ['error' => $e->getMessage()]
            ]);
        }
    }
}