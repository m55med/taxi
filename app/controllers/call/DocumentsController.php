<?php

namespace App\Controllers\Call;

use App\Core\Controller;
use App\Models\Call\Documents;

class DocumentsController extends Controller
{
    private $documentsModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        $this->documentsModel = new Documents();
    }

    public function updateDocuments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
            return;
        }

        // قراءة البيانات من الطلب JSON
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (!isset($data['driver_id']) || !isset($data['documents']) || !is_array($data['documents'])) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
            return;
        }

        $driverId = $data['driver_id'];
        $documents = $data['documents'];
        $success = true;
        $updatedDocs = [];

        // بدء المعاملة
        $this->documentsModel->beginTransaction();

        try {
            foreach ($documents as $doc) {
                if (!isset($doc['id']) || !isset($doc['status'])) {
                    continue;
                }

                $result = $this->documentsModel->updateDocument(
                    $driverId,
                    $doc['id'],
                    $doc['status'],
                    $doc['note'] ?? ''
                );

                if (!$result) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                $this->documentsModel->commit();
                // جلب البيانات المحدثة
                $updatedDocs = $this->documentsModel->getDriverDocuments($driverId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'تم تحديث المستندات بنجاح',
                    'documents' => $updatedDocs
                ]);
            } else {
                $this->documentsModel->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث المستندات'
                ]);
            }
        } catch (\Exception $e) {
            $this->documentsModel->rollBack();
            error_log("Error in updateDocuments: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ في النظام'
            ]);
        }
    }
} 