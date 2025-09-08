<?php

namespace App\Controllers\Establishments;

use App\Models\Establishments\EstablishmentModel;

class EstablishmentsController
{
    private $establishmentModel;

    public function __construct()
    {
        $this->establishmentModel = new EstablishmentModel();
    }

    /**
     * API: Create new establishment
     */
    public function createEstablishment()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            // Load ImageHelper
            require_once APPROOT . '/helpers/ImageHelper.php';

            // DEBUG: Let's see what we're receiving
            $debug_info = [
                'POST' => $_POST,
                'REQUEST' => $_REQUEST,
                'FILES' => array_keys($_FILES),
                'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                'HTTP_CONTENT_TYPE' => $_SERVER['HTTP_CONTENT_TYPE'] ?? 'not set'
            ];

            // Try to get data from different sources
            $data = [];
            
            // Method 1: Try $_POST (normal form submission)
            if (!empty($_POST)) {
                $data = $_POST;
            }
            // Method 2: Try $_REQUEST (fallback)
            else if (!empty($_REQUEST)) {
                $data = $_REQUEST;
                // Remove unwanted fields
                unset($data['PHPSESSID']);
            }
            // Method 3: For JSON (backward compatibility)
            else {
                $input = file_get_contents('php://input');
                $jsonData = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($jsonData)) {
                    $data = $jsonData;
                }
            }

            // Remove file fields from data (they should be in $_FILES)
            unset($data['establishment_logo'], $data['establishment_header_image']);

            // If still empty, return debug info
            if (empty($data['establishment_name'])) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Establishment name is required or data not received properly', 
                    'debug' => $debug_info,
                    'received_data' => $data,
                    'server_settings' => [
                        'PHP_VERSION' => PHP_VERSION,
                        'post_max_size' => ini_get('post_max_size'),
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'max_file_uploads' => ini_get('max_file_uploads'),
                        'max_input_vars' => ini_get('max_input_vars')
                    ]
                ]);
                return;
            }



            // Validation (مثال على اسم المؤسسة)
            if (empty($data['establishment_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Establishment name is required']);
                return;
            }

            // Create the establishment
            $establishmentId = $this->establishmentModel->create($data);

            if (!$establishmentId) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create establishment']);
                return;
            }

            // Handle image uploads
            $imageResults = [];

            // Logo
            if (isset($_FILES['establishment_logo']) && $_FILES['establishment_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $logoResult = \App\Helpers\ImageHelper::uploadEstablishmentImage(
                    $_FILES['establishment_logo'],
                    'logo',
                    $establishmentId
                );

                if ($logoResult['success']) {
                    $this->establishmentModel->updateFields($establishmentId, [
                        'establishment_logo' => $logoResult['path']
                    ]);
                    $imageResults['logo'] = $logoResult['path'];
                } else {
                    $imageResults['logo_error'] = $logoResult['error'];
                }
            }

            // Header
            if (isset($_FILES['establishment_header_image']) && $_FILES['establishment_header_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $headerResult = \App\Helpers\ImageHelper::uploadEstablishmentImage(
                    $_FILES['establishment_header_image'],
                    'header',
                    $establishmentId
                );

                if ($headerResult['success']) {
                    $this->establishmentModel->updateFields($establishmentId, [
                        'establishment_header_image' => $headerResult['path']
                    ]);
                    $imageResults['header'] = $headerResult['path'];
                } else {
                    $imageResults['header_error'] = $headerResult['error'];
                }
            }

            // Get updated establishment data
            $establishmentData = $this->establishmentModel->getById($establishmentId);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Establishment created successfully',
                'establishment_id' => $establishmentId,
                'images' => $imageResults,
                'data' => $establishmentData
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
        }
    }


    /**
     * Serve establishment image with authentication
     */
    public function serveImage($imagePath = null)
    {
        // Verify user is logged in
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $userRole = $_SESSION['user']['role_name'] ?? 'agent';

        // Check if user can access establishment images
        if (!in_array($userRole, ['admin', 'developer', 'marketer'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        if (!$imagePath) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Image path required']);
            return;
        }

        try {
            // Load ImageHelper
            require_once APPROOT . '/helpers/ImageHelper.php';
            
            // Get the actual file path
            $filePath = \App\Helpers\ImageHelper::getImageFilePath($imagePath);
            
            if (!$filePath || !file_exists($filePath)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Image not found']);
                return;
            }

            // Get file info
            $fileSize = filesize($filePath);
            $fileInfo = pathinfo($filePath);
            $extension = strtolower($fileInfo['extension']);

            // Set appropriate MIME type
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp'
            ];

            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            // Set headers for image serving
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . $fileSize);
            header('Cache-Control: private, max-age=3600'); // Cache for 1 hour
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT');
            
            // Security headers
            header('X-Content-Type-Options: nosniff');
            header('Content-Security-Policy: default-src \'none\'; img-src \'self\'');

            // Output the file
            readfile($filePath);
            
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error serving image']);
        }
    }

    /**
     * Display establishments page
     */
    public function index()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/login');
            return;
        }

        $userRole = $_SESSION['user']['role_name'] ?? 'agent';
        $userId = $_SESSION['user']['id'] ?? 0;

        // Check if user can access establishments
        if (!in_array($userRole, ['admin', 'developer', 'marketer'])) {
            header('Location: ' . URLROOT . '/dashboard');
            return;
        }

        // Determine marketer filter
        $marketerId = null;
        if ($userRole === 'marketer') {
            $marketerId = $userId;
        }

        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $filterMarketer = $_GET['filter_marketer'] ?? '';
        $filterContact = $_GET['filter_contact'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';

        // For marketers, override filter_marketer with their own ID
        if ($userRole === 'marketer') {
            $filterMarketer = $userId;
        }

        // Pagination
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 25;

        // Get data with filters
        $establishments = $this->establishmentModel->getEstablishments($marketerId, $page, $limit, $search, $filterMarketer, $filterContact, $sortBy, $sortOrder);
        $totalCount = $this->establishmentModel->getTotalCount($marketerId, $search, $filterMarketer, $filterContact);
        $summaryStats = $this->establishmentModel->getSummaryStats($marketerId, $search, $filterMarketer, $filterContact);

        // Get marketers for filter dropdown
        $db = \App\Core\Database::getInstance();
        $db->query("SELECT id, name FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer') ORDER BY name");
        $marketers = $db->resultSet(\PDO::FETCH_OBJ);

        // Get current marketer's details if user is a marketer
        $currentMarketer = null;
        if ($userRole === 'marketer') {
            $db->query("SELECT id, name FROM users WHERE id = :userId");
            $db->bind(':userId', $userId);
            $currentMarketer = $db->single(\PDO::FETCH_OBJ);
        }

        // Calculate pagination info
        $totalPages = ceil($totalCount / $limit);
        $startRecord = (($page - 1) * $limit) + 1;
        $endRecord = min($page * $limit, $totalCount);

        $data = [
            'establishments' => $establishments,
            'summaryStats' => $summaryStats,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalCount,
                'start_record' => $startRecord,
                'end_record' => $endRecord,
                'limit' => $limit
            ],
            'user_role' => $userRole,
            'user_id' => $userId,
            'marketer_id' => $marketerId,
            'marketers' => $marketers,
            'current_marketer' => $currentMarketer, // Pass current marketer data to the view
            'filters' => [
                'search' => $search,
                'filter_marketer' => $filterMarketer,
                'filter_contact' => $filterContact,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ];

        require_once APPROOT . '/views/referral/establishments.php';
    }

    /**
     * Export establishments data
     */
    public function export()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/login');
            return;
        }

        $userRole = $_SESSION['user']['role_name'] ?? 'agent';
        $userId = $_SESSION['user']['id'] ?? 0;

        // Check if user can access establishments
        if (!in_array($userRole, ['admin', 'developer', 'marketer'])) {
            header('Location: ' . URLROOT . '/dashboard');
            return;
        }

        // Determine marketer filter
        $marketerId = null;
        if ($userRole === 'marketer') {
            $marketerId = $userId;
        }

        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $filterMarketer = $_GET['filter_marketer'] ?? '';
        $filterContact = $_GET['filter_contact'] ?? '';

        // For marketers, override filter_marketer with their own ID
        if ($userRole === 'marketer') {
            $filterMarketer = $userId;
        }

        $format = $_GET['format'] ?? 'excel';
        $data = $this->establishmentModel->getAllForExport($marketerId, $search, $filterMarketer, $filterContact);

        $exportData = [
            'headers' => [
                'ID',
                'Establishment Name',
                'Legal Name', 
                'Taxpayer Number',
                'Street',
                'House Number',
                'Postal/ZIP',
                'Establishment Email',
                'Establishment Phone',
                'Owner Full Name',
                'Owner Position',
                'Owner Email',
                'Owner Phone',
                'Description',
                'Marketer',
                'Created At'
            ],
            'rows' => array_map(function($item) {
                return [
                    $item['id'],
                    $item['establishment_name'],
                    $item['legal_name'],
                    $item['taxpayer_number'],
                    $item['street'],
                    $item['house_number'],
                    $item['postal_zip'],
                    $item['establishment_email'],
                    $item['establishment_phone'],
                    $item['owner_full_name'],
                    $item['owner_position'],
                    $item['owner_email'],
                    $item['owner_phone'],
                    $item['description'],
                    $item['marketer_name'],
                    $item['created_at']
                ];
            }, $data)
        ];

        // Load ExportHelper
        require_once APPROOT . '/helpers/export_helper.php';
        
        switch ($format) {
            case 'csv':
                \App\Helpers\ExportHelper::exportToCsv($exportData, 'establishments');
                break;
            case 'json':
                \App\Helpers\ExportHelper::exportToJson($data, 'establishments');
                break;
            case 'excel':
            default:
                \App\Helpers\ExportHelper::exportToExcel($exportData, 'establishments');
                break;
        }
    }

    /**
     * Edit establishment (admin only)
     */
    public function edit($id = null)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/login');
            return;
        }

        $userRole = $_SESSION['user']['role_name'] ?? 'agent';

        // Only admin can edit
        if (!in_array($userRole, ['admin', 'developer'])) {
            $_SESSION['flash_message'] = 'Access denied. Only administrators can edit establishments.';
            $_SESSION['flash_message_type'] = 'error';
            header('Location: ' . URLROOT . '/referral/establishments');
            return;
        }

        if ($id === null || !is_numeric($id)) {
            $_SESSION['flash_message'] = 'Invalid establishment ID.';
            $_SESSION['flash_message_type'] = 'error';
            header('Location: ' . URLROOT . '/referral/establishments');
            return;
        }

        $establishment = $this->establishmentModel->getById($id);
        
        if (!$establishment) {
            $_SESSION['flash_message'] = 'Establishment not found.';
            $_SESSION['flash_message_type'] = 'error';
            header('Location: ' . URLROOT . '/referral/establishments');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Load ImageHelper
            require_once APPROOT . '/helpers/ImageHelper.php';
            
            $data = [
                'establishment_name' => $_POST['establishment_name'] ?? '',
                'legal_name' => $_POST['legal_name'] ?? '',
                'taxpayer_number' => $_POST['taxpayer_number'] ?? '',
                'street' => $_POST['street'] ?? '',
                'house_number' => $_POST['house_number'] ?? '',
                'postal_zip' => $_POST['postal_zip'] ?? '',
                'establishment_email' => $_POST['establishment_email'] ?? '',
                'establishment_phone' => $_POST['establishment_phone'] ?? '',
                'owner_full_name' => $_POST['owner_full_name'] ?? '',
                'owner_position' => $_POST['owner_position'] ?? '',
                'owner_email' => $_POST['owner_email'] ?? '',
                'owner_phone' => $_POST['owner_phone'] ?? '',
                'description' => $_POST['description'] ?? '',
                'marketer_id' => !empty($_POST['marketer_id']) ? $_POST['marketer_id'] : null
            ];

            $imageMessages = [];
            
            // Handle logo upload if new file is provided
            if (isset($_FILES['establishment_logo']) && $_FILES['establishment_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $logoResult = \App\Helpers\ImageHelper::uploadEstablishmentImage(
                    $_FILES['establishment_logo'], 
                    'logo', 
                    $id
                );
                
                if ($logoResult['success']) {
                    // Delete old logo if exists
                    if (!empty($establishment->establishment_logo)) {
                        \App\Helpers\ImageHelper::deleteEstablishmentImage($establishment->establishment_logo);
                    }
                    $data['establishment_logo'] = $logoResult['path'];
                    $imageMessages[] = 'Logo updated successfully.';
                } else {
                    $imageMessages[] = 'Logo upload failed: ' . $logoResult['error'];
                }
            }

            // Handle header image upload if new file is provided
            if (isset($_FILES['establishment_header_image']) && $_FILES['establishment_header_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $headerResult = \App\Helpers\ImageHelper::uploadEstablishmentImage(
                    $_FILES['establishment_header_image'], 
                    'header', 
                    $id
                );
                
                if ($headerResult['success']) {
                    // Delete old header image if exists
                    if (!empty($establishment->establishment_header_image)) {
                        \App\Helpers\ImageHelper::deleteEstablishmentImage($establishment->establishment_header_image);
                    }
                    $data['establishment_header_image'] = $headerResult['path'];
                    $imageMessages[] = 'Header image updated successfully.';
                } else {
                    $imageMessages[] = 'Header image upload failed: ' . $headerResult['error'];
                }
            }

            if ($this->establishmentModel->update($id, $data)) {
                $message = 'Establishment updated successfully.';
                if (!empty($imageMessages)) {
                    $message .= ' ' . implode(' ', $imageMessages);
                }
                $_SESSION['flash_message'] = $message;
                $_SESSION['flash_message_type'] = 'success';
                header('Location: ' . URLROOT . '/referral/establishments');
                return;
            } else {
                $_SESSION['flash_message'] = 'Failed to update establishment.';
                $_SESSION['flash_message_type'] = 'error';
            }
        }

        // Get marketers for dropdown
        $db = \App\Core\Database::getInstance();
        $db->query("SELECT id, name FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer')");
        $marketers = $db->resultSet(\PDO::FETCH_OBJ);

        $data = [
            'establishment' => $establishment,
            'marketers' => $marketers
        ];

        require_once APPROOT . '/views/referral/edit_establishment.php';
    }

    /**
     * Delete establishment (admin only)
     */
    public function delete($id = null)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/login');
            return;
        }

        $userRole = $_SESSION['user']['role_name'] ?? 'agent';

        // Only admin can delete
        if (!in_array($userRole, ['admin', 'developer'])) {
            $_SESSION['flash_message'] = 'Access denied. Only administrators can delete establishments.';
            $_SESSION['flash_message_type'] = 'error';
            header('Location: ' . URLROOT . '/referral/establishments');
            return;
        }

        if ($id === null || !is_numeric($id)) {
            $_SESSION['flash_message'] = 'Invalid establishment ID.';
            $_SESSION['flash_message_type'] = 'error';
            header('Location: ' . URLROOT . '/referral/establishments');
            return;
        }

        $establishment = $this->establishmentModel->getById($id);
        
        if (!$establishment) {
            $_SESSION['flash_message'] = 'Establishment not found.';
            $_SESSION['flash_message_type'] = 'error';
            header('Location: ' . URLROOT . '/referral/establishments');
            return;
        }

        if ($this->establishmentModel->delete($id)) {
            // Delete associated images after successful deletion
            require_once APPROOT . '/helpers/ImageHelper.php';
            
            if (!empty($establishment->establishment_logo)) {
                \App\Helpers\ImageHelper::deleteEstablishmentImage($establishment->establishment_logo);
            }
            
            if (!empty($establishment->establishment_header_image)) {
                \App\Helpers\ImageHelper::deleteEstablishmentImage($establishment->establishment_header_image);
            }
            
            $_SESSION['flash_message'] = 'Establishment "' . $establishment->establishment_name . '" deleted successfully.';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Failed to delete establishment.';
            $_SESSION['flash_message_type'] = 'error';
        }

        header('Location: ' . URLROOT . '/referral/establishments');
    }
}
