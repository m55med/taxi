<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\TrengoService;

class TrengoController extends Controller
{
    private $trengoService;

    public function __construct()
    {
        parent::__construct();
        $this->trengoService = new TrengoService();
    }

    /**
     * Show Trengo ticket viewer page
     */
    public function viewer()
    {
        // Check if user is authenticated
        if (!Auth::isLoggedIn()) {
            redirect('login');
        }

        // Get ticket number from URL parameter
        $ticketNumber = $_GET['ticket'] ?? null;

        $data = [
            'title' => 'Trengo Ticket Viewer',
            'ticket_number' => $ticketNumber
        ];

        $this->view('tickets/trengo_viewer', $data);
    }

    /**
     * API endpoint to get ticket messages
     * GET /tickets/trengo/messages/{ticketNumber}?page=1
     */
    public function getMessages($ticketNumber)
    {
        header('Content-Type: application/json');

        // Check if user is authenticated
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if (!$this->trengoService->isAvailable()) {
            http_response_code(503);
            echo json_encode(['error' => 'Trengo API is not available']);
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        $result = $this->trengoService->getTicketMessages($ticketNumber, $page);
        
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Ticket not found or no messages available']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $result
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * API endpoint to get contact's other tickets
     * GET /tickets/trengo/contact-tickets/{contactId}?page=1&limit=10
     */
    public function getContactTickets($contactId)
    {
        header('Content-Type: application/json');

        // Check if user is authenticated
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if (!$this->trengoService->isAvailable()) {
            http_response_code(503);
            echo json_encode(['error' => 'Trengo API is not available']);
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $result = $this->trengoService->getContactTickets((int)$contactId, $page, $limit);
        
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'No tickets found for this contact']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $result
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * API endpoint to get ticket context (contact info, phone, platform, country)
     * GET /tickets/trengo/context/{ticketNumber}
     */
    public function getContext($ticketNumber)
    {
        header('Content-Type: application/json');

        // Check if user is authenticated
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if (!$this->trengoService->isAvailable()) {
            http_response_code(503);
            echo json_encode(['error' => 'Trengo API is not available']);
            return;
        }

        $result = $this->trengoService->getTicketContext($ticketNumber);
        
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Ticket context not available']);
            return;
        }

        if (isset($result['error'])) {
            http_response_code(404);
            echo json_encode(['error' => $result['error']]);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $result
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * API endpoint to check if tickets exist in our database
     * GET /tickets/trengo/check-exists?ticket_numbers=123,456,789
     * Returns mapping of trengo_ticket_number => database_ticket_id
     */
    public function checkExists()
    {
        header('Content-Type: application/json');

        // Check if user is authenticated
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $ticketNumbers = $_GET['ticket_numbers'] ?? '';
        if (empty($ticketNumbers)) {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        // Parse ticket numbers (Trengo ticket IDs)
        $numbers = array_map('trim', explode(',', $ticketNumbers));
        $numbers = array_filter($numbers, 'is_numeric');

        if (empty($numbers)) {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        // Check which tickets exist in database
        // Use direct SQL to get the correct ticket.id (not ticket_detail.id)
        try {
            $db = \App\Core\Database::getInstance();
            $result = [];
            
            foreach ($numbers as $trengoTicketNumber) {
                // Query to get ticket.id by ticket_number
                $sql = "SELECT id FROM tickets WHERE ticket_number = :ticket_number LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([':ticket_number' => $trengoTicketNumber]);
                $ticketId = $stmt->fetchColumn();
                
                if ($ticketId) {
                    $result[$trengoTicketNumber] = (int)$ticketId;
                    error_log("checkExists: Trengo #{$trengoTicketNumber} → Ticket ID: {$ticketId}");
                }
            }
        } catch (\Exception $e) {
            error_log("Error in checkExists: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'data' => $result
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

