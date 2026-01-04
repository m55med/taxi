<?php



namespace App\Controllers\Upload;



use App\Core\Controller;

use App\Core\Database;

use App\Models\Admin\CarType;

use PhpOffice\PhpSpreadsheet\IOFactory;

use Exception;



class UploadController extends Controller

{

    private $driverModel;

    private $countryModel;

    private $docTypeModel;

    private $carTypeModel;



    public function __construct()

    {

        parent::__construct();

        // Note: Authentication checks are handled per method for flexibility

        $this->driverModel = $this->model('driver/Driver');

        $this->countryModel = $this->model('Admin/Country');

        $this->docTypeModel = $this->model('Admin/DocumentType');

        $this->carTypeModel = $this->model('Admin/CarType');

    }



    public function index()

    {

        $data = [

            'page_main_title' => 'Bulk Upload Drivers',

            'countries' => $this->countryModel->getAll(),

            'document_types' => $this->docTypeModel->getAll(),

            'car_types' => $this->carTypeModel->getAll()

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

            if (empty($_POST['country_id']) || empty($_POST['app_status']) || empty($_POST['car_type_id'])) {

                 throw new Exception('Country, Application Status, and Car Type are required fields.');

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

                'main_system_status' => $_POST['app_status'],

                'data_source' => $_POST['data_source'] ?? 'excel',

                'notes' => $_POST['notes'] ?? null,

                'added_by' => $_SESSION['user_id'],

                'required_doc_ids' => $_POST['required_doc_ids'] ?? [],

            ];



            // --- Database Insertion ---

            $result = $this->driverModel->bulkInsert($driversToInsert, $commonData);



            if ($result['status']) {

                $stats = $result['stats'];

                $message = "تمت معالجة الملف بنجاح.";

                $message .= " تمت إضافة " . $stats['added'] . " سائق جديد.";

                if ($stats['skipped'] > 0) {

                    $message .= " تم تخطي " . $stats['skipped'] . " سائق لوجود أرقام هواتف مكررة.";

                }

                if ($stats['errors'] > 0) {

                    $message .= " حدث خطأ أثناء إضافة " . $stats['errors'] . " سائق.";

                }

                flash('success', $message);

            } else {

                $message = $result['message'] ?? 'An unknown error occurred during bulk insert.';

                if (isset($result['stats'])) {

                    $stats = $result['stats'];

                    $message .= " (Added: {$stats['added']}, Skipped: {$stats['skipped']}, Errors: {$stats['errors']})";

                }

                flash('error', $message);

            }



        } catch (Exception $e) {

            flash('error', 'An error occurred: ' . $e->getMessage());

        }



        redirect('upload');

    }

    /**
     * Serve files from uploads directory safely
     * @param string $path The file path relative to uploads directory
     */
    public function serveFile($filename)
    {
        // Debug: Log that the method was called
        error_log("serveFile called with filename: " . $filename);

        // Security: Only allow logged-in users to access files
        \App\Core\Auth::requireLogin();

        error_log("User is logged in, continuing...");

        // Clean and validate the filename
        $filename = trim($filename, '/');

        // Prevent directory traversal attacks
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            http_response_code(403);
            exit('Access denied');
        }

        // Build full file path - assume all files are in tasks directory for simplicity
        $filePath = dirname(APPROOT) . '/public/uploads/tasks/' . $filename;

        error_log("File path: " . $filePath);
        error_log("File exists: " . (file_exists($filePath) ? 'YES' : 'NO'));

        // Check if file exists
        if (!file_exists($filePath)) {
            error_log("File not found, sending 404");
            http_response_code(404);
            exit('File not found');
        }

        error_log("File found, proceeding to serve...");

        // Get file info
        $fileSize = filesize($filePath);
        $fileType = mime_content_type($filePath);

        // Set appropriate headers
        header('Content-Type: ' . $fileType);
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: private, max-age=3600');

        // For images and PDFs, allow inline display
        if (strpos($fileType, 'image/') === 0 || $fileType === 'application/pdf') {
            header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        } else {
            // For other files, force download
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        }

        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Output file content
        readfile($filePath);
        exit;
    }

} 