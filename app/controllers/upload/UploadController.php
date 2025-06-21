<?php

namespace App\Controllers\Upload;

use App\Core\Controller;
use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class UploadController extends Controller
{
    private $driverModel;

    public function __construct()
    {
        $this->driverModel = $this->model('driver/Driver');
    }

    public function index()
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->view('upload/index');
    }

    public function process()
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . '/upload');
            exit;
        }

        // التحقق من وجود الملف
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'حدث خطأ أثناء رفع الملف';
            header('Location: ' . BASE_PATH . '/upload');
            exit;
        }

        $file = $_FILES['file'];
        $data_source = $_POST['data_source'] ?? 'excel';

        // التحقق من نوع الملف
        $allowedTypes = ['application/vnd.ms-excel', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'نوع الملف غير مدعوم. يرجى استخدام CSV أو Excel';
            header('Location: ' . BASE_PATH . '/upload');
            exit;
        }

        try {
            // قراءة وتحليل الملف
            require_once __DIR__ . '/../../../vendor/autoload.php'; // تحديث المسار للوصول إلى مجلد vendor
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // التحقق من وجود العناوين المطلوبة
            $headers = array_map('strtolower', $rows[0]);
            $requiredColumns = ['fullname', 'phone', 'email', 'rating', 'vehicletype', 'status'];
            $missingColumns = array_diff($requiredColumns, array_intersect($requiredColumns, $headers));

            if (!empty($missingColumns)) {
                $_SESSION['error'] = 'الملف لا يحتوي على جميع الأعمدة المطلوبة: ' . implode(', ', $missingColumns);
                header('Location: ' . BASE_PATH . '/upload');
                exit;
            }

            // تحضير البيانات للإدخال
            $drivers = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = array_combine($headers, $rows[$i]);
                
                // تحويل حالة السائق إلى الصيغة المناسبة
                $status = strtolower($row['status']);
                $status = ($status === 'active' || $status === 'نشط') ? 'active' : 'inactive';

                $drivers[] = [
                    'name' => $row['fullname'],
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'rating' => floatval($row['rating']),
                    'car_type_id' => $this->getCarTypeId($row['vehicletype']),
                    'app_status' => $status,
                    'data_source' => $data_source,
                    'added_by' => $_SESSION['user_id']
                ];
            }

            // إدخال البيانات إلى قاعدة البيانات
            $result = $this->driverModel->bulkInsert($drivers);

            if ($result['status']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }

        } catch (Exception $e) {
            $_SESSION['error'] = 'حدث خطأ أثناء معالجة الملف: ' . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/upload');
        exit;
    }

    private function getCarTypeId($vehicleType)
    {
        // تعيين القيم المتوقعة من ملف Excel إلى معرفات قاعدة البيانات
        $types = [
            'sedan' => 1,
            'سيدان' => 1,
            'suv' => 2,
            'دفع رباعي' => 2,
            'van' => 3,
            'فان' => 3,
            'luxury' => 4,
            'فاخرة' => 4,
            'economy' => 5,
            'اقتصادية' => 5,
            'premium' => 6,
            'بريميوم' => 6
        ];

        $type = strtolower(trim($vehicleType ?? ''));
        return $types[$type] ?? 1; // إرجاع سيدان كقيمة افتراضية
    }
} 