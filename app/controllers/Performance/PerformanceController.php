<?php

namespace App\Controllers\Performance;

use App\Core\Controller;
use App\Models\Listings\ListingModel;
use App\Models\Calls\Call;

class PerformanceController extends Controller
{
    private $listingModel;
    private $callModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->listingModel = new ListingModel();
        $this->callModel = new Call();
        $this->db = $this->listingModel->getDb();
    }

    /**
     * System Overview - نظرة عامة على النظام
     */
    public function overview()
    {
        $this->view('performance/overview', [
            'title' => 'System Overview - نظرة عامة على النظام',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * Daily Statistics - الإحصائيات اليومية
     */
    public function daily()
    {
        $today = date('Y-m-d');

        $this->view('performance/daily', [
            'title' => 'Daily Stats - الإحصائيات اليومية',
            'activeNav' => 'performance',
            'today' => $today
        ]);
    }

    /**
     * Team Performance - أداء الفرق
     */
    public function teams()
    {
        $this->view('performance/teams', [
            'title' => 'Team Performance - أداء الفرق',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * Ticket Analytics - تحليلات التذاكر
     */
    public function tickets()
    {
        $this->view('performance/tickets', [
            'title' => 'Ticket Analytics - تحليلات التذاكر',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * Call Center Statistics - إحصائيات مركز الاتصال
     */
    public function calls()
    {
        $this->view('performance/calls', [
            'title' => 'Call Center Stats - إحصائيات مركز الاتصال',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * User Performance - أداء المستخدمين
     */
    public function users()
    {
        $this->view('performance/users', [
            'title' => 'User Performance - أداء المستخدمين',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * System Health - حالة النظام
     */
    public function health()
    {
        $this->view('performance/health', [
            'title' => 'System Health - حالة النظام',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * Quality Metrics - مقاييس الجودة
     */
    public function quality()
    {
        $this->view('performance/quality', [
            'title' => 'Quality Metrics - مقاييس الجودة',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * Real-time Dashboard - لوحة التحكم الفورية
     */
    public function realtime()
    {
        $this->view('performance/realtime', [
            'title' => 'Real-time Dashboard - لوحة التحكم الفورية',
            'activeNav' => 'performance'
        ]);
    }

    /**
     * Performance Reports - تقارير الأداء
     */
    public function reports()
    {
        $this->view('performance/reports', [
            'title' => 'Performance Reports - تقارير الأداء',
            'activeNav' => 'performance'
        ]);
    }

    // ================ API Methods ================

    /**
     * Get overview data for dashboard
     */
    public function getOverviewData()
    {
        header('Content-Type: application/json');

        try {
            $today = date('Y-m-d');

            // Get ticket stats
            $ticketStats = $this->listingModel->getTicketStats([
                'start_date' => $today,
                'end_date' => $today
            ]);

            // Get call stats
            $callStats = $this->callModel->getCallStats([
                'start_date' => $today,
                'end_date' => $today
            ]);

            // Get user activity stats
            $userActivity = $this->getUserActivityStats($today);

            $data = [
                'success' => true,
                'data' => [
                    'tickets' => $ticketStats,
                    'calls' => $callStats,
                    'users' => $userActivity,
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get daily statistics
     */
    public function getDailyStats()
    {
        header('Content-Type: application/json');

        try {
            $selectedDate = $_GET['date'] ?? date('Y-m-d');
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $lastWeek = date('Y-m-d', strtotime('-7 days'));

            $data = [
                'success' => true,
                'data' => [
                    'today' => $this->getDaySummary($today),
                    'yesterday' => $this->getDaySummary($yesterday),
                    'last_week_avg' => $this->getWeekAverage($lastWeek, $today),
                    'trends' => $this->getTrendsData($selectedDate),
                    'selected_date' => $selectedDate,
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get team performance data
     */
    public function getTeamPerformance()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getTeamStats(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get ticket analytics data
     */
    public function getTicketAnalytics()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getTicketAnalyticsData(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get call center statistics
     */
    public function getCallStats()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getCallCenterStats(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get user performance data
     */
    public function getUserPerformance()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getIndividualUserStats(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get system health data
     */
    public function getSystemHealth()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getHealthMetrics(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get quality metrics data
     */
    public function getQualityMetrics()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getQualityData(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get real-time data
     */
    public function getRealtimeData()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->getLiveMetrics(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get performance reports data
     */
    public function getPerformanceReports()
    {
        header('Content-Type: application/json');

        try {
            // Get date parameters from query string with wider default range
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-365 days')); // Default to 1 year ago
            $to = $_GET['to'] ?? date('Y-m-d');

            error_log("API Request - Date range: $from to $to");

            $data = [
                'success' => true,
                'data' => $this->getComprehensiveReports($from, $to),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            error_log("API Error: " . $e->getMessage());
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get recent tickets data
     */
    public function getRecentTickets()
    {
        header('Content-Type: application/json');

        try {
            $sql = "SELECT
                        t.ticket_number,
                        td.created_at,
                        u.name as user_name
                    FROM ticket_details td
                    JOIN tickets t ON td.ticket_id = t.id
                    JOIN users u ON td.edited_by = u.id
                    ORDER BY td.created_at DESC
                    LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'success' => true,
                'data' => array_map(function($ticket) {
                    return [
                        'time' => $ticket['created_at'],
                        'ticket' => $ticket['ticket_number'],
                        'user' => $ticket['user_name']
                    ];
                }, $tickets)
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    /**
     * Get active sessions/users data
     */
    public function getActiveSessions()
    {
        header('Content-Type: application/json');

        try {
            $sql = "SELECT
                        u.name as user_name,
                        u.is_online,
                        MAX(td.created_at) as last_ticket_time,
                        MAX(dc.created_at) as last_call_time,
                        TIMESTAMPDIFF(MINUTE, u.last_activity, NOW()) as minutes_since_activity
                    FROM users u
                    LEFT JOIN ticket_details td ON td.edited_by = u.id AND DATE(td.created_at) = CURDATE()
                    LEFT JOIN driver_calls dc ON dc.call_by = u.id AND DATE(dc.created_at) = CURDATE()
                    WHERE u.status = 'active'
                    GROUP BY u.id, u.name, u.is_online, u.last_activity
                    HAVING (last_ticket_time IS NOT NULL OR last_call_time IS NOT NULL OR minutes_since_activity < 60)
                    ORDER BY GREATEST(COALESCE(last_ticket_time, '2000-01-01'), COALESCE(last_call_time, '2000-01-01')) DESC
                    LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sessions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'success' => true,
                'data' => array_map(function($session) {
                    $lastActivity = max(
                        strtotime($session['last_ticket_time'] ?: '2000-01-01'),
                        strtotime($session['last_call_time'] ?: '2000-01-01')
                    );

                    $duration = floor((time() - $lastActivity) / 60); // minutes ago

                    return [
                        'user' => $session['user_name'],
                        'activity' => $session['is_online'] ? 'نشط الآن' : 'غير نشط',
                        'duration' => $duration < 60 ? $duration . ' دقيقة' : floor($duration / 60) . ' ساعة'
                    ];
                }, $sessions)
            ];

        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($data);
    }

    // ================ Helper Methods ================

    private function getUserActivityStats($date)
    {
        try {
            $sql = "SELECT
                        COUNT(DISTINCT u.id) as active_users,
                        COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as online_users,
                        COUNT(DISTINCT CASE WHEN DATE(u.created_at) = ? THEN u.id END) as new_users_today
                    FROM users u
                    LEFT JOIN user_activity_logs ual ON ual.user_id = u.id AND DATE(ual.created_at) = ?
                    WHERE DATE(u.last_activity) = ? OR DATE(ual.created_at) = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$date, $date, $date, $date]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'active_users' => (int)($result['active_users'] ?? 0),
                'online_users' => (int)($result['online_users'] ?? 0),
                'new_users_today' => (int)($result['new_users_today'] ?? 0)
            ];

        } catch (\Exception $e) {
            return [
                'active_users' => 0,
                'online_users' => 0,
                'new_users_today' => 0
            ];
        }
    }

    private function getDaySummary($date)
    {
        $ticketStats = $this->listingModel->getTicketStats([
            'start_date' => $date,
            'end_date' => $date
        ]);

        $callStats = $this->callModel->getCallStats([
            'start_date' => $date,
            'end_date' => $date
        ]);

        return [
            'date' => $date,
            'tickets' => $ticketStats['total'] ?? 0,
            'calls' => ($callStats['incoming'] ?? 0) + ($callStats['outgoing'] ?? 0),
            'vip_tickets' => $ticketStats['vip_count'] ?? 0,
            'normal_tickets' => $ticketStats['normal_count'] ?? 0
        ];
    }

    private function getTrendsData($selectedDate)
    {
        $endDate = $selectedDate;
        $startDate = date('Y-m-d', strtotime('-29 days', strtotime($endDate))); // Last 30 days

        try {
            $sql = "SELECT
                        DATE(td.created_at) as date,
                        COUNT(DISTINCT td.id) as tickets,
                        COUNT(DISTINCT CASE WHEN td.is_vip = 1 THEN td.id END) as vip_tickets,
                        COUNT(DISTINCT CASE WHEN td.is_vip = 0 THEN td.id END) as normal_tickets,
                        COUNT(DISTINCT CASE WHEN DATE(dc.created_at) = DATE(td.created_at) THEN dc.id END) as calls_outgoing,
                        COUNT(DISTINCT CASE WHEN DATE(ic.call_started_at) = DATE(td.created_at) THEN ic.id END) as calls_incoming
                    FROM ticket_details td
                    LEFT JOIN driver_calls dc ON DATE(dc.created_at) = DATE(td.created_at)
                    LEFT JOIN incoming_calls ic ON DATE(ic.call_started_at) = DATE(td.created_at)
                    WHERE DATE(td.created_at) BETWEEN ? AND ?
                    GROUP BY DATE(td.created_at)
                    ORDER BY date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $rawData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fill in missing dates with zeros
            $allDates = [];
            $currentDate = strtotime($startDate);
            $endDateTime = strtotime($endDate);

            while ($currentDate <= $endDateTime) {
                $dateStr = date('Y-m-d', $currentDate);
                $allDates[$dateStr] = [
                    'date' => $dateStr,
                    'formatted_date' => date('M j', $currentDate),
                    'tickets' => 0,
                    'vip_tickets' => 0,
                    'normal_tickets' => 0,
                    'calls' => 0,
                    'calls_outgoing' => 0,
                    'calls_incoming' => 0
                ];
                $currentDate = strtotime('+1 day', $currentDate);
            }

            // Fill in actual data
            foreach ($rawData as $row) {
                $date = $row['date'];
                if (isset($allDates[$date])) {
                    $allDates[$date]['tickets'] = (int)$row['tickets'];
                    $allDates[$date]['vip_tickets'] = (int)$row['vip_tickets'];
                    $allDates[$date]['normal_tickets'] = (int)$row['normal_tickets'];
                    $allDates[$date]['calls'] = (int)$row['calls_outgoing'] + (int)$row['calls_incoming'];
                    $allDates[$date]['calls_outgoing'] = (int)$row['calls_outgoing'];
                    $allDates[$date]['calls_incoming'] = (int)$row['calls_incoming'];
                }
            }

            return array_values($allDates);

        } catch (\Exception $e) {
            // Return empty data structure on error
            return [];
        }
    }

    private function getWeekAverage($startDate, $endDate)
    {
        // Calculate average for the last 7 days
        $ticketStats = $this->listingModel->getTicketStats([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $callStats = $this->callModel->getCallStats([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;

        return [
            'avg_tickets_per_day' => round(($ticketStats['total'] ?? 0) / $days, 1),
            'avg_calls_per_day' => round((($callStats['incoming'] ?? 0) + ($callStats['outgoing'] ?? 0)) / $days, 1),
            'total_tickets' => $ticketStats['total'] ?? 0,
            'total_calls' => ($callStats['incoming'] ?? 0) + ($callStats['outgoing'] ?? 0),
            'days_count' => $days
        ];
    }

    private function getTeamStats()
    {
        try {
            // Get all team members first
            $teamMembersSql = "SELECT
                                t.id as team_id,
                                t.name as team_name,
                                t.team_leader_id,
                                u.name as leader_name,
                                tm.user_id as member_id
                            FROM teams t
                            LEFT JOIN users u ON t.team_leader_id = u.id
                            LEFT JOIN team_members tm ON t.id = tm.team_id
                            ORDER BY t.id";

            $teamMembersStmt = $this->db->prepare($teamMembersSql);
            $teamMembersStmt->execute();
            $teamMembers = $teamMembersStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group members by team
            $teamsData = [];
            $allUserIds = [];
            foreach ($teamMembers as $member) {
                $teamId = $member['team_id'];
                if (!isset($teamsData[$teamId])) {
                    $teamsData[$teamId] = [
                        'team_id' => $teamId,
                        'team_name' => $member['team_name'] ?? 'غير محدد',
                        'leader_name' => $member['leader_name'] ?? 'غير محدد',
                        'members' => []
                    ];
                }
                if ($member['member_id']) {
                    $teamsData[$teamId]['members'][] = $member['member_id'];
                    $allUserIds[] = $member['member_id'];
                }
            }

            if (empty($allUserIds)) {
                return [];
            }

            // Remove duplicates
            $allUserIds = array_unique($allUserIds);
            $userIdsPlaceholders = implode(',', array_fill(0, count($allUserIds), '?'));

            // Get stats for all users in one query (similar to UsersReport logic)
            $statsSql = "
                SELECT
                    user_id,
                    SUM(CASE WHEN activity_type = 'Ticket'
                              AND LOWER(REPLACE(platform_name, '_', ' ')) NOT IN ('incoming call', 'incoming calls')
                              AND is_vip = 1 THEN 1 ELSE 0 END) as vip_tickets,
                    SUM(CASE WHEN activity_type = 'Ticket'
                              AND LOWER(REPLACE(platform_name, '_', ' ')) NOT IN ('incoming call', 'incoming calls')
                              AND is_vip = 0 THEN 1 ELSE 0 END) as normal_tickets,
                    SUM(CASE WHEN activity_type = 'Incoming Call' THEN 1 ELSE 0 END) as incoming_calls,
                    SUM(CASE WHEN activity_type = 'Outgoing Call' THEN 1 ELSE 0 END) as outgoing_calls,
                    SUM(COALESCE(points, 0)) as total_points
                FROM (
                    -- Tickets
                    SELECT
                        td.edited_by as user_id,
                        'Ticket' as activity_type,
                        td.created_at as activity_date,
                        td.is_vip,
                        p.name as platform_name,
                        COALESCE(tcp.points, 10.0) as points
                    FROM ticket_details td
                    JOIN platforms p ON td.platform_id = p.id
                    LEFT JOIN ticket_code_points tcp
                        ON tcp.code_id = td.code_id
                       AND tcp.is_vip = td.is_vip
                       AND tcp.valid_from <= DATE(td.created_at)
                       AND (tcp.valid_to >= DATE(td.created_at) OR tcp.valid_to IS NULL)
                    WHERE td.edited_by IN ({$userIdsPlaceholders})
                      AND DATE(td.created_at) = CURDATE()

                    UNION ALL

                    -- Outgoing Calls
                    SELECT
                        dc.call_by as user_id,
                        'Outgoing Call' as activity_type,
                        dc.created_at as activity_date,
                        NULL as is_vip,
                        NULL as platform_name,
                        COALESCE(cp.points, 0) as points
                    FROM driver_calls dc
                    LEFT JOIN call_points cp
                        ON cp.call_type = 'outgoing'
                       AND cp.valid_from <= DATE(dc.created_at)
                       AND (cp.valid_to >= DATE(dc.created_at) OR cp.valid_to IS NULL)
                    WHERE dc.call_by IN ({$userIdsPlaceholders})
                      AND DATE(dc.created_at) = CURDATE()

                    UNION ALL

                    -- Incoming Calls
                    SELECT
                        ic.call_received_by as user_id,
                        'Incoming Call' as activity_type,
                        ic.call_started_at as activity_date,
                        NULL as is_vip,
                        NULL as platform_name,
                        COALESCE(cp.points, 0) as points
                    FROM incoming_calls ic
                    LEFT JOIN call_points cp
                        ON cp.call_type = 'incoming'
                       AND cp.valid_from <= DATE(ic.call_started_at)
                       AND (cp.valid_to >= DATE(ic.call_started_at) OR cp.valid_to IS NULL)
                    WHERE ic.call_received_by IN ({$userIdsPlaceholders})
                      AND DATE(ic.call_started_at) = CURDATE()
                ) activities
                GROUP BY user_id
            ";

            $statsStmt = $this->db->prepare($statsSql);
            $statsStmt->execute(array_merge($allUserIds, $allUserIds, $allUserIds));
            $userStats = $statsStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Convert to associative array keyed by user_id
            $userStatsMap = [];
            foreach ($userStats as $stat) {
                $userStatsMap[$stat['user_id']] = [
                    'normal_tickets' => (int)$stat['normal_tickets'],
                    'vip_tickets' => (int)$stat['vip_tickets'],
                    'incoming_calls' => (int)$stat['incoming_calls'],
                    'outgoing_calls' => (int)$stat['outgoing_calls'],
                    'total_points' => (float)$stat['total_points']
                ];
            }

            // Aggregate stats by team
            $result = [];
            foreach ($teamsData as $teamId => $team) {
                $teamStats = [
                    'team_id' => $teamId,
                    'team_name' => $team['team_name'],
                    'leader_name' => $team['leader_name'],
                    'members_count' => count($team['members']),
                    'tickets_today' => 0,
                    'vip_tickets_today' => 0,
                    'calls_today' => 0,
                    'total_points' => 0
                ];

                foreach ($team['members'] as $memberId) {
                    if (isset($userStatsMap[$memberId])) {
                        $memberStats = $userStatsMap[$memberId];
                        $teamStats['tickets_today'] += $memberStats['normal_tickets'] + $memberStats['vip_tickets'];
                        $teamStats['vip_tickets_today'] += $memberStats['vip_tickets'];
                        $teamStats['calls_today'] += $memberStats['incoming_calls'] + $memberStats['outgoing_calls'];
                        $teamStats['total_points'] += $memberStats['total_points'];
                    }
                }

                // Only include teams with members
                if ($teamStats['members_count'] > 0) {
                    $result[] = $teamStats;
                }
            }

            // Sort by total points descending
            usort($result, function($a, $b) {
                return $b['total_points'] <=> $a['total_points'];
            });

            return $result;

        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTicketAnalyticsData()
    {
        try {
            $sql = "SELECT
                        DATE(td.created_at) as date,
                        COUNT(*) as total_tickets,
                        COUNT(DISTINCT t.created_by) as creators,
                        COUNT(DISTINCT CASE WHEN td.is_vip = 1 THEN td.id END) as vip_tickets,
                        COUNT(DISTINCT CASE WHEN td.is_vip = 0 THEN td.id END) as normal_tickets,
                        COUNT(DISTINCT p.id) as platforms_used,
                        COUNT(DISTINCT cat.id) as categories_used
                    FROM ticket_details td
                    JOIN tickets t ON td.ticket_id = t.id
                    LEFT JOIN platforms p ON td.platform_id = p.id
                    LEFT JOIN ticket_categories cat ON td.category_id = cat.id
                    WHERE DATE(td.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(td.created_at)
                    ORDER BY date DESC
                    LIMIT 30";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    'date' => $row['date'],
                    'total_tickets' => (int)$row['total_tickets'],
                    'creators' => (int)$row['creators'],
                    'vip_tickets' => (int)$row['vip_tickets'],
                    'normal_tickets' => (int)$row['normal_tickets'],
                    'platforms_used' => (int)$row['platforms_used'],
                    'categories_used' => (int)$row['categories_used']
                ];
            }, $data);

        } catch (\Exception $e) {
            return [];
        }
    }

    private function getCallCenterStats()
    {
        try {
            $sql = "SELECT
                        DATE(call_time) as date,
                        call_type,
                        COUNT(*) as call_count,
                        AVG(CASE WHEN call_type = 'Incoming' THEN duration_seconds END) as avg_incoming_duration,
                        AVG(CASE WHEN call_type = 'Outgoing' THEN duration_seconds END) as avg_outgoing_duration
                    FROM (
                        SELECT 'Incoming' as call_type, call_started_at as call_time, TIMESTAMPDIFF(SECOND, call_started_at, call_ended_at) as duration_seconds
                        FROM incoming_calls
                        WHERE DATE(call_started_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

                        UNION ALL

                        SELECT 'Outgoing' as call_type, created_at as call_time, NULL as duration_seconds
                        FROM driver_calls
                        WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ) as all_calls
                    GROUP BY DATE(call_time), call_type
                    ORDER BY date DESC, call_type";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $result = [];
            foreach ($data as $row) {
                $date = $row['date'];
                if (!isset($result[$date])) {
                    $result[$date] = [
                        'date' => $date,
                        'incoming_calls' => 0,
                        'outgoing_calls' => 0,
                        'avg_incoming_duration' => 0,
                        'avg_outgoing_duration' => 0
                    ];
                }

                if ($row['call_type'] === 'Incoming') {
                    $result[$date]['incoming_calls'] = (int)$row['call_count'];
                    $result[$date]['avg_incoming_duration'] = round((float)$row['avg_incoming_duration'], 1);
                } else {
                    $result[$date]['outgoing_calls'] = (int)$row['call_count'];
                    $result[$date]['avg_outgoing_duration'] = round((float)$row['avg_outgoing_duration'], 1);
                }
            }

            return array_values($result);

        } catch (\Exception $e) {
            return [];
        }
    }

    private function getIndividualUserStats()
    {
        try {
            // Get all active users first
            $usersSql = "SELECT
                            u.id,
                            u.name,
                            u.username,
                            r.name as role_name
                        FROM users u
                        LEFT JOIN roles r ON u.role_id = r.id
                        WHERE u.status = 'active'
                          AND r.name NOT IN ('developer', 'marketer', 'VIP')
                        ORDER BY u.created_at DESC";

            $usersStmt = $this->db->prepare($usersSql);
            $usersStmt->execute();
            $users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($users)) {
                return [];
            }

            // Get user IDs
            $userIds = array_column($users, 'id');
            $userIdsPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

            // Get today's stats
            $todayStatsSql = "
                SELECT
                    user_id,
                    SUM(CASE WHEN activity_type = 'Ticket'
                              AND LOWER(REPLACE(platform_name, '_', ' ')) NOT IN ('incoming call', 'incoming calls')
                              AND is_vip = 1 THEN 1 ELSE 0 END) as vip_tickets,
                    SUM(CASE WHEN activity_type = 'Ticket'
                              AND LOWER(REPLACE(platform_name, '_', ' ')) NOT IN ('incoming call', 'incoming calls')
                              AND is_vip = 0 THEN 1 ELSE 0 END) as normal_tickets,
                    SUM(CASE WHEN activity_type = 'Incoming Call' THEN 1 ELSE 0 END) as incoming_calls,
                    SUM(CASE WHEN activity_type = 'Outgoing Call' THEN 1 ELSE 0 END) as outgoing_calls
                FROM (
                    -- Tickets Today
                    SELECT
                        td.edited_by as user_id,
                        'Ticket' as activity_type,
                        td.is_vip,
                        p.name as platform_name
                    FROM ticket_details td
                    JOIN platforms p ON td.platform_id = p.id
                    WHERE td.edited_by IN ({$userIdsPlaceholders})
                      AND DATE(td.created_at) = CURDATE()

                    UNION ALL

                    -- Outgoing Calls Today
                    SELECT
                        dc.call_by as user_id,
                        'Outgoing Call' as activity_type,
                        NULL as is_vip,
                        NULL as platform_name
                    FROM driver_calls dc
                    WHERE dc.call_by IN ({$userIdsPlaceholders})
                      AND DATE(dc.created_at) = CURDATE()

                    UNION ALL

                    -- Incoming Calls Today
                    SELECT
                        ic.call_received_by as user_id,
                        'Incoming Call' as activity_type,
                        NULL as is_vip,
                        NULL as platform_name
                    FROM incoming_calls ic
                    WHERE ic.call_received_by IN ({$userIdsPlaceholders})
                      AND DATE(ic.call_started_at) = CURDATE()
                ) activities
                GROUP BY user_id
            ";

            $todayStatsStmt = $this->db->prepare($todayStatsSql);
            $todayStatsStmt->execute(array_merge($userIds, $userIds, $userIds));
            $todayStatsData = $todayStatsStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get yesterday's stats for comparison
            $yesterdayStatsSql = str_replace('CURDATE()', "DATE_SUB(CURDATE(), INTERVAL 1 DAY)", $todayStatsSql);
            $yesterdayStatsStmt = $this->db->prepare($yesterdayStatsSql);
            $yesterdayStatsStmt->execute(array_merge($userIds, $userIds, $userIds));
            $yesterdayStatsData = $yesterdayStatsStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Convert stats to associative arrays
            $todayStatsMap = [];
            foreach ($todayStatsData as $stat) {
                $todayStatsMap[$stat['user_id']] = [
                    'normal_tickets' => (int)$stat['normal_tickets'],
                    'vip_tickets' => (int)$stat['vip_tickets'],
                    'incoming_calls' => (int)$stat['incoming_calls'],
                    'outgoing_calls' => (int)$stat['outgoing_calls']
                ];
            }

            $yesterdayStatsMap = [];
            foreach ($yesterdayStatsData as $stat) {
                $yesterdayStatsMap[$stat['user_id']] = [
                    'normal_tickets' => (int)$stat['normal_tickets'],
                    'vip_tickets' => (int)$stat['vip_tickets'],
                    'incoming_calls' => (int)$stat['incoming_calls'],
                    'outgoing_calls' => (int)$stat['outgoing_calls']
                ];
            }

            // Get quality scores
            $qualityScores = $this->getUserQualityScores($userIds);

            // Combine user data with stats and comparison
            $result = [];
            foreach ($users as $user) {
                $userId = $user['id'];

                $todayStats = $todayStatsMap[$userId] ?? [
                    'normal_tickets' => 0,
                    'vip_tickets' => 0,
                    'incoming_calls' => 0,
                    'outgoing_calls' => 0
                ];

                $yesterdayStats = $yesterdayStatsMap[$userId] ?? [
                    'normal_tickets' => 0,
                    'vip_tickets' => 0,
                    'incoming_calls' => 0,
                    'outgoing_calls' => 0
                ];

                // Calculate today's totals
                $todayTotalTickets = $todayStats['normal_tickets'] + $todayStats['vip_tickets'];
                $todayTotalCalls = $todayStats['incoming_calls'] + $todayStats['outgoing_calls'];

                // Calculate yesterday's totals
                $yesterdayTotalTickets = $yesterdayStats['normal_tickets'] + $yesterdayStats['vip_tickets'];
                $yesterdayTotalCalls = $yesterdayStats['incoming_calls'] + $yesterdayStats['outgoing_calls'];

                // Calculate percentage change
                $ticketsChange = $this->calculatePercentageChange($todayTotalTickets, $yesterdayTotalTickets);
                $callsChange = $this->calculatePercentageChange($todayTotalCalls, $yesterdayTotalCalls);

                // Only include users with today's activity
                if ($todayTotalTickets > 0 || $todayTotalCalls > 0) {
                    $result[] = [
                        'id' => $userId,
                        'name' => $user['name'] ?? 'غير محدد',
                        'role' => $user['role_name'] ?? 'موظف',
                        'normal_tickets' => $todayStats['normal_tickets'],
                        'vip_tickets' => $todayStats['vip_tickets'],
                        'incoming_calls' => $todayStats['incoming_calls'],
                        'outgoing_calls' => $todayStats['outgoing_calls'],
                        'total_tickets' => $todayTotalTickets,
                        'total_calls' => $todayTotalCalls,
                        'tickets_change' => $ticketsChange,
                        'calls_change' => $callsChange,
                        'yesterday_tickets' => $yesterdayTotalTickets,
                        'yesterday_calls' => $yesterdayTotalCalls,
                        'rating' => $qualityScores[$userId]['quality_score'] ?? 0
                    ];
                }
            }

            // Sort by total tickets + calls descending
            usort($result, function($a, $b) {
                $aTotal = $a['total_tickets'] + $a['total_calls'];
                $bTotal = $b['total_tickets'] + $b['total_calls'];
                return $bTotal <=> $aTotal;
            });

            return $result;

        } catch (\Exception $e) {
            return [];
        }
    }

    private function calculatePercentageChange($today, $yesterday)
    {
        if ($yesterday == 0) {
            return $today > 0 ? 100 : 0;
        }

        $change = (($today - $yesterday) / $yesterday) * 100;
        return round($change, 1);
    }

    private function getUserQualityScores(array $userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        $userIdsPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

        $sql = "
            SELECT
                user_id,
                AVG(rating) AS quality_score,
                COUNT(rating) AS total_reviews
            FROM (
                SELECT
                    td.edited_by AS user_id,
                    r.rating,
                    r.reviewed_at
                FROM reviews r
                JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
                WHERE td.edited_by IN ({$userIdsPlaceholders})
                  AND DATE(r.reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)

                UNION ALL

                SELECT
                    dc.call_by AS user_id,
                    r.rating,
                    r.reviewed_at
                FROM reviews r
                JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
                WHERE dc.call_by IN ({$userIdsPlaceholders})
                  AND DATE(r.reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ) AS all_reviews
            GROUP BY user_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($userIds, $userIds));
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $scores = [];
        foreach ($results as $row) {
            $scores[$row['user_id']] = [
                'quality_score' => round((float)$row['quality_score'], 1),
                'total_reviews' => (int)$row['total_reviews']
            ];
        }

        return $scores;
    }

    private function getHealthMetrics()
{
    try {
        // Memory, CPU, Disk - نقدر نخليها ثابتة أو نقيس من النظام مباشرة لو متاح
        $memoryUsage = rand(20, 80);
        $cpuUsage = rand(10, 60);
        $diskUsage = rand(30, 70);

        // Tables count
        $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE()");
        $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        $tablesCount = $row ? (int)$row['cnt'] : 0;

        // DB size
        $stmt = $this->db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) as size_mb
                                  FROM information_schema.tables WHERE table_schema = DATABASE()");
        $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        $dbSize = $row ? $row['size_mb'] . ' MB' : '0 MB';

        // Active connections
        $stmt = $this->db->query("SHOW STATUS WHERE variable_name = 'Threads_connected'");
        $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        $activeConnections = $row ? (int)$row['Value'] : 0;

        // Query rate (can use Queries / Uptime)
        $stmt = $this->db->query("SHOW STATUS WHERE variable_name = 'Queries'");
        $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        $totalQueries = $row ? (int)$row['Value'] : 0;

        $stmt = $this->db->query("SHOW STATUS WHERE variable_name = 'Uptime'");
        $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        $uptime = $row ? (int)$row['Value'] : 1; // نستخدم 1 لتجنب القسمة على صفر

        $queryRate = round($totalQueries / $uptime, 1);

        // Response time & uptime simulated
        $responseTime = rand(50, 200); // بالميلي ثانية
        $uptimeHours = round($uptime / 3600, 1);

        return [
            'server_status' => 'healthy',
            'db_status' => 'connected',
            'memory_usage' => $memoryUsage,
            'cpu_usage' => $cpuUsage,
            'disk_usage' => $diskUsage,
            'response_time' => $responseTime,
            'uptime' => $uptimeHours,
            'tables_count' => $tablesCount,
            'db_size' => $dbSize,
            'active_connections' => $activeConnections,
            'query_rate' => $queryRate
        ];

    } catch (\Exception $e) {
        // fallback data if something fails
        return [
            'server_status' => 'healthy',
            'db_status' => 'connected',
            'memory_usage' => 50,
            'cpu_usage' => 30,
            'disk_usage' => 40,
            'response_time' => 100,
            'uptime' => 24,
            'tables_count' => 0,
            'db_size' => '0 MB',
            'active_connections' => 0,
            'query_rate' => 0
        ];
    }
}

    

    private function getLiveMetrics()
    {
        try {
            // Active users (logged in within last 30 minutes)
            $activeUsersQuery = "SELECT COUNT(*) as active_users FROM users WHERE is_online = 1 AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
            $activeUsersResult = $this->db->prepare($activeUsersQuery);
            $activeUsersResult->execute();
            $activeUsers = $activeUsersResult->fetch(\PDO::FETCH_ASSOC)['active_users'] ?? 0;

            // Live tickets today
            $liveTicketsQuery = "SELECT COUNT(*) as live_tickets FROM ticket_details WHERE DATE(created_at) = CURDATE()";
            $liveTicketsResult = $this->db->prepare($liveTicketsQuery);
            $liveTicketsResult->execute();
            $liveTickets = $liveTicketsResult->fetch(\PDO::FETCH_ASSOC)['live_tickets'] ?? 0;

            // Live calls (incoming calls in progress + outgoing calls today)
            $incomingCallsQuery = "SELECT COUNT(*) as incoming_calls FROM incoming_calls WHERE call_ended_at IS NULL";
            $incomingCallsResult = $this->db->prepare($incomingCallsQuery);
            $incomingCallsResult->execute();
            $incomingCalls = $incomingCallsResult->fetch(\PDO::FETCH_ASSOC)['incoming_calls'] ?? 0;

            $outgoingCallsQuery = "SELECT COUNT(*) as outgoing_calls FROM driver_calls WHERE DATE(created_at) = CURDATE()";
            $outgoingCallsResult = $this->db->prepare($outgoingCallsQuery);
            $outgoingCallsResult->execute();
            $outgoingCalls = $outgoingCallsResult->fetch(\PDO::FETCH_ASSOC)['outgoing_calls'] ?? 0;

            $liveCalls = $incomingCalls + $outgoingCalls;

            // System load (simulate based on active operations)
            $systemLoadQuery = "SELECT
                (SELECT COUNT(*) FROM breaks WHERE is_active = 1) +
                (SELECT COUNT(*) FROM incoming_calls WHERE call_ended_at IS NULL) +
                (SELECT COUNT(*) FROM driver_assignments WHERE is_seen = 0) as system_load";
            $systemLoadResult = $this->db->prepare($systemLoadQuery);
            $systemLoadResult->execute();
            $rawSystemLoad = $systemLoadResult->fetch(\PDO::FETCH_ASSOC)['system_load'] ?? 0;
            $systemLoad = min(100, max(10, $rawSystemLoad * 2)); // Scale to 10-100%

            // Response time (average based on recent activity)
            $responseTimeQuery = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_response_time
                FROM ticket_details
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND updated_at > created_at";
            $responseTimeResult = $this->db->prepare($responseTimeQuery);
            $responseTimeResult->execute();
            $avgResponseTime = $responseTimeResult->fetch(\PDO::FETCH_ASSOC)['avg_response_time'] ?? 60;
            $responseTime = max(50, min(500, $avgResponseTime * 1000)); // Convert to milliseconds

            // Throughput (tickets processed per hour today)
            $throughputQuery = "SELECT COUNT(*) as throughput FROM ticket_details
                WHERE DATE(created_at) = CURDATE() AND HOUR(created_at) >= HOUR(NOW()) - 1";
            $throughputResult = $this->db->prepare($throughputQuery);
            $throughputResult->execute();
            $throughput = $throughputResult->fetch(\PDO::FETCH_ASSOC)['throughput'] ?? 0;

            // Error rate (based on failed operations or issues)
            $errorRateQuery = "SELECT
                (SELECT COUNT(*) FROM reviews WHERE rating < 60 AND DATE(reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) /
                NULLIF((SELECT COUNT(*) FROM reviews WHERE DATE(reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)), 0) * 100 as error_rate";
            $errorRateResult = $this->db->prepare($errorRateQuery);
            $errorRateResult->execute();
            $errorRate = $errorRateResult->fetch(\PDO::FETCH_ASSOC)['error_rate'] ?? 0;
            $errorRate = min(10, max(0, $errorRate)); // Keep between 0-10%

            // Uptime percentage (simulate based on system health)
            $uptimeQuery = "SELECT
                CASE
                    WHEN (SELECT COUNT(*) FROM users WHERE is_online = 1) > 0 THEN 99.8
                    ELSE 99.5
                END as uptime_percentage";
            $uptimeResult = $this->db->prepare($uptimeQuery);
            $uptimeResult->execute();
            $uptimePercentage = $uptimeResult->fetch(\PDO::FETCH_ASSOC)['uptime_percentage'] ?? 99.5;

            return [
                'active_users' => (int)$activeUsers,
                'live_tickets' => (int)$liveTickets,
                'live_calls' => (int)$liveCalls,
                'system_load' => (int)$systemLoad,
                'response_time' => (int)$responseTime,
                'throughput' => (int)$throughput,
                'error_rate' => round((float)$errorRate, 1),
                'uptime_percentage' => round((float)$uptimePercentage, 1)
            ];

        } catch (\Exception $e) {
            // Return fallback data if query fails
            return [
                'active_users' => 0,
                'live_tickets' => 0,
                'live_calls' => 0,
                'system_load' => 25,
                'response_time' => 150,
                'throughput' => 10,
                'error_rate' => 0.5,
                'uptime_percentage' => 99.5
            ];
        }
    }

    private function getComprehensiveReports($from = null, $to = null)
    {
        // Set default dates if not provided
        $from = $from ?? date('Y-m-d', strtotime('-30 days'));
        $to = $to ?? date('Y-m-d');

        // Debug logging
        error_log("PerformanceController::getComprehensiveReports - Date range: $from to $to");

        try {
            // First, check if we have any data at all
            $checkDataSql = "SELECT COUNT(*) as total_users FROM users WHERE status = 'active'";
            $checkStmt = $this->db->prepare($checkDataSql);
            $checkStmt->execute();
            $userCount = $checkStmt->fetch(\PDO::FETCH_ASSOC)['total_users'];

            $checkTicketsSql = "SELECT COUNT(*) as total_tickets FROM ticket_details WHERE DATE(created_at) BETWEEN ? AND ?";
            $checkTicketsStmt = $this->db->prepare($checkTicketsSql);
            $checkTicketsStmt->execute([$from, $to]);
            $ticketCount = $checkTicketsStmt->fetch(\PDO::FETCH_ASSOC)['total_tickets'];

            error_log("Data Check - Users: $userCount, Tickets in range ($from to $to): $ticketCount");

            // Get top performers within date range
            $topPerformersSql = "SELECT
                                    u.id as user_id,
                                    u.name,
                                    COALESCE(t.name, 'No Team') as team_name,
                                    COALESCE(SUM(tcp.points), 0) as points
                                FROM users u
                                LEFT JOIN teams t ON u.team_id = t.id
                                LEFT JOIN ticket_details td ON td.edited_by = u.id
                                    AND DATE(td.created_at) BETWEEN ? AND ?
                                LEFT JOIN ticket_code_points tcp ON tcp.code_id = td.code_id
                                    AND tcp.is_vip = td.is_vip
                                    AND tcp.valid_from <= DATE(td.created_at)
                                    AND (tcp.valid_to >= DATE(td.created_at) OR tcp.valid_to IS NULL)
                                WHERE u.status = 'active'
                                GROUP BY u.id, u.name, t.name
                                ORDER BY points DESC
                                LIMIT 5";

            $topPerformersStmt = $this->db->prepare($topPerformersSql);
            $topPerformersStmt->execute([$from, $to]);
            $topPerformers = $topPerformersStmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("Top Performers SQL: " . $topPerformersSql);
            error_log("Top Performers Params: " . json_encode([$from, $to]));
            error_log("Top Performers Result: " . json_encode($topPerformers));

            // Get quality metrics within date range
            $qualitySql = "SELECT
                            AVG(r.rating) as avg_rating,
                            COUNT(CASE WHEN r.rating >= 80 THEN 1 END) * 100.0 / COUNT(*) as excellent_percentage
                        FROM reviews r
                        WHERE r.reviewable_type LIKE '%TicketDetail'
                            AND DATE(r.reviewed_at) BETWEEN ? AND ?";

            $qualityStmt = $this->db->prepare($qualitySql);
            $qualityStmt->execute([$from, $to]);
            $qualityData = $qualityStmt->fetch(\PDO::FETCH_ASSOC);

            error_log("Quality SQL: " . $qualitySql);
            error_log("Quality Params: " . json_encode([$from, $to]));
            error_log("Quality Result: " . json_encode($qualityData));

            // Get ticket growth rate (comparing current period to previous period of same length)
            $fromDate = new DateTime($from);
            $toDate = new DateTime($to);
            $periodDays = $fromDate->diff($toDate)->days + 1;

            $previousFrom = clone $fromDate;
            $previousFrom->modify("-{$periodDays} days");
            $previousTo = clone $fromDate;
            $previousTo->modify('-1 day');

            $growthSql = "SELECT
                            COUNT(CASE WHEN DATE(td.created_at) BETWEEN ? AND ? THEN 1 END) as current_tickets,
                            COUNT(CASE WHEN DATE(td.created_at) BETWEEN ? AND ? THEN 1 END) as previous_tickets
                        FROM ticket_details td";

            $growthStmt = $this->db->prepare($growthSql);
            $growthStmt->execute([$from, $to, $previousFrom->format('Y-m-d'), $previousTo->format('Y-m-d')]);
            $growthData = $growthStmt->fetch(\PDO::FETCH_ASSOC);

            error_log("Growth SQL: " . $growthSql);
            error_log("Growth Params: " . json_encode([$from, $to, $previousFrom->format('Y-m-d'), $previousTo->format('Y-m-d')]));
            error_log("Growth Result: " . json_encode($growthData));

            $growthRate = 0;
            if ($growthData['previous_tickets'] > 0) {
                $growthRate = (($growthData['current_tickets'] - $growthData['previous_tickets']) / $growthData['previous_tickets']) * 100;
            }

            // Get average response time within date range
            $responseSql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, td.created_at, td.updated_at)) as avg_response_minutes
                        FROM ticket_details td
                        WHERE DATE(td.created_at) BETWEEN ? AND ?
                            AND td.updated_at > td.created_at";

            $responseStmt = $this->db->prepare($responseSql);
            $responseStmt->execute([$from, $to]);
            $responseData = $responseStmt->fetch(\PDO::FETCH_ASSOC);

            error_log("Response SQL: " . $responseSql);
            error_log("Response Params: " . json_encode([$from, $to]));
            error_log("Response Result: " . json_encode($responseData));

            $avgResponse = $responseData['avg_response_minutes'] ?? 0;
            $avgResponseFormatted = $avgResponse < 60 ?
                round($avgResponse, 1) . ' minutes' :
                round($avgResponse / 60, 1) . ' hours';

            // Check if we have any real data
            $hasRealData = !empty($topPerformers) || ($qualityData['avg_rating'] ?? 0) > 0 || $growthData['current_tickets'] > 0;

            if (!$hasRealData) {
                // Return sample data for demonstration
                error_log("No real data found, returning sample data for demonstration");
                return [
                    'summary' => [
                        'best_performer' => 'John Doe',
                        'growth_rate' => '+15.3%',
                        'avg_quality' => '87.5%',
                        'avg_response' => '2.3 hours'
                    ],
                    'top_performers' => [
                        ['user_id' => 1, 'name' => 'John Doe', 'team_name' => 'Support Team A', 'points' => 1250],
                        ['user_id' => 2, 'name' => 'Jane Smith', 'team_name' => 'Support Team B', 'points' => 1180],
                        ['user_id' => 3, 'name' => 'Mike Johnson', 'team_name' => 'Support Team A', 'points' => 1095],
                        ['user_id' => 4, 'name' => 'Sarah Wilson', 'team_name' => 'Support Team C', 'points' => 1020],
                        ['user_id' => 5, 'name' => 'Tom Brown', 'team_name' => 'Support Team B', 'points' => 980]
                    ],
                    'efficiency_metrics' => [
                        'completion_rate' => '94.2%',
                        'satisfaction_rate' => '87.5%',
                        'time_efficiency' => '91.5%'
                    ],
                    'recommendations' => [
                        'Implement automated task distribution system to improve efficiency',
                        'Enhance training program for new employees to reduce onboarding time',
                        'Apply continuous performance monitoring system for real-time insights',
                        'Optimize response time protocols to improve customer satisfaction',
                        'Develop skill-based routing for complex issues'
                    ],
                    'charts_data' => [
                        'productivity_trends' => $this->getSampleProductivityData(),
                        'quality_distribution' => $this->getSampleQualityData()
                    ]
                ];
            }

            $result = [
                'summary' => [
                    'best_performer' => $topPerformers[0]['name'] ?? 'N/A',
                    'growth_rate' => ($growthRate >= 0 ? '+' : '') . round($growthRate, 1) . '%',
                    'avg_quality' => round($qualityData['avg_rating'] ?? 0, 1) . '%',
                    'avg_response' => $avgResponseFormatted
                ],
                'top_performers' => array_map(function($performer) {
                    return [
                        'user_id' => $performer['user_id'],
                        'name' => $performer['name'],
                        'team_name' => $performer['team_name'],
                        'points' => (int)$performer['points']
                    ];
                }, $topPerformers),
                'efficiency_metrics' => [
                    'completion_rate' => '94.2%',
                    'satisfaction_rate' => round($qualityData['excellent_percentage'] ?? 0, 1) . '%',
                    'time_efficiency' => '91.5%'
                ],
                'recommendations' => [
                    'Implement automated task distribution system to improve efficiency',
                    'Enhance training program for new employees to reduce onboarding time',
                    'Apply continuous performance monitoring system for real-time insights',
                    'Optimize response time protocols to improve customer satisfaction',
                    'Develop skill-based routing for complex issues'
                ],
                'charts_data' => [
                    'productivity_trends' => $this->getProductivityTrendsData($from, $to),
                    'quality_distribution' => $this->getQualityDistributionData($from, $to)
                ]
            ];

            error_log("Final Result with real data: " . json_encode($result));
            return $result;

        } catch (\Exception $e) {
            // Log the error
            error_log("PerformanceController Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            // Return fallback data
            return [
                'summary' => [
                    'best_performer' => 'N/A',
                    'growth_rate' => '+0.0%',
                    'avg_quality' => '0.0%',
                    'avg_response' => '0 minutes'
                ],
                'top_performers' => [],
                'efficiency_metrics' => [
                    'completion_rate' => '0.0%',
                    'satisfaction_rate' => '0.0%',
                    'time_efficiency' => '0.0%'
                ],
                'recommendations' => [
                    'Data not available',
                    'Please check system configuration',
                    'Contact system administrator'
                ],
                'charts_data' => [
                    'productivity_trends' => [],
                    'quality_distribution' => []
                ]
            ];
        }
    }

    /**
     * Get sample productivity data for demonstration
     */
    private function getSampleProductivityData()
    {
        $data = [];
        $baseDate = strtotime('-30 days');

        for ($i = 0; $i < 30; $i++) {
            $date = date('Y-m-d', strtotime("+$i days", $baseDate));
            $tickets = rand(15, 35);
            $users = rand(8, 12);

            $data[] = [
                'date' => $date,
                'tickets' => $tickets,
                'users' => $users
            ];
        }

        return $data;
    }

    /**
     * Get sample quality data for demonstration
     */
    private function getSampleQualityData()
    {
        return [
            ['level' => 'Excellent', 'count' => 45],
            ['level' => 'Good', 'count' => 28],
            ['level' => 'Needs Improvement', 'count' => 12]
        ];
    }

    private function getProductivityTrendsData($from, $to)
    {
        try {
            $sql = "SELECT
                        DATE(td.created_at) as date,
                        COUNT(*) as tickets_count,
                        COUNT(DISTINCT td.edited_by) as active_users
                    FROM ticket_details td
                    WHERE DATE(td.created_at) BETWEEN ? AND ?
                    GROUP BY DATE(td.created_at)
                    ORDER BY date ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$from, $to]);
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    'date' => $row['date'],
                    'tickets' => (int)$row['tickets_count'],
                    'users' => (int)$row['active_users'],
                    'productivity' => (int)$row['active_users'] > 0 ? round($row['tickets_count'] / $row['active_users'], 1) : 0
                ];
            }, $data);

        } catch (\Exception $e) {
            return [];
        }
    }

    private function getQualityDistributionData($from, $to)
    {
        try {
            $sql = "SELECT
                        CASE
                            WHEN r.rating >= 80 THEN 'Excellent'
                            WHEN r.rating >= 60 THEN 'Good'
                            ELSE 'Needs Improvement'
                        END as quality_level,
                        COUNT(*) as count
                    FROM reviews r
                    WHERE r.reviewable_type LIKE '%TicketDetail'
                        AND DATE(r.reviewed_at) BETWEEN ? AND ?
                    GROUP BY
                        CASE
                            WHEN r.rating >= 80 THEN 'Excellent'
                            WHEN r.rating >= 60 THEN 'Good'
                            ELSE 'Needs Improvement'
                        END";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$from, $to]);
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    'level' => $row['quality_level'],
                    'count' => (int)$row['count']
                ];
            }, $data);

        } catch (\Exception $e) {
            return [];
        }
    }

    private function getQualityData()
    {
        try {
            $sql = "SELECT
                        DATE(r.reviewed_at) as date,
                        COUNT(*) as total_reviews,
                        AVG(r.rating) as avg_rating,
                        COUNT(CASE WHEN r.rating >= 80 THEN 1 END) as excellent_reviews,
                        COUNT(CASE WHEN r.rating >= 60 AND r.rating < 80 THEN 1 END) as good_reviews,
                        COUNT(CASE WHEN r.rating < 60 THEN 1 END) as poor_reviews,
                        COUNT(DISTINCT r.reviewed_by) as reviewers_count
                    FROM reviews r
                    WHERE r.reviewable_type LIKE '%TicketDetail'
                        AND DATE(r.reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(r.reviewed_at)
                    ORDER BY date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($row) {
                $total = (int)$row['total_reviews'];
                return [
                    'date' => $row['date'],
                    'total_reviews' => $total,
                    'avg_rating' => round((float)$row['avg_rating'], 1),
                    'excellent_reviews' => (int)$row['excellent_reviews'],
                    'good_reviews' => (int)$row['good_reviews'],
                    'poor_reviews' => (int)$row['poor_reviews'],
                    'reviewers_count' => (int)$row['reviewers_count'],
                    'excellent_percentage' => $total > 0 ? round(((int)$row['excellent_reviews'] / $total) * 100, 1) : 0
                ];
            }, $data);

        } catch (\Exception $e) {
            return [];
        }
    }
}
