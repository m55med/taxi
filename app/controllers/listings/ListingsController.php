<?php

namespace App\Controllers\Listings;

use App\Core\Auth;
use App\Core\Controller;

class ListingsController extends Controller
{
    private $listingModel;
    private $ticketCategoryModel;
    private $userModel;
    private $platformModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        
        // These models will be created/used in the next steps
        $this->listingModel = $this->model('listings/ListingModel');
        $this->ticketCategoryModel = $this->model('tickets/Category');
        $this->userModel = $this->model('user/User'); 
        $this->platformModel = $this->model('admin/Platform');
    }

    /**
     * Display the main tickets listing page.
     */
    public function tickets()
    {
        $this->authorize('listings/tickets');
        
        $data = [
            'page_main_title' => 'All Tickets',
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
            'platforms' => $this->platformModel->getAll(),
            'users' => $this->userModel->getAllUsers(),
        ];

        $this->view('listings/tickets', $data);
    }

    /**
     * API endpoint to fetch filtered tickets.
     */
    public function get_tickets_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/tickets');
        echo json_encode($this->listingModel->getFilteredTickets($_GET));
    }

    /**
     * Display the outgoing calls listing page.
     */
    public function outgoing_calls()
    {
        $this->authorize('listings/outgoing_calls');

        $data = [
            'page_main_title' => 'Outgoing Calls',
            'ticket_categories' => $this->ticketCategoryModel->getAllCategoriesWithSubcategoriesAndCodes(),
             'users' => $this->userModel->getAllUsers(),
        ];

        $this->view('listings/outgoing_calls', $data);
    }

    /**
     * API endpoint to fetch filtered outgoing calls.
     */
    public function get_outgoing_calls_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/outgoing_calls');
        echo json_encode($this->listingModel->getFilteredOutgoingCalls($_GET));
    }

    /**
     * Display the incoming calls listing page.
     */
    public function incoming_calls()
    {
        $this->authorize('listings/incoming_calls');

        $data = [
            'page_main_title' => 'Incoming Calls',
            'users' => $this->userModel->getAllUsers(),
        ];
        
        $this->view('listings/incoming_calls', $data);
    }

    /**
     * API endpoint to fetch filtered incoming calls.
     */
    public function get_incoming_calls_api()
    {
        header('Content-Type: application/json');
        $this->authorize('listings/incoming_calls');
        echo json_encode($this->listingModel->getFilteredIncomingCalls($_GET));
    }
} 