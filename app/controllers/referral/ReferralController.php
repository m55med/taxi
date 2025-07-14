<?php

namespace App\Controllers\Referral;

use App\Core\Controller;
use App\Models\Referral\Referral as ReferralModel;
use App\Core\Auth;
use App\Core\Database;

class ReferralController extends Controller
{
    private $referralModel;

    public function __construct()
    {
        // We will create the Referral model shortly
        $this->referralModel = new ReferralModel();
    }

    /**
     * Show the driver registration page (landing page).
     * Also handles logging the visit and processing form submission.
     */
    public function index()
    {
        // Add this for debugging
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $debug_info = $this->referralModel->getIpInfoForDebug($ip_address);

        // 1. Get affiliate ID if it exists
        $affiliate_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $affiliate_name_for_display = null;
        $visit_id = null; // To store the ID of the visit log

        // Initial values for data array
        $data = [
            'page_main_title' => 'تسجيل سائق جديد',
            'affiliate_id' => $affiliate_id,
            'affiliate_name_for_display' => null,
            'countries' => [],
            'car_types' => [],
            'display_message' => '',
            'display_message_type' => 'info',
            'form_full_name_value' => '',
            'form_phone_value' => '',
            'user_already_registered_in_this_visit' => false,
            'color_brand_black' => '#1a1a1a',
            'color_brand_yellow' => '#fabf0d',
            'color_brand_white' => '#ffffff',
            'color_brand_gray_default' => '#2a2a2a',
            'color_brand_gray_light' => '#3a3a3a',
            'color_brand_gray_text' => '#e0e0e0',
            'color_brand_gray_subtext' => '#8f8f8f',
            'source_specific_welcome_message' => '',
            'debug_info' => $debug_info // Pass debug info to the view
        ];

        if ($affiliate_id) {
            $affiliate_user = $this->referralModel->findUserById($affiliate_id);
            if ($affiliate_user) {
                $data['affiliate_name_for_display'] = $affiliate_user['username'];
            } else {
                $data['affiliate_id'] = null;
                $affiliate_id = null;
            }
        }

        // Log the visit and get the visit ID
        // We will update this record upon registration attempt/success
        $visit_id = $this->referralModel->logVisit($affiliate_id, 'form_opened');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_referral'])) {
            // Form was submitted, handle registration logic
            $this->handleRegistration($data, $affiliate_id, $visit_id);
        }

        // Fetch data for form dropdowns
        $data['countries'] = $this->referralModel->getCountries();
        $data['car_types'] = $this->referralModel->getCarTypes();

        // 4. Render the view
        $this->view('referral/register', $data);
    }

    private function handleRegistration(&$data, $affiliate_id, $visit_id)
    {
        // Sanitize and retrieve POST data
        $full_name = strip_tags($_POST['full_name'] ?? '');
        $phone = strip_tags($_POST['phone'] ?? '');
        $country_id = filter_input(INPUT_POST, 'country_id', FILTER_VALIDATE_INT);
        $car_type_id = filter_input(INPUT_POST, 'car_type_id', FILTER_VALIDATE_INT);

        // Keep form values in case of error
        $data['form_full_name_value'] = $full_name;
        $data['form_phone_value'] = $phone;

        $this->referralModel->updateVisitStatus($visit_id, 'attempted');

        // Validation
        if (empty($full_name) || empty($phone) || empty($country_id) || empty($car_type_id)) {
            $data['display_message'] = 'يرجى ملء جميع الحقول المطلوبة.';
            $data['display_message_type'] = 'error';
            return;
        }

        // Check for existing driver
        if ($this->referralModel->findDriverByPhone($phone)) {
            $this->referralModel->updateVisitStatus($visit_id, 'duplicate_phone');
            $data['display_message'] = 'أنت مسجل بالفعل. يرجى التواصل معنا على واتساب: <a href="https://wa.me/96897653339" target="_blank">+96897653339</a>';
            $data['display_message_type'] = 'error';
            $data['user_already_registered_in_this_visit'] = true;
            return;
        }

        // Attempt to create driver
        $driver_data = [
            'name' => $full_name,
            'phone' => $phone,
            'country_id' => $country_id,
            'car_type_id' => $car_type_id,
            'data_source' => 'referral',
            'added_by' => $affiliate_id,
        ];

        $new_driver_id = $this->referralModel->createDriver($driver_data);

        if ($new_driver_id) {
            $this->referralModel->updateVisitOnSuccess($visit_id, $new_driver_id);
            $data['display_message'] = 'تم تسجيلك بنجاح! سيتم التواصل معك قريباً.';
            $data['display_message_type'] = 'success';
            $data['user_already_registered_in_this_visit'] = true; // Hide form after success
        } else {
            $this->referralModel->updateVisitStatus($visit_id, 'failed_other');
            $data['display_message'] = 'حدث خطأ غير متوقع أثناء التسجيل. يرجى المحاولة مرة أخرى.';
            $data['display_message_type'] = 'error';
        }
    }

    /**
     * Displays the referral dashboard for admins and marketers.
     */
    public function dashboard()
    {
        $this->authorize('Referral/dashboard');

        $dashboardModel = $this->model('Referral/DashboardModel');
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role_name'] ?? 'marketer'; // Default to marketer for safety

        if ($userRole === 'admin') {
            $this->adminDashboard($dashboardModel);
        } else {
            $this->marketerDashboard($dashboardModel, $userId);
        }
    }

    private function adminDashboard($dashboardModel)
    {
        $filters = [
            'marketer_id' => filter_input(INPUT_GET, 'marketer_id', FILTER_VALIDATE_INT),
            'start_date' => filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'end_date' => filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        $allMarketers = $this->model('Referral/ProfileModel')->getAllAgentsWithUsers();

        // Calculate aggregate stats for summary cards
        $total_marketers = count($allMarketers);
        $total_visits = array_sum(array_column($allMarketers, 'total_visits'));
        $total_registrations = array_sum(array_column($allMarketers, 'total_registrations'));
        $overall_conversion_rate = ($total_visits > 0) ? ($total_registrations / $total_visits) * 100 : 0;

        $summary_stats = [
            'total_marketers' => $total_marketers,
            'total_visits' => $total_visits,
            'total_registrations' => $total_registrations,
            'conversion_rate' => $overall_conversion_rate
        ];

        $data = [
            'page_main_title' => 'Admin - Referral Dashboard',
            'marketers' => $allMarketers,
            'summary_stats' => $summary_stats,
            'filters' => $filters
        ];

        $this->view('referral/dashboard/admin_dashboard', $data);
    }

    private function marketerDashboard($dashboardModel, $userId)
    {
        $profileModel = $this->model('Referral/ProfileModel');

        $filters = [
            'marketer_id' => $userId, // Marketer can only see their own data
            'start_date' => filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'end_date' => filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        $dashboardStats = $dashboardModel->getDashboardStats($filters);
        $visits = $dashboardModel->getVisitsForMarketer($userId, $filters);
        $agentProfile = $profileModel->getAgentByUserId($userId);
        $workingHours = ($agentProfile) ? $profileModel->getWorkingHoursByAgentId($agentProfile['id']) : [];

        $data = [
            'page_main_title' => 'لوحة تحكم المناديب',
            'dashboardStats' => $dashboardStats,
            'visits' => $visits,
            'filters' => $filters,
            'agentProfile' => $agentProfile,
            'working_hours' => $workingHours,
            'referral_link' => BASE_PATH . '/referral/register?ref=' . $_SESSION['username'],
            'user_role' => 'marketer'
        ];

        $this->view('referral/dashboard/marketer_dashboard', $data);
    }

    /**
     * Shows the profile editing form for a specific user (for admins).
     * @param int $userId The ID of the user to edit.
     */
    public function editProfile(int $userId)
    {
        $this->authorize('Referral/editProfile');

        $profileModel = $this->model('Referral/ProfileModel');
        $user = $this->referralModel->findUserById($userId);

        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            redirect('referral/dashboard');
            return;
        }

        $agentProfile = $profileModel->getAgentByUserId($userId);

        // Always fetch or generate working hours structure.
        // If agent exists, get their hours. If not, get a default blank structure.
        $agentIdForHours = $agentProfile ? $agentProfile['id'] : 0; // Use 0 or null for non-existent agent
        $workingHours = $profileModel->getWorkingHoursByAgentId($agentIdForHours);

        $data = [
            'page_main_title' => 'Edit Profile for ' . htmlspecialchars($user['username']),
            'user' => $user,
            'agentProfile' => $agentProfile, // This can be null if no profile exists
            'working_hours' => $workingHours,
        ];

        $this->view('referral/profile/edit_profile', $data);
    }

    /**
     * Shows a detailed view of a single marketer's visits (for admins).
     * @param int $userId The ID of the marketer to view.
     */
    public function marketerDetails(int $userId)
    {
        $this->authorize('Referral/dashboard'); // Reuse admin dashboard permission

        // Use the ReferralModel which contains the findUserById method
        $referralModel = $this->model('Referral/Referral');
        $dashboardModel = $this->model('Referral/DashboardModel');

        $marketer = $referralModel->findUserById($userId);

        if (!$marketer) {
            $_SESSION['error'] = 'Marketer not found.';
            redirect('referral/dashboard');
            return;
        }

        // We can add filtering later if needed
        $filters = ['start_date' => null, 'end_date' => null];
        $visits = $dashboardModel->getVisitsForMarketer($userId, $filters);

        $data = [
            'page_main_title' => 'Visit Details for ' . htmlspecialchars($marketer['username']),
            'marketer' => $marketer,
            'visits' => $visits,
            'filters' => $filters
        ];

        $this->view('referral/dashboard/marketer_details', $data);
    }

    public function saveAgentProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        // Determine which user's profile is being saved.
        $targetUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $currentUserId = $_SESSION['user_id'];
        $currentUserRole = $_SESSION['role_name'];

        // If no target user ID is provided, it's the current user editing their own profile.
        if (empty($targetUserId)) {
            $targetUserId = $currentUserId;
        }

        // Authorization check: 
        // 1. You can edit your own profile.
        // 2. An admin can edit anyone's profile.
        if ($targetUserId != $currentUserId && $currentUserRole !== 'admin') {
            $_SESSION['error'] = 'You are not authorized to perform this action.';
            redirect('referral/dashboard');
            return;
        }

        // Authorize based on who is being edited
        // A marketer saving their own profile
        if ($targetUserId == $currentUserId) {
            $this->authorize('Referral/saveAgentProfile');
        } else { // An admin saving another profile
            $this->authorize('Referral/editProfile');
        }

        $agentModel = $this->model('Referral/ProfileModel');

        $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $google_map_url_input = trim(filter_input(INPUT_POST, 'google_map_url', FILTER_SANITIZE_URL));
        $map_url_to_save = null;

        if (!empty($google_map_url_input)) {
            $resolvedUrl = $this->resolveGoogleMapsShortLink($google_map_url_input);
            $coordinates = $this->extractCoordinates($resolvedUrl);

            if ($coordinates) {
                $latitude = $coordinates['latitude'];
                $longitude = $coordinates['longitude'];
                $map_url_to_save = $resolvedUrl;
            } else {
                $_SESSION['error'] = 'Could not extract coordinates from the link. Please check the link and try again.';
                // Redirect back to the correct page
                redirect($currentUserRole === 'admin' ? 'referral/editProfile/' . $targetUserId : 'referral/dashboard');
                return;
            }
        } elseif ($latitude && $longitude) {
            $map_url_to_save = "https://www.google.com/maps?q={$latitude},{$longitude}";
        }

        $data = [
            'user_id' => $targetUserId,
            'state' => trim(htmlspecialchars($_POST['state'] ?? '')),
            'phone' => trim(htmlspecialchars($_POST['phone'] ?? '')),
            'is_online_only' => isset($_POST['is_online_only']) ? 1 : 0,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'map_url' => $map_url_to_save
        ];

        // Basic validation
        if (empty($data['state']) || empty($data['phone'])) {
            $_SESSION['error'] = 'State and Phone are required fields.';
            redirect($currentUserRole === 'admin' ? 'referral/editProfile/' . $targetUserId : 'referral/dashboard');
            return;
        }

        $profileSaved = $agentModel->createOrUpdateAgent($data);

        $agentProfile = $agentModel->getAgentByUserId($targetUserId);
        if ($profileSaved && $agentProfile && isset($_POST['working_hours'])) {
            $agentModel->saveWorkingHours($agentProfile['id'], $_POST['working_hours']);
        }

        if ($profileSaved) {
            $_SESSION['success'] = 'Profile updated successfully.';
        } else {
            $_SESSION['error'] = 'An error occurred while updating the profile.';
        }

        // Redirect back to the appropriate page
        redirect($currentUserRole === 'admin' ? 'referral/dashboard' : 'referral/dashboard');
    }

    private function resolveGoogleMapsShortLink($shortUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $shortUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // يسمح بمتابعة التحويلات
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // لا نحتاج لمحتوى الصفحة

        curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return $finalUrl;
    }

    // دالة لاستخراج الإحداثيات من الرابط الكامل
    private function extractCoordinates($url)
    {
        // New pattern for /search/lat,lng and /place/name/@lat,lng
        if (preg_match('/@([\d\.-]+),([\d\.-]+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        // Pattern for /maps/search/lat,+lng
        if (preg_match('/\/search\/([\d\.-]+),\+?([\d\.-]+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        // From !3d and !4d parameters
        if (preg_match('/!3d([\d\.-]+)!4d([\d\.-]+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        // From q=lat,long parameter
        if (preg_match('/[?&]q=([\d\.-]+),([\d\.-]+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        return null;
    }
}