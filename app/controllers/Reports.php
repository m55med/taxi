<?php
class Reports extends Controller {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function drivers() {
        try {
            // Calculate main statistics
            $query = "SELECT 
                COUNT(*) as total_drivers,
                SUM(CASE WHEN app_status = 'active' THEN 1 ELSE 0 END) as active_drivers,
                SUM(CASE WHEN main_system_status = 'pending' THEN 1 ELSE 0 END) as pending_drivers,
                SUM(CASE WHEN app_status = 'banned' THEN 1 ELSE 0 END) as banned_drivers,
                SUM(CASE WHEN has_missing_documents = 0 THEN 1 ELSE 0 END) as complete_docs,
                SUM(CASE WHEN has_missing_documents = 1 THEN 1 ELSE 0 END) as missing_docs,
                
                -- Registration sources based on actual enum values
                SUM(CASE WHEN data_source = 'form' THEN 1 ELSE 0 END) as source_form,
                SUM(CASE WHEN data_source = 'referral' THEN 1 ELSE 0 END) as source_referral,
                SUM(CASE WHEN data_source = 'telegram' THEN 1 ELSE 0 END) as source_telegram,
                SUM(CASE WHEN data_source = 'staff' THEN 1 ELSE 0 END) as source_staff,
                SUM(CASE WHEN data_source = 'excel' THEN 1 ELSE 0 END) as source_excel,
                
                -- Processing status
                SUM(CASE WHEN main_system_status = 'waiting_chat' THEN 1 ELSE 0 END) as waiting_chat,
                SUM(CASE WHEN main_system_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                SUM(CASE WHEN main_system_status = 'rescheduled' THEN 1 ELSE 0 END) as rescheduled,
                SUM(CASE WHEN main_system_status = 'reconsider' THEN 1 ELSE 0 END) as reconsider
            FROM drivers";

            // Debug: Print the statistics query
            echo "<!-- Debug: Statistics Query\n" . $query . "\n-->";

            if (!$this->db->query($query)) {
                throw new Exception("Failed to execute statistics query: " . print_r($this->db->getError(), true));
            }

            $stats = $this->db->single();
            if ($stats === false) {
                throw new Exception("Failed to fetch statistics: " . print_r($this->db->getError(), true));
            }

            // Debug: Print raw statistics
            echo "<!-- Debug: Raw Statistics\n";
            var_export($stats);
            echo "\n-->";
            
            // Calculate completion rate
            $total = (int)($stats->total_drivers ?? 0);
            $complete = (int)($stats->complete_docs ?? 0);
            $stats->docs_completion_rate = $total > 0 ? ($complete / $total) * 100 : 0;

            // Get filtered drivers list
            $conditions = [];
            $params = [];

            if (!empty($_GET['main_system_status'])) {
                $conditions[] = "d.main_system_status = :status";
                $params[':status'] = $_GET['main_system_status'];
            }

            if (!empty($_GET['data_source'])) {
                $conditions[] = "d.data_source = :source";
                $params[':source'] = $_GET['data_source'];
            }

            if (isset($_GET['has_missing_documents'])) {
                $conditions[] = "d.has_missing_documents = :missing_docs";
                $params[':missing_docs'] = $_GET['has_missing_documents'];
            }

            if (!empty($_GET['date_from'])) {
                $conditions[] = "DATE(d.created_at) >= :date_from";
                $params[':date_from'] = $_GET['date_from'];
            }

            if (!empty($_GET['date_to'])) {
                $conditions[] = "DATE(d.created_at) <= :date_to";
                $params[':date_to'] = $_GET['date_to'];
            }

            $sql = "SELECT d.*, u.username as added_by_name 
                    FROM drivers d 
                    LEFT JOIN users u ON d.added_by = u.id";

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY d.created_at DESC";

            // Debug: Print the SQL query and parameters
            echo "<!-- Debug: Drivers Query\n" . $sql . "\n";
            echo "Parameters: " . print_r($params, true) . "\n-->";

            if (!$this->db->query($sql)) {
                throw new Exception("Failed to execute drivers query: " . print_r($this->db->getError(), true));
            }

            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    if (!$this->db->bind($key, $value)) {
                        throw new Exception("Failed to bind parameter $key: " . print_r($this->db->getError(), true));
                    }
                }
            }

            $drivers = $this->db->resultSet();
            if ($drivers === false) {
                throw new Exception("Failed to fetch drivers: " . print_r($this->db->getError(), true));
            }

            // Debug: Print number of drivers found
            echo "<!-- Debug: Found " . count($drivers) . " drivers -->";

            // Debug: Print first driver data if exists
            if (!empty($drivers)) {
                echo "<!-- Debug: First Driver Data\n";
                var_export($drivers[0]);
                echo "\n-->";
            }

            $data = [
                // Statistics
                'total_drivers' => (int)($stats->total_drivers ?? 0),
                'active_drivers' => (int)($stats->active_drivers ?? 0),
                'pending_drivers' => (int)($stats->pending_drivers ?? 0),
                'banned_drivers' => (int)($stats->banned_drivers ?? 0),
                'complete_docs' => (int)($stats->complete_docs ?? 0),
                'missing_docs' => (int)($stats->missing_docs ?? 0),
                'docs_completion_rate' => (float)($stats->docs_completion_rate ?? 0),
                'source_form' => (int)($stats->source_form ?? 0),
                'source_referral' => (int)($stats->source_referral ?? 0),
                'source_telegram' => (int)($stats->source_telegram ?? 0),
                'source_staff' => (int)($stats->source_staff ?? 0),
                'source_excel' => (int)($stats->source_excel ?? 0),
                'waiting_chat' => (int)($stats->waiting_chat ?? 0),
                'no_answer' => (int)($stats->no_answer ?? 0),
                'rescheduled' => (int)($stats->rescheduled ?? 0),
                'reconsider' => (int)($stats->reconsider ?? 0),
                
                // Drivers list
                'drivers' => $drivers
            ];

            // Debug: Print final data array
            echo "<!-- Debug: Final Data Array\n";
            var_export($data);
            echo "\n-->";

            $this->view('reports/drivers', $data);
        } catch (Exception $e) {
            // Debug: Print any errors
            echo "<!-- Debug: Error\n";
            echo $e->getMessage() . "\n";
            echo "Stack Trace:\n";
            echo $e->getTraceAsString();
            echo "\n-->";
            
            // Return empty data on error
            $data = [
                'total_drivers' => 0,
                'active_drivers' => 0,
                'pending_drivers' => 0,
                'banned_drivers' => 0,
                'complete_docs' => 0,
                'missing_docs' => 0,
                'docs_completion_rate' => 0,
                'source_form' => 0,
                'source_referral' => 0,
                'source_telegram' => 0,
                'source_staff' => 0,
                'source_excel' => 0,
                'waiting_chat' => 0,
                'no_answer' => 0,
                'rescheduled' => 0,
                'reconsider' => 0,
                'drivers' => []
            ];
            
            $this->view('reports/drivers', $data);
        }
    }
} 