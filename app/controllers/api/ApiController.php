<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Referral\ProfileModel;
use App\Services\ActiveUserService;
use App\Core\Auth;

class ApiController extends Controller
{
    private $profileModel;

    public function __construct()
    {
        $this->profileModel = new ProfileModel();
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
} 