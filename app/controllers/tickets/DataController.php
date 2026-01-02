<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Models\Tickets\Category;
use App\Models\Tickets\Subcategory;
use App\Models\Tickets\Code;
use App\Models\Tickets\Platform;
use App\Models\Admin\Team;
use App\Models\Admin\Coupon;
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
        if (!isset($_SESSION['user'])) {
            // Not logged in, send a JSON error or redirect.
            // Sending JSON is better for API-like controllers.
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            exit;
        }

        $this->categoryModel = new Category();
        $this->subcategoryModel = new Subcategory();
        $this->codeModel = new Code();
        $this->couponModel = $this->model('admin/Coupon');
        $this->ticketModel = new Ticket();
    }

    public function getCouponsByCountry()
    {
        header('Content-Type: application/json');
        // FORCED DEBUGGING: Check if the function is even being reached.
        die(json_encode([
            'success' => false, 
            'message' => 'DEBUG: Reached the getCouponsByCountry function successfully.',
            'debug' => ['step' => 'controller_entrypoint']
        ]));

        $debug = [];

        try {
            if (!isset($_SESSION['user'])) {
                throw new \Exception('User not authenticated.');
            }
            $debug['auth_check'] = 'Passed for user_id: ' . $_SESSION['user_id'];

            if (!isset($_GET['country_id']) || empty($_GET['country_id'])) {
                throw new \Exception('Country ID is required.');
            }
            $countryId = (int)$_GET['country_id'];
            $debug['country_id'] = $countryId;

            $excludeIds = [];
            if (!empty($_GET['exclude_ids'])) {
                $excludeIds = array_map('intval', explode(',', $_GET['exclude_ids']));
            }
            $debug['exclude_ids'] = $excludeIds;

            if (!is_object($this->couponModel)) {
                 throw new \Exception('Coupon model is not a valid object.');
            }
            $debug['model_check'] = 'Coupon model is a valid object.';

            $result = $this->couponModel->getAvailableByCountry($countryId, $_SESSION['user_id'], $excludeIds);
            
            // Extract coupons and merge debug info
            $coupons = $result['coupons'];
            $debug = array_merge($debug, $result['debug']);

            $debug['final_coupons_count'] = count($coupons);

            echo json_encode(['success' => true, 'coupons' => $coupons, 'debug' => $debug]);

        } catch (\Exception $e) {
            $debug['EXCEPTION_MESSAGE'] = $e->getMessage();
            $debug['EXCEPTION_TRACE'] = $e->getTraceAsString();
            echo json_encode(['success' => false, 'message' => 'An error occurred.', 'coupons' => [], 'debug' => $debug]);
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