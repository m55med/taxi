<?php

namespace App\Controllers;

use App\Core\Controller;
use Exception;

class DriverController extends Controller
{
    private $driverModel;

    public function __construct()
    {
        parent::__construct();
        $this->driverModel = $this->model('Driver');
    }

    public function update()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة', 405);
            }

            if (empty($_POST['driver_id']) || empty(trim($_POST['name']))) {
                throw new Exception('البيانات المطلوبة غير مكتملة', 400);
            }

            $result = $this->driverModel->update([
                'id' => $_POST['driver_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email'] ?? ''),
                'gender' => trim($_POST['gender'] ?? ''),
                'nationality' => trim($_POST['nationality'] ?? ''),
                'data_source' => trim($_POST['data_source'] ?? ''),
                'app_status' => trim($_POST['app_status'] ?? 'active')
            ]);

            if (!$result) {
                throw new Exception('فشل في تحديث البيانات من جهة الخادم', 500);
            }

            // جلب بيانات السائق المحدثة لإرجاعها
            $updatedDriver = $this->driverModel->getById($_POST['driver_id']);
            if (!$updatedDriver) {
                 throw new Exception('فشل في استرداد بيانات السائق المحدثة', 500);
            }

            $this->sendJsonResponse(['success' => true, 'message' => 'تم تحديث البيانات بنجاح', 'driver' => $updatedDriver]);

        } catch (Exception $e) {
            error_log("Driver update error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    public function updateDocuments()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة', 405);
            }

            if (empty($_POST['driver_id'])) {
                throw new Exception('معرف السائق مطلوب', 400);
            }

            $driverId = $_POST['driver_id'];
            $documents = $_POST['documents'] ?? [];
            $documentNotes = $_POST['document_notes'] ?? [];

            $result = $this->driverModel->updateDocuments($driverId, $documents, $documentNotes);

            if (!$result) {
                throw new Exception('فشل تحديث المستندات في قاعدة البيانات', 500);
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'تم تحديث المستندات بنجاح'
            ]);

        } catch (Exception $e) {
            error_log("Driver documents update error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    // public function updateStatus()
    // {
    //     header('Content-Type: application/json');
    //     $data = json_decode(file_get_contents('php://input'), true);

    //     if (!$this->isAjax() || !$this->isAuthenticated()) {
    //         http_response_code(401);
    //         echo json_encode(['error' => 'Unauthorized']);
    //         return;
    //     }

    //     if (!isset($data['driver_id']) || !isset($data['status'])) {
    //         http_response_code(400);
    //         echo json_encode(['error' => 'Invalid input']);
    //         return;
    //     }

    //     try {
    //         $result = $this->driverModel->updateStatus($data['driver_id'], $data['status']);
    //         if ($result) {
    //             echo json_encode(['success' => true]);
    //         } else {
    //             http_response_code(500);
    //             echo json_encode(['error' => 'Failed to update status']);
    //         }
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['error' => $e->getMessage()]);
    //     }
    // }
} 