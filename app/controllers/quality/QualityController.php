<?php

namespace App\Controllers\Quality;

use App\Core\Auth;
use App\Core\Controller;

class QualityController extends Controller
{
    private $qualityModel;
    private $ticketCategoryModel;
    private $userModel;


    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        // We will create this model in the next step
        $this->qualityModel = $this->model('Quality/QualityModel');
        $this->ticketCategoryModel = $this->model('Tickets/Category');
        $this->userModel = $this->model('User/User'); // Load User model

    }

    /**
     * Display the main reviews page.
     */
    public function reviews()
    {
        // Authorization check: allow all relevant roles to access the page.
        // The model will handle filtering the data based on the specific role.
        $this->authorize(['admin', 'quality_manager', 'Team_leader', 'developer', 'agent']);

        // Fetch data needed for filters, like categories
        $ticket_categories = $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes();
        
        // Fetch users for the 'reviewed person' filter
        $agents = $this->userModel->getUsersByRoles(['agent', 'Team_leader']);

        // Get current user session data
        $currentUser = $_SESSION['user'] ?? [];
        $userRole = $currentUser['role_name'] ?? Auth::getUserRole() ?? 'guest';
        $userId = $currentUser['id'] ?? Auth::getUserId() ?? 0;

        $data = [
            'page_main_title' => 'All Reviews',
            'ticket_categories' => $ticket_categories,
            'agents' => $agents, // Pass agents to the view
            'current_user' => $currentUser,
            'user_role' => $userRole,
            'user_id' => $userId,
        ];

        $this->view('quality/reviews', $data);
    }

    /**
     * API endpoint to fetch filtered reviews.
     */
    public function get_reviews_api()
    {
        header('Content-Type: application/json');
        
        // Debug mode for testing
        if (isset($_GET['debug_skip_auth']) && $_GET['debug_skip_auth'] === 'yes') {
            $debug_info = [
                'session_role' => \App\Core\Auth::getUserRole() ?? 'not_set',
                'session_user_id' => \App\Core\Auth::getUserId() ?? 'not_set',
                'session_data' => $_SESSION['user'] ?? 'no_user_session',
                'timestamp' => date('Y-m-d H:i:s'),
                'filters' => $_GET
            ];
            
            try {
                $reviews = $this->qualityModel->getFilteredReviews($_GET);
                echo json_encode([
                    'debug' => $debug_info,
                    'reviews' => $reviews,
                    'message' => 'Debug mode - auth bypassed'
                ]);
                return;
            } catch (\Exception $e) {
                echo json_encode([
                    'debug' => $debug_info,
                    'error' => $e->getMessage(),
                    'message' => 'Debug mode - error occurred'
                ]);
                return;
            }
        }
        
        // Normal authorization check: allow all relevant roles. The model handles the logic.
        $this->authorize(['admin', 'quality_manager', 'Team_leader', 'developer', 'agent']);
        
        $filters = $_GET; // Using GET parameters for filtering
        
        // Add debug information for troubleshooting
        $debug_info = [
            'session_role' => \App\Core\Auth::getUserRole() ?? 'not_set',
            'session_user_id' => \App\Core\Auth::getUserId() ?? 'not_set',
            'session_data' => $_SESSION['user'] ?? 'no_user_session',
            'timestamp' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];
        
        try {
            $reviews = $this->qualityModel->getFilteredReviews($filters);
            
            // Always include debug info in development/troubleshooting
            if (is_array($reviews) && isset($reviews['error'])) {
                // Model returned an error
                echo json_encode([
                    'error' => $reviews['error'],
                    'debug' => $debug_info,
                    'reviews' => [],
                    'message' => 'Model returned error: ' . $reviews['error']
                ]);
            } elseif (is_array($reviews) && count($reviews) > 0) {
                // Success with data
                echo json_encode([
                    'reviews' => $reviews,
                    'debug' => $debug_info,
                    'message' => 'Success: ' . count($reviews) . ' reviews found'
                ]);
            } else {
                // Empty result
                echo json_encode([
                    'reviews' => [],
                    'debug' => $debug_info,
                    'message' => 'No reviews found with current filters'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'error' => $e->getMessage(),
                'debug' => $debug_info,
                'reviews' => [],
                'message' => 'Exception occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the main discussions page.
     */
    public function discussions()
    {
        // Authorization check: allow all relevant roles. The model handles the logic.
        $this->authorize(['admin', 'quality_manager', 'Team_leader', 'developer', 'agent']);

        // Fetch data needed for filters, just like the reviews page
        $ticket_categories = $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes();

        $data = [
            'page_main_title' => 'All Discussions',
            'ticket_categories' => $ticket_categories,
        ];

        $this->view('quality/discussions', $data);
    }

    /**
     * API endpoint to fetch filtered discussions.
     */
    public function get_discussions_api()
    {
        header('Content-Type: application/json');
        // Authorization check: allow all relevant roles. The model handles the logic.
        $this->authorize(['admin', 'quality_manager', 'Team_leader', 'developer', 'agent']);

        $filters = $_GET;

        $discussions = $this->qualityModel->getFilteredDiscussions($filters);
        
        echo json_encode($discussions);
    }

    /**
     * Update a review (admin only)
     */
    public function update_review()
    {
        header('Content-Type: application/json');
        
        // Authorization check: only admin roles can update reviews
        if (!$this->checkAjaxPermission(['admin', 'quality_manager', 'developer'])) {
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }
        
        $reviewId = $_POST['review_id'] ?? null;
        $rating = $_POST['rating'] ?? null;
        $reviewNotes = $_POST['review_notes'] ?? '';
        
        // Validation
        if (!$reviewId || !is_numeric($reviewId)) {
            echo json_encode(['success' => false, 'error' => 'Invalid review ID']);
            return;
        }
        
        if (!$rating || !is_numeric($rating) || $rating < 0 || $rating > 100) {
            echo json_encode(['success' => false, 'error' => 'Rating must be between 0 and 100']);
            return;
        }
        
        try {
            $result = $this->qualityModel->updateReview($reviewId, $rating, $reviewNotes);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a review (admin only)
     */
    public function delete_review()
    {
        header('Content-Type: application/json');
        
        // Authorization check: only admin roles can delete reviews
        if (!$this->checkAjaxPermission(['admin', 'quality_manager', 'developer'])) {
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }
        
        $reviewId = $_POST['review_id'] ?? null;
        
        // Validation
        if (!$reviewId || !is_numeric($reviewId)) {
            echo json_encode(['success' => false, 'error' => 'Invalid review ID']);
            return;
        }
        
        try {
            $result = $this->qualityModel->deleteReview($reviewId);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if user has permission for AJAX requests
     * Returns false and sends JSON error if unauthorized
     */
    private function checkAjaxPermission($allowedRoles = [])
    {
        $userRole = Auth::getUserRole();
        $userId = Auth::getUserId();

        // Check if user is logged in
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return false;
        }

        // Admin and developer always have access
        if (in_array($userRole, ['admin', 'developer', 'Quality'])) {
            return true;
        }

        // Check if user role is in allowed roles
        if (in_array($userRole, $allowedRoles)) {
            return true;
        }

        // Unauthorized
        echo json_encode([
            'success' => false,
            'error' => 'Access denied. Required roles: ' . implode(', ', $allowedRoles) . '. Your role: ' . ($userRole ?? 'Not set')
        ]);
        return false;
    }

    /**
     * API endpoint for search with pagination (New optimized endpoint).
     */
    public function search_reviews_api()
    {
        header('Content-Type: application/json');

        // Authorization check: allow all relevant roles. The model handles the logic.
        $this->authorize(['admin', 'quality_manager', 'Team_leader', 'developer', 'agent']);

        // Extract parameters
        $filters = $_GET;
        $searchQuery = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 25);

        // Validate parameters
        if ($page < 1) $page = 1;
        if ($perPage < 1 || $perPage > 100) $perPage = 25;

        // Remove search parameters from filters
        unset($filters['q'], $filters['page'], $filters['per_page']);

        try {
            $result = $this->qualityModel->searchReviews($filters, $searchQuery, $page, $perPage);

            if (isset($result['error'])) {
                echo json_encode([
                    'success' => false,
                    'error' => $result['error'],
                    'data' => []
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'data' => $result['reviews'],
                    'pagination' => [
                        'current_page' => $result['page'],
                        'per_page' => $result['per_page'],
                        'total' => $result['total'],
                        'total_pages' => $result['total_pages'],
                        'from' => ($result['page'] - 1) * $result['per_page'] + 1,
                        'to' => min($result['page'] * $result['per_page'], $result['total'])
                    ]
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * API endpoint for search suggestions.
     */
    public function search_suggestions_api()
    {
        header('Content-Type: application/json');

        // Authorization check: allow all relevant roles. The model handles the logic.
        $this->authorize(['admin', 'quality_manager', 'Team_leader', 'developer', 'agent']);

        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 5);

        if ($limit < 1 || $limit > 10) $limit = 5;

        try {
            $suggestions = $this->qualityModel->getSearchSuggestions($query, $limit);
            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'suggestions' => []
            ]);
        }
    }

} 