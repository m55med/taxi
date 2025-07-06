<?php

namespace App\Controllers\Quality;

use App\Core\Auth;
use App\Core\Controller;

class QualityController extends Controller
{
    private $qualityModel;
    private $ticketCategoryModel;


    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        // We will create this model in the next step
        $this->qualityModel = $this->model('quality/QualityModel');
        $this->ticketCategoryModel = $this->model('tickets/Category');

    }

    /**
     * Display the main reviews page.
     */
    public function reviews()
    {
        // Authorization check (example: only admin and quality_manager can access)
        $this->authorize(['admin', 'quality_manager', 'Team_leader']);

        // Fetch data needed for filters, like categories
        $ticket_categories = $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes();

        $data = [
            'page_main_title' => 'All Reviews',
            'ticket_categories' => $ticket_categories,
        ];

        $this->view('quality/reviews', $data);
    }

    /**
     * API endpoint to fetch filtered reviews.
     */
    public function get_reviews_api()
    {
        header('Content-Type: application/json');
        $this->authorize(['admin', 'quality_manager', 'Team_leader']);
        
        $filters = $_GET; // Using GET parameters for filtering
        
        $reviews = $this->qualityModel->getFilteredReviews($filters);
        
        echo json_encode($reviews);
    }

    /**
     * Display the main discussions page.
     */
    public function discussions()
    {
        // Authorization check
        $this->authorize(['admin', 'quality_manager', 'Team_leader']);

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
        $this->authorize(['admin', 'quality_manager', 'Team_leader']);

        $filters = $_GET;

        $discussions = $this->qualityModel->getFilteredDiscussions($filters);
        
        echo json_encode($discussions);
    }

} 