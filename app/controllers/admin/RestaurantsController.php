<?php

namespace App\Controllers\admin;

use App\Core\Controller;
use App\helpers\ExportHelper;
use App\Core\Auth;

class RestaurantsController extends Controller
{
    private $restaurantModel;

    public function __construct()
    {
        // Use the centralized admin check for consistency.
        Auth::checkAdmin();
        $this->restaurantModel = $this->model('Admin/Restaurant');
    }

    public function index()
    {


        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $recordsPerPage = 25;

        $filters = [
            'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS),
            'governorate' => filter_input(INPUT_GET, 'governorate', FILTER_SANITIZE_SPECIAL_CHARS),
            'category' => filter_input(INPUT_GET, 'category', FILTER_SANITIZE_SPECIAL_CHARS),
            'marketer' => filter_input(INPUT_GET, 'marketer', FILTER_SANITIZE_NUMBER_INT),
            'start_date' => filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS),
            'end_date' => filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $result = $this->restaurantModel->getFilteredRestaurants($filters, $currentPage, $recordsPerPage);
        $restaurants = $result['data'];
        $totalRecords = $result['total'];

        $marketers = $this->restaurantModel->getMarketers();
        $governorates = $this->restaurantModel->getGovernorates();
        $categories = $this->restaurantModel->getCategories();
        $stats = $this->restaurantModel->getStats($filters);

        $data = [
            'page_title' => 'Manage Restaurants',
            'restaurants' => $restaurants,
            'marketers' => $marketers,
            'governorates' => $governorates,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $filters,
            'currentPage' => $currentPage,
            'totalPages' => ceil($totalRecords / $recordsPerPage),
            'totalRecords' => $totalRecords
        ];

        $this->view('admin/restaurants/index', $data);
    }

    public function export($format = 'excel')
    {
        if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
            redirect('login');
        }
        
        $restaurants = $this->restaurantModel->getAll();
        
        $fileName = 'restaurants_' . date('Y-m-d');
        
        $exportData = [
            'headers' => [
                'ID', 'Name (AR)', 'Name (EN)', 'Category', 'Governorate', 'City', 
                'Address', 'Is Chain', 'Num Stores', 'Contact Name', 'Email', 'Phone', 
                'PDF Path', 'Created At'
            ],
            'rows' => array_map(function($restaurant) {
                return [
                    $restaurant['id'],
                    $restaurant['name_ar'],
                    $restaurant['name_en'],
                    $restaurant['category'],
                    $restaurant['governorate'],
                    $restaurant['city'],
                    $restaurant['address'],
                    $restaurant['is_chain'] ? 'Yes' : 'No',
                    $restaurant['num_stores'],
                    $restaurant['contact_name'],
                    $restaurant['email'],
                    $restaurant['phone'],
                    $restaurant['pdf_path'],
                    $restaurant['created_at']
                ];
            }, $restaurants)
        ];

        if ($format === 'excel') {
            ExportHelper::exportToExcel($exportData, $fileName);
        } elseif ($format === 'json') {
            // For JSON, we might not need the headers, just the data rows
            ExportHelper::exportToJson($exportData['rows'], $fileName);
        }
    }

    public function edit($id)
    {
        if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
            redirect('login');
        }

        $restaurant = $this->restaurantModel->getById($id);
        if (!$restaurant) {
            // Handle not found error, maybe redirect with a message
            redirect('admin/restaurants');
        }

        $data = [
            'page_title' => 'Edit Restaurant',
            'restaurant' => $restaurant
        ];

        $this->view('admin/restaurants/edit', $data);
    }

    public function update($id)
    {
        if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
            redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/restaurants');
        }

        // Get existing data to preserve pdf_path if no new file is uploaded
        $existingRestaurant = $this->restaurantModel->getById($id);

        $data = [
            'name_ar' => $_POST['name_ar'] ?? null,
            'name_en' => $_POST['name_en'] ?? null,
            'category' => $_POST['category'] ?? null,
            'governorate' => $_POST['governorate'] ?? null,
            'city' => $_POST['city'] ?? null,
            'address' => $_POST['address'] ?? null,
            'is_chain' => isset($_POST['is_chain']) ? 1 : 0,
            'num_stores' => !empty($_POST['num_stores']) ? (int)$_POST['num_stores'] : null,
            'contact_name' => $_POST['contact_name'] ?? null,
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'pdf_path' => $existingRestaurant['pdf_path'], // Default to old filename
        ];

        // Handle PDF upload if a new file is provided
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0) {
            $uploadDir = APPROOT . '/uploads/pdfs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['pdf']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
                // If there was an old PDF, delete it
                if ($existingRestaurant['pdf_path'] && file_exists($uploadDir . $existingRestaurant['pdf_path'])) {
                    unlink($uploadDir . $existingRestaurant['pdf_path']);
                }
                $data['pdf_path'] = $fileName; // Store only the filename
            }
        }

        if ($this->restaurantModel->update($id, $data)) {
            // Optional: Add success flash message
        } else {
            // Optional: Add error flash message
        }
        
        redirect('admin/restaurants');
    }

    public function viewPdf($id)
    {
        if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
            redirect('login');
        }
        
        $restaurant = $this->restaurantModel->getById($id);

        if (!$restaurant || empty($restaurant['pdf_path'])) {
            // Handle not found or no PDF
            http_response_code(404);
            echo "File not found.";
            exit;
        }
        
        // The pdf_path is relative to the uploads/pdfs directory.
        $relativePath = $restaurant['pdf_path'];

        // Security check to prevent directory traversal.
        if (strpos($relativePath, '..') !== false) {
            http_response_code(400);
            echo "Invalid file path.";
            exit;
        }

        $filePath = APPROOT . '/uploads/pdfs/' . $relativePath;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            http_response_code(404);
            echo "File not found on server.";
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
    }

    public function migratePdfs()
    {
        echo "Starting PDF migration...<br>";
        $restaurants = $this->restaurantModel->getAll();
        $publicDir = dirname(APPROOT) . '/public';
        $secureDir = APPROOT . '/uploads/pdfs/';

        if (!is_dir($secureDir)) {
            mkdir($secureDir, 0777, true);
        }

        $updatedCount = 0;
        foreach ($restaurants as $restaurant) {
            $oldPath = $restaurant['pdf_path'];
            if ($oldPath && strpos($oldPath, '/uploads/pdfs/') === 0) {
                $fileName = basename($oldPath);
                $oldFullPath = $publicDir . $oldPath;
                $newFullPath = $secureDir . $fileName;

                echo "Processing restaurant ID {$restaurant['id']}:<br>";
                echo " - Old path: {$oldFullPath}<br>";

                if (file_exists($oldFullPath)) {
                    if (rename($oldFullPath, $newFullPath)) {
                        echo " - Moved file to: {$newFullPath}<br>";
                        $this->restaurantModel->updatePdfPath($restaurant['id'], $fileName);
                        $updatedCount++;
                        echo " - Database updated.<br>";
                    } else {
                        echo " - <strong style='color:red;'>ERROR:</strong> Could not move file.<br>";
                    }
                } else {
                    echo " - <strong style='color:orange;'>WARNING:</strong> File not found at old path, updating DB anyway.<br>";
                    $this->restaurantModel->updatePdfPath($restaurant['id'], $fileName);
                }
            } else {
                 echo "Skipping restaurant ID {$restaurant['id']} as pdf_path is already clean or empty.<br>";
            }
        }
        echo "<hr><strong>Migration complete. Total records updated: {$updatedCount}</strong>";
    }

    public function delete($id)
    {
        if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
            redirect('login');
        }

        // Before deleting the record, delete the associated PDF file
        $restaurant = $this->restaurantModel->getById($id);
        $uploadDir = APPROOT . '/uploads/pdfs/';
        if ($restaurant && $restaurant['pdf_path'] && file_exists($uploadDir . $restaurant['pdf_path'])) {
            unlink($uploadDir . $restaurant['pdf_path']);
        }

        if ($this->restaurantModel->delete($id)) {
            // Optional: Add a flash message for success
        } else {
            // Optional: Add a flash message for error
        }
        redirect('admin/restaurants');
    }
}
