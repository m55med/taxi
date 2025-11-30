<?php
namespace App\Models\Tickets;
use App\Core\Model;
use PDO;
use PDOException;
// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Ticket extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates a new ticket and its initial detail record.
     */
    public function createTicket(array $data, $userId)
    {
        $this->db->beginTransaction();
        try {
            // Use Cairo-based exception: if Cairo time between 00:00-06:00, store -1 day; else store now (saved in UTC)
            $utcTimestamp = self::getCurrentUTCWithCustomerException();

            $ticketSql = "INSERT INTO tickets (ticket_number, created_by, created_at) VALUES (:ticket_number, :created_by, :created_at)";
            $stmt = $this->db->prepare($ticketSql);
            $stmt->execute([
                ':ticket_number' => $data['ticket_number'],
                ':created_by' => $userId,
                ':created_at' => $utcTimestamp
            ]);
            $ticketId = $this->db->lastInsertId();

            $ticketDetailId = $this->addTicketDetail($ticketId, $data, $userId);
            if ($ticketDetailId) {
                $this->db->commit();
                return ['ticket_id' => $ticketId, 'ticket_detail_id' => $ticketDetailId];
            }
            $this->db->rollBack();
            return false;
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
            $utcTimestamp = self::getCurrentUTCWithCustomerException(); // ← هنا التعديل الوحيد
            $stmt = $this->db->prepare(
                "INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, created_by, edited_by, created_at, updated_at)
                 VALUES (:ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :assigned_team_leader_id, :created_by, :edited_by, :created_at, :updated_at)"
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
                ':created_by' => $userId,
                ':edited_by' => $userId,
                ':created_at' => $utcTimestamp,
                ':updated_at' => $utcTimestamp
            ]);
            $ticketDetailId = $this->db->lastInsertId();
            $this->db->commit();
            return $ticketDetailId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error in addTicketDetail: " . $e->getMessage());
            return false;
        }
    }

    private static function getCurrentUTCWithCustomerException(): string
    {
        $utcNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $cairoTime = $utcNow->setTimezone(new \DateTimeZone('Africa/Cairo'));
        $hour = (int) $cairoTime->format('H');

        if ($hour >= 0 && $hour < 5) {
            $cairoTime = $cairoTime->modify('-1 day');
        }

        $finalUtc = $cairoTime->setTimezone(new \DateTimeZone('UTC'));
        return $finalUtc->format('Y-m-d H:i:s');
    }



    public function findTicketByNumber(string $ticketNumber)
    {
        $sql = "SELECT 
                    t.id, t.ticket_number, t.created_by, t.created_at,
                    td.*, 
                    p.name as platform_name, 
                    c.name as category_name, 
                    sc.name as subcategory_name, 
                    co.name as code_name, 
                    u_creator.username as creator_name, 
                    u_editor.username as editor_name,
                    u_leader.username as leader_name
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id AND td.id = (
                    SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id
                )
                LEFT JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN ticket_categories c ON td.category_id = c.id
                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id
                LEFT JOIN ticket_codes co ON td.code_id = co.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_editor ON td.edited_by = u_editor.id
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
        $ticket = $this->findTicketByNumber($searchTerm);
        if ($ticket) return $ticket;
        $sql = "SELECT t.id
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id
                WHERE td.phone = :phone
                ORDER BY td.created_at DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':phone' => $searchTerm]);
        $ticketId = $stmt->fetchColumn();
        if ($ticketId) return $this->findById($ticketId);
        return null;
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

    public function syncCoupons($ticketId, $ticketDetailId, array $couponIds)
    {
        if (empty($couponIds) || empty($ticketDetailId)) return true;
        $this->db->beginTransaction();
        try {
            $utcTimestamp = DateTimeHelper::getCurrentUTCWithCustomerException();
            $updateSql = "UPDATE coupons SET is_used = 1, used_in_ticket = :ticket_id, used_by = :user_id, used_at = :used_at WHERE id = :coupon_id";
            $updateStmt = $this->db->prepare($updateSql);
            $pivotSql = "INSERT INTO ticket_coupons (ticket_id, ticket_detail_id, coupon_id) VALUES (:ticket_id, :ticket_detail_id, :coupon_id)
                         ON DUPLICATE KEY UPDATE ticket_detail_id = VALUES(ticket_detail_id)";
            $pivotStmt = $this->db->prepare($pivotSql);
            foreach ($couponIds as $couponId) {
                if (empty($couponId)) continue;
                $updateStmt->execute([
                    ':ticket_id' => $ticketId,
                    ':user_id' => $_SESSION['user_id'],
                    ':used_at' => $utcTimestamp,
                    ':coupon_id' => $couponId
                ]);
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
        $stmt->execute(['ticket_id' => $ticketId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $history;
    }

    public function getTicketCoupons($ticketId)
    {
        $sql = "SELECT c.id, c.code, c.value, c.is_used, c.used_at, tc.ticket_detail_id
                FROM ticket_coupons tc
                JOIN coupons c ON tc.coupon_id = c.id
                WHERE tc.ticket_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);
    }

    public function getCouponsForTicketDetail($detailId)
    {
        $sql = "SELECT c.code, c.value 
                FROM coupons c
                JOIN ticket_coupons tc ON c.id = tc.coupon_id
                WHERE tc.ticket_detail_id = :detail_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':detail_id' => $detailId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);
    }

    public function getRelatedTickets($phone, $currentTicketId)
    {
        if (empty($phone) || $currentTicketId === null) return [];
        try {
            $stmt = $this->db->prepare("SELECT id, ticket_number, created_at FROM tickets WHERE phone = :phone AND id != :current_ticket_id ORDER BY created_at DESC");
            $stmt->execute([':phone' => $phone, ':current_ticket_id' => $currentTicketId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);
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
        $stmt->execute([':searchTerm1' => $searchTerm, ':searchTerm2' => $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($ticket) {
            $label = $ticket['ticket_number'];
            if (!empty($ticket['phone'])) {
                $label .= ' - ' . htmlspecialchars($ticket['phone']);
            }
            return ['id' => $ticket['id'], 'label' => $label];
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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return \convert_dates_for_display_12h($result, ['created_at', 'updated_at']);
        }
        return $result;
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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return \convert_dates_for_display_12h($result, ['created_at', 'updated_at']);
        }
        return $result;
    }

    public function updateTicketNumber($ticketId, $ticketNumber)
    {
        try {
            // Check if ticket_number already exists for another ticket
            $checkSql = "SELECT id FROM tickets WHERE ticket_number = :ticket_number AND id != :ticket_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([
                ':ticket_number' => $ticketNumber,
                ':ticket_id' => $ticketId
            ]);
            if ($checkStmt->fetch()) {
                // Ticket number already exists
                return false;
            }
            
            // Update ticket number
            $sql = "UPDATE tickets SET ticket_number = :ticket_number WHERE id = :ticket_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':ticket_number' => $ticketNumber,
                ':ticket_id' => $ticketId
            ]);
        } catch (\Exception $e) {
            error_log("Exception in updateTicketNumber: " . $e->getMessage());
            return false;
        }
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
                        assigned_team_leader_id = :assigned_team_leader_id,
                        edited_by = :edited_by,
                        updated_at = NOW()
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
                ':edited_by' => $userId,
                ':detail_id' => $detailId
            ];
            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log("Exception in updateTicketDetail: " . $e->getMessage());
            return false;
        }
    }

    public function logEdit($ticketDetailId, $editedBy, $fieldName, $oldValue, $newValue)
    {
        try {
            error_log("LOGGING EDIT: Detail ID: $ticketDetailId, User: $editedBy, Field: $fieldName, Old: $oldValue, New: $newValue");
            $utcTimestamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            $sql = "INSERT INTO ticket_edit_logs (ticket_detail_id, edited_by, field_name, old_value, new_value, created_at)
                    VALUES (:ticket_detail_id, :edited_by, :field_name, :old_value, :new_value, :created_at)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':ticket_detail_id' => $ticketDetailId,
                ':edited_by'        => $editedBy,
                ':field_name'       => $fieldName,
                ':old_value'        => $oldValue,
                ':new_value'        => $newValue,
                ':created_at'       => $utcTimestamp
            ]);
        } catch (\Exception $e) {
            error_log("Error in logEdit: " . $e->getMessage());
            return false;
        }
    }

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

    public function getAllEditLogsForTicket($ticketId)
    {
        $sql = "SELECT
                    tel.*,
                    u.name as editor_name,
                    u.username as editor_username,
                    td.created_at as detail_created_at,
                    t.ticket_number
                FROM ticket_edit_logs tel
                LEFT JOIN users u ON tel.edited_by = u.id
                LEFT JOIN ticket_details td ON tel.ticket_detail_id = td.id
                LEFT JOIN tickets t ON td.ticket_id = t.id
                WHERE td.ticket_id = :ticket_id
                ORDER BY tel.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
