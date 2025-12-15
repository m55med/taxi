<?php

namespace App\Services;

class TrengoService
{
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->baseUrl = "https://app.trengo.com/api/v2";
        $this->token = $this->getToken();
    }

    /**
     * Get Trengo API token from environment variables
     * Uses the same pattern as Database.php for loading env variables
     */
    private function getToken(): ?string
    {
        $token =
            $_ENV['TRENGO_API_TOKEN']
            ?? $_SERVER['TRENGO_API_TOKEN']
            ?? getenv('TRENGO_API_TOKEN')
            ?? null;
    
        if (!$token) {
            error_log('TRENGO_API_TOKEN not found in ENV / SERVER / getenv');
            return null;
        }
    
        $token = trim($token, "\"'");
    
        if (strpos($token, 'Bearer ') !== 0) {
            $token = 'Bearer ' . $token;
        }
    
        return $token;
    }
    

    /**
     * Check if Trengo API is available (token is configured)
     */
    public function isAvailable(): bool
    {
        return $this->token !== null;
    }

    /**
     * Make a GET request to Trengo API
     */
    public function get(string $path): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }
        
        $url = $this->baseUrl . $path;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Trengo API cURL Error: " . $curlError);
            return null;
        }

        if ($httpCode >= 400) {
            error_log("Trengo API HTTP Error: Status code {$httpCode} for path: {$path}");
            if ($response) {
                error_log("Trengo API Error Response: " . substr($response, 0, 500));
            }
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Trengo API JSON Error: " . json_last_error_msg() . " for path: {$path}");
            error_log("Trengo API Raw Response: " . substr($response, 0, 500));
            return null;
        }

        return $data;
    }

    /**
     * Search for tickets by number in Trengo API
     * Trengo API may require searching instead of direct ticket ID access
     */
    public function searchTicket(string $ticketNumber): ?array
    {
        // Strategy 1: Try direct ticket ID (Trengo ticket IDs are usually numeric without leading zeros)
        $ticketId = ltrim($ticketNumber, '0');
        if (!empty($ticketId)) {
            $ticketInfo = $this->get("/tickets/{$ticketId}");
            // Check if response has 'data' key or direct ticket data
            if ($ticketInfo) {
                // If response has 'data' key, extract it
                if (isset($ticketInfo['data'])) {
                    $ticket = $ticketInfo['data'];
                } else {
                    $ticket = $ticketInfo;
                }
                
                // Verify this is the correct ticket by checking ticket_id or id
                $returnedTicketId = $ticket['ticket_id'] ?? $ticket['id'] ?? null;
                if ($returnedTicketId && (string)$returnedTicketId === $ticketId) {
                    error_log("searchTicket: Found ticket {$ticketNumber} with ID {$ticketId}");
                    return $ticket;
                }
            }
        }

        // Strategy 2: Try original ticket number
        if ($ticketNumber !== $ticketId) {
            $ticketInfo = $this->get("/tickets/{$ticketNumber}");
            if ($ticketInfo) {
                if (isset($ticketInfo['data'])) {
                    $ticket = $ticketInfo['data'];
                } else {
                    $ticket = $ticketInfo;
                }
                
                $returnedTicketId = $ticket['ticket_id'] ?? $ticket['id'] ?? null;
                if ($returnedTicketId && (string)$returnedTicketId === $ticketNumber) {
                    error_log("searchTicket: Found ticket {$ticketNumber}");
                    return $ticket;
                }
            }
        }

        error_log("searchTicket: Ticket {$ticketNumber} not found");
        return null;
    }

    /**
     * Get ticket context from Trengo API
     * This function mimics the Python get_ticket_context function
     */
    public function getTicketContext(string $ticketNumber): ?array
    {
        try {
            if (!$this->isAvailable()) {
                error_log("getTrengoTicketContext: Trengo token not available for ticket {$ticketNumber}");
                return null;
            }
            
            // Use search instead of direct access
            $ticketInfo = $this->searchTicket($ticketNumber);
            if (!$ticketInfo) {
                error_log("getTrengoTicketContext: Ticket {$ticketNumber} not found in Trengo API");
                return ["error" => "ticket not found in trengo"];
            }

            // Get ticket ID from the found ticket
            $ticketId = $ticketInfo['id'] ?? null;
            if (!$ticketId) {
                error_log("getTrengoTicketContext: Ticket {$ticketNumber} found but no ID in response");
                return ["error" => "invalid ticket data"];
            }

            // 1) Fetch ticket messages
            $messagesData = $this->get("/tickets/{$ticketId}/messages");
            if (!$messagesData || !isset($messagesData['data']) || empty($messagesData['data'])) {
                return ["error" => "no messages"];
            }

            $messages = $messagesData['data'];
            
            // Find first INBOUND message from customer (not from agent/system)
            // INBOUND messages are from customers, OUTBOUND are from agents
            $contactId = null;
            foreach ($messages as $message) {
                // Only look for INBOUND messages (from customer)
                if (isset($message['type']) && $message['type'] === 'INBOUND' && isset($message['contact']['id'])) {
                    $contactId = $message['contact']['id'];
                    error_log("getTicketContext: Found INBOUND message from contact {$contactId}");
                    break;
                }
            }
            
            // If no INBOUND found, it means ticket has no customer messages yet (unusual)
            // In this case, get contact from any message (but this should rarely happen)
            if (!$contactId) {
                error_log("getTicketContext: No INBOUND messages found for ticket {$ticketNumber}, trying any message with contact");
                foreach ($messages as $message) {
                    if (isset($message['contact']['id'])) {
                        $contactId = $message['contact']['id'];
                        error_log("getTicketContext: Using contact {$contactId} from non-INBOUND message");
                        break;
                    }
                }
            }
            
            if (!$contactId) {
                error_log("getTicketContext: No contact found in any messages for ticket {$ticketNumber}");
                return ["error" => "contact not found in messages"];
            }

            // 2) Fetch contact
            $contact = $this->get("/contacts/{$contactId}");
            if (!$contact) {
                return ["error" => "contact not found"];
            }

            // Extract contact data (may be wrapped in 'data' key or direct)
            $contactData = $contact['data'] ?? $contact;

            // 3) Get phone - check multiple possible locations
            $phone = null;
            if (isset($contactData['phone']) && !empty($contactData['phone'])) {
                $phone = $contactData['phone'];
            } elseif (isset($contact['phone']) && !empty($contact['phone'])) {
                $phone = $contact['phone'];
            }

            // 4) Detect platform from channels
            $channels = $contactData['channels'] ?? $contact['channels'] ?? null;
            $platform = $this->detectPlatformFromChannels($channels);

            // 5) Detect country
            $country = $this->detectCountryFromPhone($phone);

            return [
                "contact_id" => $contactId,
                "phone" => $phone,
                "platform" => $platform,
                "country" => $country
            ];

        } catch (\Exception $e) {
            error_log("Error getting Trengo ticket context: " . $e->getMessage());
            return ["error" => "trengo_api_error"];
        }
    }

    /**
     * Detect country from phone number
     */
    private function detectCountryFromPhone(?string $phone): ?array
    {
        if (empty($phone)) {
            return null;
        }

        $countryCodes = [
            "+973" => ["id" => 13, "name" => "Bahrain"],
            "+20"  => ["id" => 10, "name" => "Egypt"],
            "+962" => ["id" => 5, "name" => "Jordan"],
            "+965" => ["id" => 8, "name" => "Kuwait"],
            "+961" => ["id" => 9, "name" => "Lebanon"],
            "+968" => ["id" => 7, "name" => "Oman"],
            "+974" => ["id" => 6, "name" => "Qatar"],
            "+963" => ["id" => 11, "name" => "Syria"],
            "+263" => ["id" => 12, "name" => "Zimbabwe"],
        ];

        foreach ($countryCodes as $code => $data) {
            if (strpos($phone, $code) === 0) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Detect platform from contact channels
     * Prioritizes active channels and finds the first valid one
     */
    private function detectPlatformFromChannels(?array $channels): ?array
    {
        if (empty($channels) || !is_array($channels)) {
            return null;
        }

        $platforms = [
            "CALL" => ["id" => 11, "name" => "Call"],
            "EMAIL" => ["id" => 12, "name" => "Email"],
            "FACEBOOK" => ["id" => 8, "name" => "Facebook"],
            "INSTAGRAM" => ["id" => 9, "name" => "instagram"],
            "TELEGRAM" => ["id" => 10, "name" => "Telegram"],
            "WHATSAPP" => ["id" => 7, "name" => "Whatsapp"],
            "VIP" => ["id" => 5, "name" => "VIP 👑"],
            "WA_BUSINESS" => ["id" => 7, "name" => "Whatsapp"],
        ];

        // First, try to find an ACTIVE channel
        foreach ($channels as $channel) {
            if (!isset($channel['type'])) {
                continue;
            }
            
            // Prefer ACTIVE channels
            $status = isset($channel['status']) ? strtoupper($channel['status']) : '';
            if ($status === 'ACTIVE') {
                $channelType = strtoupper($channel['type']);
                if (isset($platforms[$channelType])) {
                    return $platforms[$channelType];
                }
            }
        }

        // If no active channel found, use first channel with valid type
        foreach ($channels as $channel) {
            if (!isset($channel['type'])) {
                continue;
            }
            
            $channelType = strtoupper($channel['type']);
            if (isset($platforms[$channelType])) {
                return $platforms[$channelType];
            }
        }

        return null;
    }

    /**
     * Get ticket messages with pagination
     * @param string $ticketNumber The ticket number
     * @param int $page Page number (default: 1)
     * @return array|null Array containing messages data and meta, or null on failure
     */
    public function getTicketMessages(string $ticketNumber, int $page = 1): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        // Get ticket ID first
        $ticketInfo = $this->searchTicket($ticketNumber);
        if (!$ticketInfo) {
            return null;
        }

        $ticketId = $ticketInfo['id'] ?? null;
        if (!$ticketId) {
            return null;
        }

        // Fetch messages with pagination
        $result = $this->get("/tickets/{$ticketId}/messages?page={$page}");
        if (!$result || !isset($result['data'])) {
            return null;
        }

        return [
            'messages' => $result['data'],
            'meta' => $result['meta'] ?? null,
            'links' => $result['links'] ?? null
        ];
    }

    /**
     * Get other tickets for the same contact
     * @param int $contactId The contact ID
     * @param int $page Page number (default: 1)
     * @param int $limit Number of tickets per page (default: 10)
     * @return array|null Array containing tickets data, or null on failure
     */
    public function getContactTickets(int $contactId, int $page = 1, int $limit = 10): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $result = $this->get("/tickets?page={$page}&contact_id={$contactId}&sort=-date&per_page={$limit}");
        if (!$result || !isset($result['data'])) {
            return null;
        }

        return [
            'tickets' => $result['data'],
            'meta' => $result['meta'] ?? null,
            'links' => $result['links'] ?? null
        ];
    }

    /**
     * Get contact information by ID
     * @param int $contactId The contact ID
     * @return array|null Contact data or null on failure
     */
    public function getContact(int $contactId): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $result = $this->get("/contacts/{$contactId}");
        if (!$result) {
            return null;
        }

        // Return direct data (not wrapped in 'data' key for single contact)
        return $result;
    }
}

