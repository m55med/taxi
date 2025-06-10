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
        Auth::requireLogin();
        
        $user_role = Auth::getUserRole();
        $user_id = Auth::getUserId();

        if (!in_array($user_role, ['admin', 'marketer'])) {
             http_response_code(403);
             require_once APPROOT . '/../app/views/errors/403.php';
             exit;
        }

        // Get filter values from GET request
        $filters = [
            'marketer_id' => filter_input(INPUT_GET, 'marketer_id', FILTER_VALIDATE_INT),
            'date_from' => isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : null,
            'date_to' => isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : null
        ];

        $data = [
            'page_main_title' => 'لوحة تحكم التسويق',
            'user_role' => $user_role,
            'referral_link' => '',
            'visits' => [],
            'stats' => [],
            'marketers' => [],
            'filters' => $filters
        ];

        $stats_filters = $filters; // Copy filters for stats query

        if ($user_role === 'marketer') {
            // Generate personal referral link
            $data['referral_link'] = BASE_PATH . '/referral?id=' . $user_id;
            
            // Fetch data for this marketer
            $data['visits'] = $this->referralModel->getVisitsForMarketer($user_id, $filters);
            
            // Fetch stats for this marketer
            $stats_filters['user_id'] = $user_id;
            $data['stats'] = $this->referralModel->getSummaryStats($stats_filters);

        } elseif ($user_role === 'admin') {
            // Fetch all data for admin based on filters
            $data['visits'] = $this->referralModel->getAllVisits($filters);
            
            // Fetch stats for admin based on filters
            $data['stats'] = $this->referralModel->getSummaryStats($filters);

            // Fetch list of marketers for the filter dropdown
            $data['marketers'] = $this->referralModel->getMarketers();
        }

        $this->view('referral/dashboard', $data);
    }
} 