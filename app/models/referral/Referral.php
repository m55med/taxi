<?php

namespace App\Models\Referral;

use App\Core\Database;
use PDO;

class Referral
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCountries()
    {
        $stmt = $this->db->query("SELECT id, name FROM countries ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCarTypes()
    {
        $stmt = $this->db->query("SELECT id, name FROM car_types ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findUserById($id)
    {
        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE id = :id AND role_id = (SELECT id FROM roles WHERE name = 'marketer')");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Finds a driver by their phone number.
     *
     * @param string $phone
     * @return mixed The driver record if found, false otherwise.
     */
    public function findDriverByPhone($phone)
    {
        $stmt = $this->db->prepare("SELECT id, name, phone FROM drivers WHERE phone = :phone");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new driver record.
     *
     * @param array $data The driver data.
     * @return string|false The ID of the new driver, or false on failure.
     */
    public function createDriver($data)
    {
        $sql = "INSERT INTO drivers (name, phone, country_id, car_type_id, data_source, added_by, registered_at)
                VALUES (:name, :phone, :country_id, :car_type_id, :data_source, :added_by, NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':phone' => $data['phone'],
                ':country_id' => $data['country_id'],
                ':car_type_id' => $data['car_type_id'],
                ':data_source' => 'referral',
                ':added_by' => $data['added_by']
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            // Optional: log the error ($e->getMessage())
            return false;
        }
    }

    /**
     * Logs a visit to the referral page.
     * We will add the IPinfo logic here later.
     *
     * @param int|null $affiliate_id
     * @param string $status
     * @return int The ID of the inserted visit record.
     */
    public function logVisit($affiliate_id, $status = 'visit_only')
    {
        $ip_address = $this->getRealIpAddr();

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $referer_url = $_SERVER['HTTP_REFERER'] ?? null;
        
        $geo_data = $this->getIpInfo($ip_address);

        try {
            $sql = "INSERT INTO referral_visits 
                        (affiliate_user_id, ip_address, user_agent, referer_url, registration_status, 
                         country, region, city, isp, device_type, browser_name, operating_system)
                    VALUES 
                        (:affiliate_user_id, :ip_address, :user_agent, :referer_url, :status, 
                         :country, :region, :city, :isp, :device_type, :browser_name, :os)
                    ON DUPLICATE KEY UPDATE 
                        visit_recorded_at = NOW(), 
                        user_agent = VALUES(user_agent),
                        -- Do not downgrade a more advanced status (like 'successful') back to 'form_opened'
                        registration_status = IF(registration_status IN ('attempted', 'successful', 'duplicate_phone', 'failed_other'), registration_status, VALUES(registration_status)),
                        id = LAST_INSERT_ID(id)"; // This ensures lastInsertId() returns the correct ID even on update
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':affiliate_user_id' => $affiliate_id,
                ':ip_address' => $ip_address,
                ':user_agent' => $user_agent,
                ':referer_url' => $referer_url,
                ':status' => $status,
                ':country' => $geo_data['country'] ?? null,
                ':region' => $geo_data['region'] ?? null,
                ':city' => $geo_data['city'] ?? null,
                ':isp' => $geo_data['isp'] ?? null,
                ':device_type' => $geo_data['device_type'] ?? 'Unknown',
                ':browser_name' => $geo_data['browser_name'] ?? 'Unknown',
                ':os' => $geo_data['operating_system'] ?? 'Unknown'
            ]);
            
            return $this->db->lastInsertId();

        } catch (\Exception $e) {
             error_log('Referral Visit Log Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetches geolocation data from IPinfo.
     * @param string $ip
     * @return array
     */
    private function getIpInfo(string $ip): array
    {
        $api_key = $_ENV['IP_INFO'] ?? null;
        if (!$api_key || $ip === 'UNKNOWN') {
            return [];
        }

        $url = "https://ipinfo.io/{$ip}?token={$api_key}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return []; // Return empty on cURL error
        }
        
        curl_close($ch);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return []; // Return empty on JSON decode error
        }
        
        // Add user agent parsing here if needed
        // For simplicity, we'll return basic geo data
        return [
            'country' => $data['country'] ?? null,
            'region' => $data['region'] ?? null,
            'city' => $data['city'] ?? null,
            'isp' => $data['org'] ?? null,
            // Basic User Agent parsing - a library would be better for production
            'device_type' => $this->getDeviceType($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'browser_name' => $this->getBrowser($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'operating_system' => $this->getOS($_SERVER['HTTP_USER_AGENT'] ?? '')
        ];
    }

    /**
     * Fetches raw IPinfo data for debugging purposes.
     * @param string $ip
     * @return array
     */
    public function getIpInfoForDebug(string $ip): array
    {
        // We call getRealIpAddr() here to ensure the debug output shows the same IP as the logging logic.
        $real_ip = $this->getRealIpAddr();

        $api_key = $_ENV['IP_INFO'] ?? null;
        $raw_response = 'IPinfo API Key not set or IP is UNKNOWN.';

        if ($api_key && $real_ip !== 'UNKNOWN' && !in_array($real_ip, ['127.0.0.1', '::1'])) {
            $url = "https://ipinfo.io/{$real_ip}?token={$api_key}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $raw_response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $raw_response = 'cURL Error: ' . curl_error($ch);
            }
            curl_close($ch);
        } elseif (in_array($real_ip, ['127.0.0.1', '::1'])) {
            $raw_response = "This is a local IP address. IPinfo requires a public IP. This is expected on a local server.";
        }

        return [
            'detected_ip' => $real_ip,
            'ipinfo_response' => $raw_response
        ];
    }

    /**
     * Gets the real IP address of the user, checking for proxies.
     * @return string
     */
    private function getRealIpAddr(): string
    {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // Check for IPs passing through proxies (e.g., Cloudflare, AWS ELB)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, the first one is the client
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        
        // Check for other common proxy headers
        if (!empty($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        // Default to REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    // --- Basic User Agent Parser Functions ---
    // Note: For robust parsing, a library like `jenssegers/agent` is recommended.
    
    private function getDeviceType($user_agent) {
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent)) return 'Tablet';
        if (preg_match('/(mobi|ipod|phone|blackberry|opera mini|fennec|minimo|symbian|psp|nintendo ds)/i', $user_agent)) return 'Mobile';
        return 'Desktop';
    }

    private function getOS($user_agent) {
        if (preg_match('/windows nt 10/i', $user_agent)) return 'Windows 10';
        if (preg_match('/windows nt 6.3/i', $user_agent)) return 'Windows 8.1';
        if (preg_match('/windows nt 6.2/i', $user_agent)) return 'Windows 8';
        if (preg_match('/windows nt 6.1/i', $user_agent)) return 'Windows 7';
        if (preg_match('/android/i', $user_agent)) return 'Android';
        if (preg_match('/iphone/i', $user_agent)) return 'iOS';
        if (preg_match('/macintosh|mac os x/i', $user_agent)) return 'Mac OS';
        if (preg_match('/linux/i', $user_agent)) return 'Linux';
        return 'Unknown';
    }

    private function getBrowser($user_agent) {
        if (preg_match('/(msie|trident)/i', $user_agent) && !preg_match('/opera/i', $user_agent)) return 'Internet Explorer';
        if (preg_match('/firefox/i', $user_agent)) return 'Firefox';
        if (preg_match('/chrome/i', $user_agent) && !preg_match('/edge/i', $user_agent)) return 'Chrome';
        if (preg_match('/safari/i', $user_agent) && !preg_match('/chrome/i', $user_agent)) return 'Safari';
        if (preg_match('/edge/i', $user_agent)) return 'Edge';
        if (preg_match('/opera/i', $user_agent) || preg_match('/opr/i', $user_agent)) return 'Opera';
        return 'Unknown';
    }

    /**
     * Updates the status of a visit record.
     *
     * @param int $visit_id
     * @param string $status
     * @return bool
     */
    public function updateVisitStatus($visit_id, $status)
    {
        if (!$visit_id) return false;

        $sql = "UPDATE referral_visits 
                SET registration_status = :status, registration_attempted_at = IF(:status = 'attempted', NOW(), registration_attempted_at)
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['status' => $status, 'id' => $visit_id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Updates a visit record upon successful registration.
     *
     * @param int $visit_id
     * @param int $driver_id
     * @return bool
     */
    public function updateVisitOnSuccess($visit_id, $driver_id)
    {
        if (!$visit_id) return false;

        $sql = "UPDATE referral_visits SET registration_status = 'successful', registered_driver_id = :driver_id WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['driver_id' => $driver_id, 'id' => $visit_id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fetches all referral visits for the admin dashboard.
     * @return array
     */
    public function getAllVisits($filters = [])
    {
        $sql = "SELECT 
                    rv.*, 
                    u.username as affiliate_name,
                    d.name as driver_name
                FROM referral_visits rv
                LEFT JOIN users u ON rv.affiliate_user_id = u.id
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id";
        
        $where = [];
        $params = [];

        if (!empty($filters['marketer_id'])) {
            $where[] = "rv.affiliate_user_id = :marketer_id";
            $params[':marketer_id'] = $filters['marketer_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "rv.visit_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "rv.visit_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY rv.visit_recorded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches referral visits for a specific marketer.
     * @param int $user_id
     * @param array $filters
     * @return array
     */
    public function getVisitsForMarketer($user_id, $filters = [])
    {
        $sql = "SELECT 
                    rv.*,
                    d.name as driver_name
                FROM referral_visits rv
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id
                WHERE rv.affiliate_user_id = :user_id";

        $params = [':user_id' => $user_id];
        $where = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "rv.visit_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "rv.visit_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY rv.visit_recorded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSummaryStats($filters = [])
    {
        // Base query for total visits
        $sql_visits = "SELECT COUNT(id) FROM referral_visits";
        // Base query for successful registrations
        $sql_regs = "SELECT COUNT(id) FROM referral_visits WHERE registration_status = 'successful'";

        $where = [];
        $params = [];

        // Apply filters to both queries
        if (!empty($filters['user_id'])) { // For marketer-specific stats
             $where[] = "affiliate_user_id = :user_id";
             $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['marketer_id'])) { // For admin filtering by marketer
             $where[] = "affiliate_user_id = :marketer_id";
             $params[':marketer_id'] = $filters['marketer_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "visit_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "visit_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($where)) {
            $conditions = " WHERE " . implode(' AND ', $where);
            $sql_visits .= $conditions;
            $sql_regs .= " AND " . implode(' AND ', $where);
        }

        $stmt_visits = $this->db->prepare($sql_visits);
        $stmt_visits->execute($params);
        $total_visits = $stmt_visits->fetchColumn();

        $stmt_regs = $this->db->prepare($sql_regs);
        $stmt_regs->execute($params);
        $total_registrations = $stmt_regs->fetchColumn();

        return [
            'total_visits' => $total_visits,
            'total_registrations' => $total_registrations,
            'conversion_rate' => $total_visits > 0 ? round(($total_registrations / $total_visits) * 100, 2) : 0
        ];
    }
    
    public function getMarketers()
    {
        $sql = "SELECT id, username FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer') ORDER BY username";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 