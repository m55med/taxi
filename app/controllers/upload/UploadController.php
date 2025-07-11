<?php

namespace App\Controllers\Upload;

use App\Core\Controller;
use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class UploadController extends Controller
{
    private $driverModel;
    private $countryModel;
    private $docTypeModel;

    public function __construct()
    {
        parent::__construct();
        \App\Core\Auth::requireLogin();
        if (!\App\Core\Auth::hasPermission('upload/drivers')) {
            http_response_code(403);
            require_once APPROOT . '/../app/views/errors/403.php';
            exit;
        }

        $this->driverModel = $this->model('driver/Driver');
        $this->countryModel = $this->model('admin/Country');
        $this->docTypeModel = $this->model('admin/DocumentType');
    }

    public function index()
    {
        $data = [
            'page_main_title' => 'Bulk Upload Drivers',
            'countries' => $this->countryModel->getAll(),
            'document_types' => $this->docTypeModel->getAll()
        ];
        $this->view('upload/index', $data);
    }

    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('upload');
        }

        try {
            // --- Validation ---
            if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload error. Please try again.');
            }
            if (empty($_POST['country_id']) || empty($_POST['app_status'])) {
                 throw new Exception('Country and Application Status are required fields.');
            }

            $file = $_FILES['file'];
            $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Please use CSV or Excel.');
            }

            // --- File Processing ---
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            $headers = array_map('strtolower', array_shift($rows)); // Get and remove header row
            
            $requiredColumns = ['fullname', 'phone', 'email'];
            if (count(array_intersect($requiredColumns, $headers)) !== count($requiredColumns)) {
                 throw new Exception('File is missing required columns: ' . implode(', ', $requiredColumns));
            }

            // --- Data Preparation ---
            $driversToInsert = [];
            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows
                $rowData = array_combine($headers, $row);
                $driversToInsert[] = [
                    'name' => $rowData['fullname'] ?? null,
                    'phone' => $rowData['phone'] ?? null,
                    'email' => $rowData['email'] ?? null,
                ];
            }
            
            if (empty($driversToInsert)) {
                throw new Exception('No valid driver data found in the file.');
            }
            
            $commonData = [
                'country_id' => (int)$_POST['country_id'],
                'app_status' => $_POST['app_status'],
                'data_source' => $_POST['data_source'] ?? 'excel',
                'notes' => $_POST['notes'] ?? null,
                'added_by' => $_SESSION['user_id'],
                'required_doc_ids' => $_POST['required_doc_ids'] ?? [],
            ];

            // --- Database Insertion ---
            $result = $this->driverModel->bulkInsert($driversToInsert, $commonData);

            if ($result['status']) {
                flash('success', $result['message']);
            } else {
                flash('error', $result['message']);
            }

        } catch (Exception $e) {
            flash('error', 'An error occurred: ' . $e->getMessage());
        }

        redirect('upload');
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