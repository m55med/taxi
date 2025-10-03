<?php

namespace App\Controllers\Performance;

use App\Core\Controller;
use App\Models\Listings\ListingModel;
use App\Models\Calls\Call;
use App\Models\Performance\PerformanceModel;

class PerformanceController extends Controller
{
    private $listingModel;
    private $callModel;
    private $performanceModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->listingModel = new ListingModel();
        $this->callModel = new Call();
        $this->performanceModel = new PerformanceModel();
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
            $userActivity = $this->performanceModel->getUserActivityStats($today);

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

            // تحويل التاريخ من Cairo إلى UTC للبحث في قاعدة البيانات
            $cairoDate = new \DateTimeImmutable($selectedDate . ' 00:00:00', new \DateTimeZone('Africa/Cairo'));
            $utcDate = $cairoDate->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');

            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $lastWeek = date('Y-m-d', strtotime('-7 days'));

            $data = [
                'success' => true,
                'data' => [
                    'today' => $this->performanceModel->getDaySummary($today),
                    'yesterday' => $this->performanceModel->getDaySummary($yesterday),
                    'last_week_avg' => $this->performanceModel->getWeekAverage($lastWeek, $today),
                    'trends' => $this->performanceModel->getTrendsData($utcDate, $selectedDate), // تمرير التاريخ الأصلي
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
            $selectedDate = $_GET['date'] ?? date('Y-m-d');

            $data = [
                'success' => true,
                'data' => $this->performanceModel->getTeamStats($selectedDate), // تمرير التاريخ القاهرة للمقارنة
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
                'data' => $this->performanceModel->getTicketAnalyticsData(),
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
                'data' => $this->performanceModel->getCallCenterStats(),
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
                'data' => $this->performanceModel->getIndividualUserStats(),
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
                'data' => $this->performanceModel->getHealthMetrics(),
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
                'data' => $this->performanceModel->getQualityData(),
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
                'data' => $this->performanceModel->getLiveMetrics(),
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

            // تحويل التواريخ من Cairo إلى UTC للبحث في قاعدة البيانات
            $fromCairo = new \DateTimeImmutable($from . ' 00:00:00', new \DateTimeZone('Africa/Cairo'));
            $fromUtc = $fromCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');

            $toCairo = new \DateTimeImmutable($to . ' 23:59:59', new \DateTimeZone('Africa/Cairo'));
            $toUtc = $toCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');

            error_log("API Request - Cairo dates: $from to $to, UTC dates: $fromUtc to $toUtc");

            $data = [
                'success' => true,
                'data' => $this->performanceModel->getComprehensiveReports($fromUtc, $toUtc, $from, $to),
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
            $data = [
                'success' => true,
                'data' => $this->performanceModel->getRecentTickets()
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
            $data = [
                'success' => true,
                'data' => $this->performanceModel->getActiveSessions()
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
     * Get real-time activity feed
     */
    public function getRealtimeActivity()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->performanceModel->getRealtimeActivity()
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
     * Get active users list for realtime dashboard
     */
    public function getActiveUsersList()
    {
        header('Content-Type: application/json');

        try {
            $data = [
                'success' => true,
                'data' => $this->performanceModel->getActiveSessions(),
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
}
