<?php







namespace App\Models\Tickets;







use App\Core\Model;



use PDO;



use PDOException;







class Ticket extends Model



{



    public function __construct()



    {



        parent::__construct();



    }







    /**



     * Creates a new ticket and its initial detail record.



     *



     * @param array $data The data for the new ticket.



     * @param int $userId The ID of the user creating the ticket.



     * @return array|false An array containing the new ticket_id and ticket_detail_id, or false on failure.



     */



    public function createTicket(array $data, $userId)



    {



        $this->db->beginTransaction();







        try {



            // Step 1: Create the main ticket record



            $ticketSql = "INSERT INTO tickets (ticket_number, created_by) VALUES (:ticket_number, :created_by)";



            $stmt = $this->db->prepare($ticketSql);



            $stmt->execute([



                ':ticket_number' => $data['ticket_number'],



                ':created_by' => $userId



            ]);



            $ticketId = $this->db->lastInsertId();







            // Step 2: Create the first detail record, linking it to the main ticket



            $ticketDetailId = $this->addTicketDetail($ticketId, $data, $userId);







            if ($ticketDetailId) {



                $this->db->commit();



                return ['ticket_id' => $ticketId, 'ticket_detail_id' => $ticketDetailId];



            } else {



                // If addTicketDetail fails, roll back the whole transaction



                $this->db->rollBack();



                return false;



            }



        } catch (\Exception $e) {



            $this->db->rollBack();



            error_log("Error in createTicket: " . $e->getMessage());



            return false;



        }



    }







    public function addTicketDetail($ticketId, array $data, $userId)



    {



        $this->db->beginTransaction();







        try {



            // Step 1: Insert the new detail record



            $stmt = $this->db->prepare(



                "INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, edited_by)



                 VALUES (:ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :assigned_team_leader_id, :edited_by)"



            );







            $stmt->execute([



                ':ticket_id' => $ticketId,



                ':is_vip' => $data['is_vip'] ?? 0,



                ':platform_id' => $data['platform_id'],



                ':phone' => $data['phone'] ?? null,



                ':category_id' => $data['category_id'],



                ':subcategory_id' => $data['subcategory_id'],



                ':code_id' => $data['code_id'],



                ':notes' => $data['notes'] ?? null,



                ':country_id' => $data['country_id'] ?? null,



                ':assigned_team_leader_id' => $data['assigned_team_leader_id'],



                ':edited_by' => $userId



            ]);







            $ticketDetailId = $this->db->lastInsertId();







            $this->db->commit();



            



            return $ticketDetailId; // Return the new detail ID







        } catch (\Exception $e) {



            $this->db->rollBack();



            error_log("Error in addTicketDetail: " . $e->getMessage());



            return false;



        }



    }







    public function findTicketByNumber(string $ticketNumber)



    {



        $sql = "SELECT 



                    t.id, t.ticket_number, t.created_by, t.created_at,



                    td.*, -- Select all from ticket_details



                    p.name as platform_name, 



                    c.name as category_name, 



                    sc.name as subcategory_name, 



                    co.name as code_name, 



                    u_creator.username as creator_name, 



                    u_editor.username as editor_name, -- Changed from leader to editor



                    u_leader.username as leader_name



                FROM tickets t



                -- Join with the latest detail record



                JOIN ticket_details td ON t.id = td.ticket_id AND td.id = (



                    SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id



                )



                LEFT JOIN platforms p ON td.platform_id = p.id



                LEFT JOIN ticket_categories c ON td.category_id = c.id



                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id



                LEFT JOIN ticket_codes co ON td.code_id = co.id



                LEFT JOIN users u_creator ON t.created_by = u_creator.id



                LEFT JOIN users u_editor ON td.edited_by = u_editor.id -- The user who made the last change



                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id



                WHERE t.ticket_number = :ticket_number";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':ticket_number' => $ticketNumber]);



        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);







        if ($ticket) {



            $ticket['coupons'] = $this->getTicketCoupons($ticket['id']);



        }







        return $ticket ?: null;



    }







    public function findTicketByNumberOrPhone(string $searchTerm)



    {



        // First, try to find by ticket number, as it's unique and faster.



        $ticket = $this->findTicketByNumber($searchTerm);



        if ($ticket) {



            return $ticket;



        }







        // If not found, search by phone number in all ticket_details.



        // We want the ticket that had this phone number most recently.



        $sql = "SELECT t.id



                FROM tickets t



                JOIN ticket_details td ON t.id = td.ticket_id



                WHERE td.phone = :phone



                ORDER BY td.created_at DESC



                LIMIT 1";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':phone' => $searchTerm]);



        $ticketId = $stmt->fetchColumn();







        // If we found a ticket ID, use findById to get all its details.



        if ($ticketId) {



            return $this->findById($ticketId);



        }







        return null; // Return null if nothing is found.



    }







    public function getTeamLeaderForUser($userId)



    {



        $sql = "SELECT t.team_leader_id 



                FROM teams t



                JOIN team_members tm ON t.id = tm.team_id



                WHERE tm.user_id = :user_id";







        $stmt = $this->db->prepare($sql);



        $stmt->execute([':user_id' => $userId]);



        return $stmt->fetchColumn();



    }



    



    /**



     * Synchronizes the coupons for a specific ticket detail.



     * It removes old coupons for the ticket and adds the new ones.



     *



     * @param int $ticketId The main ticket ID.



     * @param int $ticketDetailId The ID of the specific ticket detail record.



     * @param array $couponIds The array of coupon IDs to associate with the ticket detail.



     * @return bool



     */



    public function syncCoupons($ticketId, $ticketDetailId, array $couponIds)



    {



        if (empty($couponIds) || empty($ticketDetailId)) {



            return true; // Nothing to do or no detail to link to.



        }







        $this->db->beginTransaction();







        try {



            // Step 1: Mark all new coupons as used and link them to the ticket



            $updateSql = "UPDATE coupons SET is_used = 1, used_in_ticket = :ticket_id, used_by = :user_id, used_at = NOW() WHERE id = :coupon_id";



            $updateStmt = $this->db->prepare($updateSql);







            // Step 2: Insert into the pivot table



            $pivotSql = "INSERT INTO ticket_coupons (ticket_id, ticket_detail_id, coupon_id) VALUES (:ticket_id, :ticket_detail_id, :coupon_id)



                         ON DUPLICATE KEY UPDATE ticket_detail_id = VALUES(ticket_detail_id)"; // Handle re-saving



            $pivotStmt = $this->db->prepare($pivotSql);







            foreach ($couponIds as $couponId) {



                if (empty($couponId)) continue;



                



                // Mark as used



                $updateStmt->execute([



                    ':ticket_id' => $ticketId,



                    ':user_id' => $_SESSION['user_id'],



                    ':coupon_id' => $couponId



                ]);







                // Insert into pivot table



                $pivotStmt->execute([



                    ':ticket_id' => $ticketId,



                    ':ticket_detail_id' => $ticketDetailId,



                    ':coupon_id' => $couponId



                ]);



            }







            $this->db->commit();



            return true;



        } catch (\Exception $e) {



            $this->db->rollBack();



            error_log("Error in syncCoupons: " . $e->getMessage());



            return false;



        }



    }







    public function findById(int $id)



    {



        // This function now gets the latest version of the ticket



        $sql = "SELECT 



                    t.id, t.ticket_number, t.created_by, t.created_at,



                    td.*,



                    p.name as platform_name, 



                    cat.name as category_name, 



                    sc.name as subcategory_name, 



                    cod.name as code_name, 



                    u_creator.username as creator_username,



                    u_editor.username as editor_username, 



                    u_leader.username as leader_name,



                    cnt.name as country_name



                FROM tickets t



                JOIN ticket_details td ON t.id = td.ticket_id AND td.id = (



                    SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id



                )



                LEFT JOIN platforms p ON td.platform_id = p.id



                LEFT JOIN ticket_categories cat ON td.category_id = cat.id



                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id



                LEFT JOIN ticket_codes cod ON td.code_id = cod.id



                LEFT JOIN users u_creator ON t.created_by = u_creator.id



                LEFT JOIN users u_editor ON td.edited_by = u_editor.id



                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id



                LEFT JOIN countries cnt ON td.country_id = cnt.id



                WHERE t.id = :id";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':id' => $id]);



        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);



        



        return $ticket ?: null;



    }







    /**



     * Find all tickets associated with a given phone number, excluding a specific ticket ID.



     */



    public function findByPhone(string $phone, int $excludeTicketId)



    {



        if (empty($phone)) {



            return [];



        }







        // This query now needs to check the ticket_details table.



        // We find the ticket_ids that have the given phone number in any of their details.



        $sql = "SELECT DISTINCT t.id, t.ticket_number, t.created_at



                FROM tickets t



                JOIN ticket_details td ON t.id = td.ticket_id



                WHERE td.phone = :phone AND t.id != :exclude_id



                ORDER BY t.created_at DESC";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([



            ':phone' => $phone,



            ':exclude_id' => $excludeTicketId



        ]);



        



        return $stmt->fetchAll(PDO::FETCH_ASSOC);



    }







    public function getTicketHistory($ticketId)



    {



        $sql = "SELECT 



                    td.*,



                    p.name as platform_name,



                    c.name as category_name,



                    sc.name as subcategory_name,



                    co.name as code_name,



                    u_editor.username as editor_name,



                    u_leader.username as leader_username,



                    country.name as country_name,



                    marketer.name as marketer_name



                FROM ticket_details td



                LEFT JOIN platforms p ON td.platform_id = p.id



                LEFT JOIN ticket_categories c ON td.category_id = c.id



                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id



                LEFT JOIN ticket_codes co ON td.code_id = co.id



                LEFT JOIN users u_editor ON td.edited_by = u_editor.id



                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id



                LEFT JOIN countries country ON td.country_id = country.id



                LEFT JOIN ticket_vip_assignments tva ON td.id = tva.ticket_detail_id



                LEFT JOIN users marketer ON tva.marketer_id = marketer.id



                WHERE td.ticket_id = :ticket_id



                ORDER BY td.created_at DESC, td.id DESC";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':ticket_id' => $ticketId]);



        return $stmt->fetchAll(PDO::FETCH_ASSOC);



    }







    public function getTicketCoupons($ticketId)



    {



        // This now gets ALL coupons ever associated with the ticket.



        // It's used for the main display area.



        $sql = "SELECT c.id, c.code, c.value, c.is_used, c.used_at, tc.ticket_detail_id



                FROM ticket_coupons tc



                JOIN coupons c ON tc.coupon_id = c.id



                WHERE tc.ticket_id = ?";







        $stmt = $this->db->prepare($sql);



        $stmt->execute([$ticketId]);



        return $stmt->fetchAll(PDO::FETCH_ASSOC);



    }







    public function getCouponsForTicketDetail($detailId)



    {



        $sql = "SELECT c.code, c.value 



                FROM coupons c



                JOIN ticket_coupons tc ON c.id = tc.coupon_id



                WHERE tc.ticket_detail_id = :detail_id";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':detail_id' => $detailId]);



        return $stmt->fetchAll(PDO::FETCH_ASSOC);



    }







    public function getRelatedTickets($phone, $currentTicketId)



    {



        if (empty($phone) || $currentTicketId === null) {



            return [];



        }







        try {



            $stmt = $this->db->prepare("SELECT id, ticket_number, created_at FROM tickets WHERE phone = :phone AND id != :current_ticket_id ORDER BY created_at DESC");



            $stmt->execute([':phone' => $phone, ':current_ticket_id' => $currentTicketId]);



            return $stmt->fetchAll(PDO::FETCH_ASSOC);



        } catch (PDOException $e) {



            error_log("Error getting related tickets: " . $e->getMessage());



            return [];



        }



    }







    public function getSuggestions(string $term)



    {



        $searchTerm = '%' . $term . '%';



        $sql = "SELECT 



                    t.id, 



                    t.ticket_number,



                    (SELECT phone FROM ticket_details WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as phone



                FROM tickets as t



                WHERE t.ticket_number LIKE :searchTerm1 



                   OR t.id IN (SELECT ticket_id FROM ticket_details WHERE phone LIKE :searchTerm2)



                ORDER BY t.created_at DESC



                LIMIT 7";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([



            ':searchTerm1' => $searchTerm,



            ':searchTerm2' => $searchTerm



        ]);



        



        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);







        // Process results to create a user-friendly label



        return array_map(function($ticket) {



            $label = $ticket['ticket_number'];



            if (!empty($ticket['phone'])) {



                $label .= ' - ' . htmlspecialchars($ticket['phone']);



            }



            return [



                'id' => $ticket['id'],



                'label' => $label



            ];



        }, $results);



    }







    public function getTicketIdFromDetailId($detailId)



    {



        $sql = "SELECT ticket_id FROM ticket_details WHERE id = :detail_id";



        try {



            $stmt = $this->db->prepare($sql);



            $stmt->execute([':detail_id' => $detailId]);



            return $stmt->fetchColumn();



        } catch (PDOException $e) {



            error_log("Error getting ticket_id from detail_id: " . $e->getMessage());



            return null;



        }



    }







    public function getVipMarketerForDetail($ticketDetailId)



    {



        $sql = "SELECT u.name 



                FROM ticket_vip_assignments tva



                JOIN users u ON tva.marketer_id = u.id



                WHERE tva.ticket_detail_id = :ticket_detail_id";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':ticket_detail_id' => $ticketDetailId]);



        return $stmt->fetch(PDO::FETCH_ASSOC);



    }







    public function findDetailById(int $detailId)



    {



        $sql = "SELECT 



                    td.*,



                    t.ticket_number,



                    t.id as ticket_id,



                    u_leader.username as assigned_team_leader_name



                FROM ticket_details td



                JOIN tickets t ON td.ticket_id = t.id



                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id



                WHERE td.id = :detail_id";



        



        $stmt = $this->db->prepare($sql);



        $stmt->execute([':detail_id' => $detailId]);



        return $stmt->fetch(PDO::FETCH_ASSOC);



    }


    public function updateTicketDetail($detailId, array $data, $userId)
    {
        try {
            $sql = "UPDATE ticket_details SET
                        is_vip = :is_vip,
                        platform_id = :platform_id,
                        phone = :phone,
                        category_id = :category_id,
                        subcategory_id = :subcategory_id,
                        code_id = :code_id,
                        notes = :notes,
                        country_id = :country_id,
                        assigned_team_leader_id = :assigned_team_leader_id
                    WHERE id = :detail_id";
    
            $stmt = $this->db->prepare($sql);
    
            $params = [
                ':is_vip' => $data['is_vip'] ?? 0,
                ':platform_id' => $data['platform_id'],
                ':phone' => $data['phone'] ?? null,
                ':category_id' => $data['category_id'],
                ':subcategory_id' => $data['subcategory_id'],
                ':code_id' => $data['code_id'],
                ':notes' => $data['notes'] ?? null,
                ':country_id' => $data['country_id'] ?? null,
                ':assigned_team_leader_id' => $data['assigned_team_leader_id'] ?? null,
                ':detail_id' => $detailId
            ];
    
            $success = $stmt->execute($params);

            if ($success) {
                return true; // Return true on successful update
            } else {
                return false; // Return false on failed update
            }

        } catch (\Exception $e) {
            error_log("Exception in updateTicketDetail: " . $e->getMessage());
            return false;
        }
    }
    
    
    




 

    /**
     * Log ticket edit changes
     */
    public function logEdit($ticketDetailId, $editedBy, $fieldName, $oldValue, $newValue)
    {
        try {
            $sql = "INSERT INTO ticket_edit_logs (ticket_detail_id, edited_by, field_name, old_value, new_value, created_at)
                    VALUES (:ticket_detail_id, :edited_by, :field_name, :old_value, :new_value, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':ticket_detail_id' => $ticketDetailId,
                ':edited_by' => $editedBy,
                ':field_name' => $fieldName,
                ':old_value' => $oldValue,
                ':new_value' => $newValue
            ]);
        } catch (\Exception $e) {
            error_log("Error in logEdit: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get edit logs for a ticket detail (admin only)
     */
    public function getEditLogs($ticketDetailId)
    {
        $sql = "SELECT 
                    tel.*,
                    u.name as editor_name,
                    u.username as editor_username
                FROM ticket_edit_logs tel
                LEFT JOIN users u ON tel.edited_by = u.id
                WHERE tel.ticket_detail_id = :ticket_detail_id
                ORDER BY tel.created_at DESC";
        
        $this->db->query($sql);
        $this->db->bind(':ticket_detail_id', $ticketDetailId);
        
        return $this->db->fetchAll();
    }

    /**
     * Get all edit logs for a ticket (all its details)
     */
    public function getAllEditLogsForTicket($ticketId)
    {
        $sql = "SELECT 
                    tel.*,
                    u.name as editor_name,
                    u.username as editor_username,
                    td.created_at as detail_created_at
                FROM ticket_edit_logs tel
                LEFT JOIN users u ON tel.edited_by = u.id
                LEFT JOIN ticket_details td ON tel.ticket_detail_id = td.id
                WHERE td.ticket_id = :ticket_id
                ORDER BY tel.created_at DESC";
        
        $this->db->query($sql);
        $this->db->bind(':ticket_id', $ticketId);
        
        return $this->db->resultSet();
    }



} 