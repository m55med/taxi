<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Models\Tickets\Category;
use App\Models\Tickets\Subcategory;
use App\Models\Tickets\Code;
use App\Models\Tickets\Platform;
use App\Models\Tickets\Team;
use App\Models\Tickets\Coupon;
use App\Models\Tickets\Ticket;

class DataController extends Controller
{
    private $categoryModel;
    private $subcategoryModel;
    private $codeModel;
    private $couponModel;
    private $ticketModel;

    public function __construct()
    {
        // Ensure user is logged in for all data endpoints
        if (!isset($_SESSION['user_id'])) {
            // Not logged in, send a JSON error or redirect.
            // Sending JSON is better for API-like controllers.
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            exit;
        }

        $this->categoryModel = new Category();
        $this->subcategoryModel = new Subcategory();
        $this->codeModel = new Code();
        $this->couponModel = new Coupon();
        $this->ticketModel = new Ticket();
    }

    public function getCouponsByCountry()
    {
        header('Content-Type: application/json');
        if (!isset($_GET['country_id']) || empty($_GET['country_id'])) {
            echo json_encode(['success' => false, 'message' => 'Country ID is required.']);
            return;
        }

        $countryId = (int)$_GET['country_id'];
        $excludeIds = [];
        if (!empty($_GET['exclude_ids'])) {
            // Ensure IDs are integers
            $excludeIds = array_map('intval', explode(',', $_GET['exclude_ids']));
        }
        
        try {
            $coupons = $this->couponModel->getAvailableByCountry($countryId, $_SESSION['user_id'], $excludeIds);
            echo json_encode(['success' => true, 'coupons' => $coupons]);
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('Coupon Fetch Error: ' . $e->getMessage());
            // Send a generic error message to the client
            echo json_encode(['success' => false, 'message' => 'An error occurred while fetching coupons.']);
        }
    }

    public function getSubcategories()
    {
        header('Content-Type: application/json');
        if (isset($_GET['category_id'])) {
            $subcategories = $this->subcategoryModel->getByCategoryId((int)$_GET['category_id']);
            echo json_encode(['success' => true, 'subcategories' => $subcategories]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Category ID is required.']);
        }
    }

    public function getCodes()
    {
        header('Content-Type: application/json');
        if (isset($_GET['subcategory_id'])) {
            $codes = $this->codeModel->getBySubcategoryId((int)$_GET['subcategory_id']);
            echo json_encode(['success' => true, 'codes' => $codes]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Subcategory ID is required.']);
        }
    }

    public function getCategories()
    {
        header('Content-Type: application/json');
        $categories = $this->categoryModel->getAll();
        echo json_encode(['success' => true, 'categories' => $categories]);
    }

    public function getPlatforms()
    {
        $platformModel = new Platform();
        $this->sendJsonResponse($platformModel->getAll());
    }

    public function getTeamLeaders()
    {
        $teamModel = new Team();
        $this->sendJsonResponse($teamModel->getTeamLeaders());
    }

    public function getAvailableCoupons()
    {
        $countryId = $_GET['country_id'] ?? null;
        if (!$countryId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Country ID is required.'], 400);
            return;
        }

        $excludeIds = [];
        if (!empty($_GET['exclude_ids'])) {
            $excludeIds = explode(',', $_GET['exclude_ids']);
            $excludeIds = array_map('intval', $excludeIds);
            $excludeIds = array_filter($excludeIds, fn($id) => $id > 0);
        }

        $couponModel = new Coupon();
        $coupons = $couponModel->getAvailableByCountry((int)$countryId, $_SESSION['user_id'], $excludeIds);

        if (empty($coupons)) {
            $this->sendJsonResponse(['success' => true, 'coupons' => [], 'message' => 'No available coupons found for this country.']);
        } else {
            $this->sendJsonResponse(['success' => true, 'coupons' => $coupons]);
        }
    }

    public function holdCoupon()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['coupon_id'])) {
            echo json_encode(['success' => false, 'message' => 'Coupon ID is required.']);
            return;
        }

        $couponId = (int)$data['coupon_id'];
        $userId = $_SESSION['user_id'];

        if ($this->couponModel->hold($couponId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Coupon held successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Coupon is already held by another user or is invalid.']);
        }
    }

    public function releaseCoupon()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['coupon_id'])) {
            // No error needed if coupon_id is missing, just exit gracefully
            return;
        }

        $couponId = (int)$data['coupon_id'];
        $userId = $_SESSION['user_id'];
        
        $this->couponModel->release($couponId, $userId);
        
        // We don't need to send a response for release, it's a "fire and forget" action
        http_response_code(204); // No Content
    }

    public function releaseAllCoupons()
    {
        // This endpoint is designed to be called from sendBeacon, so it's kept simple.
        // It releases all coupons held by the current user.
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->couponModel->releaseAllForUser($userId);
        }
        http_response_code(204); // No Content
    }

    public function getTicket()
    {
        header('Content-Type: application/json');
        if (!isset($_GET['ticket_number']) || empty($_GET['ticket_number'])) {
            echo json_encode(['success' => false, 'message' => 'Ticket number is required.']);
            return;
        }

        $ticketNumber = $_GET['ticket_number'];
        $ticket = $this->ticketModel->findByTicketNumber($ticketNumber);

        if ($ticket) {
            echo json_encode(['success' => true, 'ticket' => $ticket]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ticket not found.']);
        }
    }
} 