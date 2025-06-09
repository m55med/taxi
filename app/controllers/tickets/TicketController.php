<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\Platform;
use App\Models\Tickets\Category;
use App\Models\Tickets\Team;
use App\Models\Tickets\Country;

class TicketController extends Controller
{
    private $ticketModel;
    private $platformModel;
    private $categoryModel;
    private $teamModel;
    private $countryModel;

    public function __construct()
    {
        // Redirect if not logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        $this->ticketModel = new Ticket();
        $this->platformModel = new Platform();
        $this->categoryModel = new Category();
        $this->teamModel = new Team();
        $this->countryModel = new Country();
    }

    public function index()
    {
        // Fetch initial data for the form from the instantiated models
        $platforms = $this->platformModel->getAll();
        $categories = $this->categoryModel->getAll();
        $teamLeaders = $this->teamModel->getTeamLeaders();
        $countries = $this->countryModel->getAll();

        // Pass data to the view
        $this->view('tickets/index', [
            'platforms' => $platforms,
            'categories' => $categories,
            'teamLeaders' => $teamLeaders,
            'countries' => $countries,
        ]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            return;
        }

        // --- Validation ---
        $requiredFields = ['ticket_number', 'platform_id', 'category_id', 'subcategory_id', 'code_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['success' => false, 'message' => "Missing required field: {$field}"]);
                return;
            }
        }
        
        // --- Auto-assign Team Leader ---
        $created_by_id = $_SESSION['user_id'];
        $assigned_team_leader_id = $this->teamModel->findLeaderByMemberId($created_by_id);

        if (!$assigned_team_leader_id) {
            echo json_encode(['success' => false, 'message' => 'Could not find a team leader for the current user.']);
            return;
        }
        // Add the automatically assigned leader to the data array
        $data['assigned_team_leader_id'] = $assigned_team_leader_id;

        // Check if ticket number already exists
        if ($this->ticketModel->findByTicketNumber($data['ticket_number'])) {
            echo json_encode(['success' => false, 'message' => 'Ticket number already exists.']);
            return;
        }

        // --- Create Ticket ---
        $ticketId = $this->ticketModel->create($data);

        if ($ticketId) {
            echo json_encode(['success' => true, 'message' => 'Ticket created successfully!', 'ticket_id' => $ticketId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create ticket.']);
        }
    }

    public function update()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            return;
        }

        // --- Validation ---
        $requiredFields = ['ticket_number', 'platform_id', 'category_id', 'subcategory_id', 'code_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['success' => false, 'message' => "Missing required field: {$field}"]);
                return;
            }
        }

        // --- Update Ticket ---
        if ($this->ticketModel->update($data)) {
            echo json_encode(['success' => true, 'message' => 'Ticket updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update ticket.']);
        }
    }

    public function show($ticketNumber)
    {
        header('Content-Type: application/json');
        $ticket = $this->ticketModel->findByTicketNumber($ticketNumber);

        if ($ticket) {
            echo json_encode(['success' => true, 'ticket' => $ticket]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ticket not found.']);
        }
    }
} 