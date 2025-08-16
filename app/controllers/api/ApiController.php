<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Referral\ProfileModel;
use App\Services\ActiveUserService;
use App\Core\Auth;
use App\Models\Admin\Restaurant;

class ApiController extends Controller
{
    private $profileModel;
    private $restaurantModel;

    public function __construct()
    {
        // Note: These models might need their own `require_once` in api.php if autoloading fails.
        $this->profileModel = new ProfileModel();
        $this->restaurantModel = new Restaurant();
    }

    public function getAgents()
    {
        header('Content-Type: application/json');
        $userModel = new \App\Models\User\User();
        $agents = $userModel->getAllAgentsDetails();
        echo json_encode($agents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function formatWorkingHours($workingHours)
    {
        $formatted = [];
        foreach ($workingHours as $day => $hours) {
            if (!empty($hours['is_closed'])) {
                $formatted[$day] = 'مغلق';
            } else {
                $open = $hours['open_time'] ?? '';
                $close = $hours['close_time'] ?? '';
                
                if (empty($open) && empty($close)) {
                    $formatted[$day] = 'غير محدد';
                } else {
                    $formatted[$day] = $open . ' - ' . $close;
                }
            }
        }
        return $formatted;
    }
    
    public function heartbeat()
    {
        header('Content-Type: application/json');
        
        $userId = Auth::getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        require_once APPROOT . '/services/ActiveUserService.php';
        $activeUserService = new ActiveUserService();
        $activeUserService->recordUserActivity($userId);
        
        echo json_encode(['status' => 'ok']);
    }

    public function createRestaurant()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        // Decode JSON body instead of using $_POST
        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'name_ar' => $input['name_ar'] ?? null,
            'name_en' => $input['name_en'] ?? null,
            'category' => $input['category'] ?? null,
            'governorate' => $input['governorate'] ?? null,
            'city' => $input['city'] ?? null,
            'address' => $input['address'] ?? null,
            'is_chain' => isset($input['is_chain']) ? (int)$input['is_chain'] : 0,
            'num_stores' => isset($input['num_stores']) ? (int)$input['num_stores'] : null,
            'contact_name' => $input['contact_name'] ?? null,
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'pdf_path' => null,
            'referred_by_user_id' => null // Initialize
        ];

        // Handle referral
        if (!empty($input['ref'])) {
            $userModel = new \App\Models\User\User();
            $referringUser = $userModel->findByUsername($input['ref']);
            if ($referringUser) {
                $data['referred_by_user_id'] = $referringUser['id'];
            }
        }

        // Basic validation
        if (empty($data['name_en'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'English name is required.']);
            return;
        }

        $restaurantId = $this->restaurantModel->create($data);

        if ($restaurantId) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Restaurant created successfully. You can now upload a PDF.',
                'restaurant_id' => $restaurantId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create restaurant in database.']);
        }
    }

    public function updateRestaurantPdf($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $restaurant = $this->restaurantModel->getById($id);
        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Restaurant not found.']);
            return;
        }

        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0) {
            $uploadDir = APPROOT . '/uploads/pdfs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['pdf']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
                // If there was an old PDF, delete it
                if ($restaurant['pdf_path'] && file_exists($uploadDir . $restaurant['pdf_path'])) {
                    unlink($uploadDir . $restaurant['pdf_path']);
                }
                
                // Update the database with the new filename
                if ($this->restaurantModel->updatePdfPath($id, $fileName)) {
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'PDF uploaded and linked to restaurant successfully.',
                        'pdf_path' => $fileName
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to update database with new PDF path.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to upload PDF.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No PDF file was uploaded or an error occurred.']);
        }
    }

    public function getReferredRestaurants($marketerId)
    {
        header('Content-Type: application/json');
        
        $restaurants = $this->profileModel->getReferredRestaurantsByMarketer($marketerId);
        
        if ($restaurants === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to retrieve restaurants.']);
            return;
        }
        
        echo json_encode(['success' => true, 'restaurants' => $restaurants], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
