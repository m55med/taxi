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
        $this->profileModel = new ProfileModel();
        $this->restaurantModel = new Restaurant();
    }

    public function getAgents()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *'); // Allow requests from any origin

        $agentsWithUsers = $this->profileModel->getAllAgentsWithUsers();
        
        $response_data = [];

        foreach ($agentsWithUsers as $agent) {
            if (empty($agent['agent_id'])) {
                continue; // Skip users who are marketers but haven't set up a profile
            }
            
            $workingHours = $this->profileModel->getWorkingHoursByAgentId($agent['agent_id']);
            
            $agent_data = [
                'name' => $agent['username'],
                'coordinates' => [
                    'latitude' => $agent['latitude'] ?? null,
                    'longitude' => $agent['longitude'] ?? null,
                ],
                'google_map_url' => $agent['map_url'],
                'phone' => $agent['phone'],
                'service_type' => $agent['is_online_only'] ? 'اونلاين فقط' : 'نقاط شحن',
                'address' => $agent['state'],
                'working_hours' => $this->formatWorkingHours($workingHours),
            ];

            $response_data[] = $agent_data;
        }

        echo json_encode($response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

    // دالة createRestaurant بعد تعديل مسار رفع PDF
public function createRestaurant()
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        return;
    }

    $data = [
        'name_ar' => $_POST['name_ar'] ?? null,
        'name_en' => $_POST['name_en'] ?? null,
        'category' => $_POST['category'] ?? null,
        'governorate' => $_POST['governorate'] ?? null,
        'city' => $_POST['city'] ?? null,
        'address' => $_POST['address'] ?? null,
        'is_chain' => isset($_POST['is_chain']) ? (int)$_POST['is_chain'] : 0,
        'num_stores' => isset($_POST['num_stores']) ? (int)$_POST['num_stores'] : null,
        'contact_name' => $_POST['contact_name'] ?? null,
        'email' => $_POST['email'] ?? null,
        'phone' => $_POST['phone'] ?? null,
        'pdf_path' => null,
    ];

    // Handle PDF upload
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0) {
        // تعديل المسار هنا
        $uploadDir = __DIR__ . '/../../public/uploads/pdfs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['pdf']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
            $data['pdf_path'] = '/uploads/pdfs/' . $fileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload PDF.']);
            return;
        }
    }

    $restaurantId = $this->restaurantModel->create($data);

    if ($restaurantId) {
        echo json_encode([
            'success' => true,
            'message' => 'Restaurant created successfully.',
            'restaurant_id' => $restaurantId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create restaurant.']);
    }
}

} 