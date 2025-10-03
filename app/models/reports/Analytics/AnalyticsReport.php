<?php

namespace App\Models\Reports\Analytics;



use App\Core\Database;

use PDO;




// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class AnalyticsReport

{

    private $db;



    public function __construct()

    {

        $this->db = Database::getInstance();

    }



    private function applyDateFilters($sql, $params, $filters, $dateColumn = 'created_at')

    {

        if (!empty($filters['original_date_from'])) {

            $sql .= " AND DATE(CONVERT_TZ($dateColumn, '+00:00', '+02:00')) >= :date_from";

            $params[':date_from'] = $filters['original_date_from'];

        }

        if (!empty($filters['original_date_to'])) {

            $sql .= " AND DATE(CONVERT_TZ($dateColumn, '+00:00', '+02:00')) <= :date_to";

            $params[':date_to'] = $filters['original_date_to'];

        }

        return ['sql' => $sql, 'params' => $params];

    }



    public function getDriverConversion($filters = [])

    {

        $sql = "SELECT 

                    data_source,

                    COUNT(id) as total_drivers,

                    SUM(CASE WHEN main_system_status = 'completed' THEN 1 ELSE 0 END) as completed_drivers,

                    (SUM(CASE WHEN main_system_status = 'completed' THEN 1 ELSE 0 END) / COUNT(id)) * 100 as conversion_rate

                FROM drivers

                WHERE 1=1";



        $dateFiltered = $this->applyDateFilters($sql, [], $filters);

        $sql = $dateFiltered['sql'] . " GROUP BY data_source";

        

        $stmt = $this->db->prepare($sql);

        $stmt->execute($dateFiltered['params']);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

    }



    public function getCallCenterStats($filters = [])

    {

        // 1. Call Outcomes

        $sql_outcomes = "SELECT call_status, COUNT(id) as count FROM driver_calls WHERE 1=1";

        $dateFilteredOutcomes = $this->applyDateFilters($sql_outcomes, [], $filters);

        $sql_outcomes = $dateFilteredOutcomes['sql'] . " GROUP BY call_status";

        $stmt_outcomes = $this->db->prepare($sql_outcomes);

        $stmt_outcomes->execute($dateFilteredOutcomes['params']);

        $outcomes = $stmt_outcomes->fetchAll(PDO::FETCH_KEY_PAIR);



        // 2. Staff Performance

        $sql_performance = "SELECT

                                u.name as user_name,

                                u.id as user_id,

                                COUNT(dc.id) as total_calls,

                                SUM(CASE WHEN dc.call_status = 'answered' THEN 1 ELSE 0 END) as answered_calls

                            FROM driver_calls dc

                            JOIN users u ON dc.call_by = u.id

                            WHERE 1=1";

        $dateFilteredPerf = $this->applyDateFilters($sql_performance, [], $filters, 'dc.created_at');

        $sql_performance = $dateFilteredPerf['sql'] . " GROUP BY u.id, u.name ORDER BY total_calls DESC";

        $stmt_performance = $this->db->prepare($sql_performance);

        $stmt_performance->execute($dateFilteredPerf['params']);

        $performance = $stmt_performance->fetchAll(PDO::FETCH_ASSOC);



        return [

            'outcomes' => $outcomes,

            'performance' => $performance

        ];

    }



    public function getTicketingStats($filters = [])

    {

        // 1. Tickets by platform

        $sql_platform = "SELECT p.name, COUNT(td.id) as count 

                         FROM ticket_details td

                         JOIN platforms p ON td.platform_id = p.id

                         WHERE 1=1";

        $dateFilteredPlatform = $this->applyDateFilters($sql_platform, [], $filters, 'td.created_at');

        $sql_platform = $dateFilteredPlatform['sql'] . " GROUP BY p.name";

        $stmt_platform = $this->db->prepare($sql_platform);

        $stmt_platform->execute($dateFilteredPlatform['params']);

        $by_platform = $stmt_platform->fetchAll(PDO::FETCH_KEY_PAIR);



        // 2. Tickets by status (assuming status is on tickets table)

        // This is a placeholder as tickets table has no status column in the schema

        // I will assume a logic where a ticket is "closed" if it has a review.

        $sql_status = "SELECT 

                        CASE WHEN r.id IS NOT NULL THEN 'Closed' ELSE 'Open' END as ticket_status,

                        COUNT(DISTINCT t.id) as count

                       FROM tickets t

                       LEFT JOIN reviews r ON r.reviewable_id = t.id AND r.reviewable_type = 'ticket'

                       WHERE 1=1";

        $dateFilteredStatus = $this->applyDateFilters($sql_status, [], $filters, 't.created_at');

        $sql_status = $dateFilteredStatus['sql'] . " GROUP BY ticket_status";

        $stmt_status = $this->db->prepare($sql_status);

        $stmt_status->execute($dateFilteredStatus['params']);

        $by_status = $stmt_status->fetchAll(PDO::FETCH_KEY_PAIR);

        

        return [

            'by_platform' => $by_platform,

            'by_status' => $by_status

        ];

    }

} 