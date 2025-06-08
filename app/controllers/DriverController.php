<?php

class DriverController extends Controller {
    private $driverModel;
    
    public function __construct() {
        // منع عرض أخطاء PHP في المخرجات
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        
        // التأكد من عدم إرسال أي مخرجات قبل الـ JSON
        ob_start();
        
        parent::__construct();
        $this->driverModel = $this->model('Driver');
        
        // تعيين نوع المحتوى إلى JSON
        header('Content-Type: application/json; charset=utf-8');
    }

    public function update() {
        try {
            error_log("\n\n=== Driver Update Request Started ===");
            
            // التحقق من طريقة الطلب
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }

            // التحقق من البيانات المطلوبة
            if (!isset($_POST['driver_id']) || empty($_POST['driver_id'])) {
                throw new Exception('معرف السائق مطلوب');
            }

            if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
                throw new Exception('اسم السائق مطلوب');
            }

            $driverId = $_POST['driver_id'];
            $name = trim($_POST['name']);
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
            $nationality = isset($_POST['nationality']) ? trim($_POST['nationality']) : '';
            $data_source = isset($_POST['data_source']) ? trim($_POST['data_source']) : '';

            error_log("Processing update for driver ID: {$driverId}");
            error_log("Data received: " . json_encode([
                'name' => $name,
                'email' => $email,
                'gender' => $gender,
                'nationality' => $nationality,
                'data_source' => $data_source
            ]));

            // محاولة تحديث البيانات
            $result = $this->driverModel->update([
                'id' => $driverId,
                'name' => $name,
                'email' => $email,
                'gender' => $gender,
                'nationality' => $nationality,
                'data_source' => $data_source
            ]);

            if (!$result) {
                throw new Exception('فشل في تحديث البيانات');
            }

            // مسح أي مخرجات متراكمة
            ob_clean();
            
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح'
            ]);

        } catch (Exception $e) {
            error_log("Error in driver update: " . $e->getMessage());
            
            // مسح أي مخرجات متراكمة
            ob_clean();
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateStatus() {
        try {
            // التأكد من عدم إرسال أي مخرجات
            ob_start();
            
            // تعيين نوع المحتوى إلى JSON
            header('Content-Type: application/json; charset=utf-8');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }

            // قراءة البيانات المرسلة
            $rawData = file_get_contents('php://input');
            error_log("Received raw data: " . $rawData);
            
            $data = json_decode($rawData, true);
            error_log("Decoded data: " . print_r($data, true));
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('خطأ في تنسيق البيانات المرسلة: ' . json_last_error_msg());
            }

            if (!isset($data['driver_id']) || !isset($data['app_status'])) {
                throw new Exception('البيانات المطلوبة غير مكتملة');
            }

            // التحقق من صحة الحالة
            $validStatuses = ['active', 'inactive', 'banned'];
            if (!in_array($data['app_status'], $validStatuses)) {
                throw new Exception('حالة التطبيق غير صالحة');
            }

            error_log("Attempting to update status for driver ID: {$data['driver_id']} to {$data['app_status']}");

            $result = $this->driverModel->updateStatus($data['driver_id'], $data['app_status']);

            if (!$result) {
                throw new Exception('فشل في تحديث الحالة');
            }

            // مسح أي مخرجات متراكمة
            ob_clean();
            
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث الحالة بنجاح'
            ]);

        } catch (Exception $e) {
            error_log("Error in status update: " . $e->getMessage());
            
            // مسح أي مخرجات متراكمة
            ob_clean();
            
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateDocuments() {
        try {
            // التأكد من عدم إرسال أي مخرجات
            ob_start();
            
            // تعيين نوع المحتوى إلى JSON
            header('Content-Type: application/json; charset=utf-8');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }

            if (!isset($_POST['driver_id'])) {
                throw new Exception('معرف السائق مطلوب');
            }

            $driverId = $_POST['driver_id'];
            $documents = isset($_POST['documents']) ? $_POST['documents'] : [];
            $documentNotes = isset($_POST['document_notes']) ? $_POST['document_notes'] : [];

            error_log("Updating documents for driver ID: {$driverId}");
            error_log("Selected documents: " . json_encode($documents));
            error_log("Document notes: " . json_encode($documentNotes));

            $result = $this->driverModel->updateDocuments($driverId, $documents, $documentNotes);

            if (!$result) {
                throw new Exception('فشل في تحديث المستندات');
            }

            // مسح أي مخرجات متراكمة
            ob_clean();
            
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث المستندات بنجاح'
            ]);

        } catch (Exception $e) {
            error_log("Error in documents update: " . $e->getMessage());
            
            // مسح أي مخرجات متراكمة
            ob_clean();
            
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 