<?php

namespace App\Controllers\Calls;

use App\Core\Controller;

class DocumentsController extends Controller
{
    private $documentsModel;
    private $driverModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            // This will handle both AJAX and regular requests properly
            $this->sendJsonResponse(['success' => false, 'message' => 'Session expired.', 'redirect' => BASE_PATH . '/auth/login'], 401);
            exit;
        }
        $this->documentsModel = $this->model('Document/Document');
        $this->driverModel = $this->model('Driver/Driver'); // Load Driver model
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'طريقة طلب غير صحيحة'], 405);
            return;
        }

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (!isset($data['driver_id']) || !isset($data['documents']) || !is_array($data['documents'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'بيانات غير صحيحة'], 400);
            return;
        }

        $driverId = $data['driver_id'];
        $documents = $data['documents'];
        $notes = $data['notes'] ?? []; // Get notes array

        try {
            // The logic is now in Driver Model
            $result = $this->driverModel->updateDocuments($driverId, $documents, $notes);

            if ($result) {
                // Fetch the updated document info to send back to the client
                $updatedDocs = $this->documentsModel->getDriverDocuments($driverId);
                
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
            $this->sendJsonResponse(['success' => false, 'message' => 'حدث خطأ في النظام'], 500);
        }
    }

    // Helper function for sending JSON responses
    protected function sendJsonResponse($data, $statusCode = 200)
    {
        if (ob_get_level()) {
            ob_clean();
        }
        header_remove('Set-Cookie');
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
} 