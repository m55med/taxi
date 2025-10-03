<?php

namespace App\Controllers\Logs;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\PointsService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LogsController extends Controller
{
    private $logModel;
    private $rolesWithPointsAccess = ['Admin', 'admin', 'Team_leader', 'Quality', 'quality', 'developer'];

    /**
     * Convert UTC datetime to Cairo timezone
     */
    private function convertToCairoTime($utcDateTime)
    {
        if (empty($utcDateTime)) {
            return $utcDateTime;
        }

        try {
            $utc = new \DateTime($utcDateTime, new \DateTimeZone('UTC'));
            $cairo = new \DateTimeZone('Africa/Cairo');
            $utc->setTimezone($cairo);
            return $utc->format('Y-m-d h:i A');
        } catch (\Exception $e) {
            error_log('Error converting datetime to Cairo timezone: ' . $e->getMessage());
            return $utcDateTime;
        }
        
    }

    /**
     * Convert datetime fields in array or object from UTC to Cairo timezone
     */
    private function convertArrayTimesToCairo(&$array, $fields = ['activity_date'])
    {
        if (!is_array($array) && !is_object($array)) {
            return;
        }

        foreach ($array as $key => &$item) {
            if (is_array($item) || is_object($item)) {
                // Recursive for nested arrays or objects
                $this->convertArrayTimesToCairo($item, $fields);
            } elseif (in_array($key, $fields) && !empty($item)) {
                $item = $this->convertToCairoTime($item);
            }
        }
    }

    public function __construct()
    {
        parent::__construct(); // It's good practice to call parent constructor
        // The login check is now handled by $this->authorize() in the methods.
        $this->logModel = $this->model('Logs/Log');
    }

    public function index()
    {
        Auth::requireLogin();
        $page_main_title = 'Activity Log';

        // Default filters from GET request
        $filters = [
            'activity_type' => $_GET['activity_type'] ?? 'all',
            'user_id' => $_GET['user_id'] ?? 'all',
            'team_id' => $_GET['team_id'] ?? 'all',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // ======= تحويل التواريخ من Cairo إلى UTC قبل الاستعلام =======
        // الحل الأمثل: احفظ التاريخ الأصلي للمقارنة في قاعدة البيانات
        if (!empty($filters['date_from'])) {
            // احفظ التاريخ الأصلي الذي أرسله المستخدم
            $filters['original_date_from'] = $filters['date_from'];

            // للتوافق مع باقي النظام، احتفظ بالتحويل إلى UTC
            $dateFromCairo = new \DateTimeImmutable($filters['date_from'] . ' 00:00:00', new \DateTimeZone('Africa/Cairo'));
            $filters['date_from'] = $dateFromCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');
        }

        if (!empty($filters['date_to'])) {
            $filters['original_date_to'] = $filters['date_to'];
            $dateToCairo = new \DateTimeImmutable($filters['date_to'] . ' 23:59:59', new \DateTimeZone('Africa/Cairo'));
            $filters['date_to'] = $dateToCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');
        }

        // Apply role-based restrictions
        $userRole = $_SESSION['role_name'] ?? null;
        $userId = $_SESSION['user_id'];

        if ($userRole === 'agent' || $userRole === 'employee') {
            $filters['user_id'] = $userId; // Agents can only see their own activities
        } elseif ($userRole === 'Team_leader') {
            $teamId = $this->logModel->getTeamIdForLeader($userId);
            if ($teamId) {
                // If a team leader tries to view "all teams", restrict to their own team.
                if (!isset($_GET['team_id']) || $_GET['team_id'] === 'all' || $_GET['team_id'] == '') {
                    $filters['team_id'] = $teamId;
                }
            } else {
                // If not a leader of any team, they only see their own activities
                $filters['user_id'] = $userId;
            }
        }

        $showPoints = in_array($userRole, $this->rolesWithPointsAccess);

        // Handle Export All
        if (isset($_GET['export'])) {
            $export_type = $_GET['export'];
            $result = $this->logModel->getActivities($filters, null, 0); // null limit to get all
            $activities = $result['activities'];

            if ($showPoints) {
                $pointsService = new PointsService();
                foreach ($activities as $activity) {
                    $pointsService->calculateForActivity($activity);
                }
            }

            // Convert datetime fields to Cairo timezone
            $this->convertArrayTimesToCairo($activities);

            $summary = $this->logModel->getActivitiesSummary($filters);

            if ($export_type === 'excel') {
                $this->_exportToExcel($activities, $summary);
            } elseif ($export_type === 'json') {
                $this->_exportToJson($activities);
            }
            return;
        }

        // Data for filter dropdowns
        $users = $this->logModel->getUsers();
        $teams = $this->logModel->getTeams();

        // Pagination logic
        $limitOptions = [20, 50, 100, 250, 500];
        $limit = isset($_GET['limit']) && in_array($_GET['limit'], $limitOptions) ? (int) $_GET['limit'] : 50;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Get activities with pagination
        $result = $this->logModel->getActivities($filters, $limit, $offset);
        $activities = $result['activities'];
        $totalRecords = $result['total'];

        // Calculate points for each activity
        if ($showPoints) {
            $pointsService = new PointsService();
            foreach ($activities as $activity) {
                $pointsService->calculateForActivity($activity);
            }
        }

        // Convert datetime fields to Cairo timezone
        $this->convertArrayTimesToCairo($activities);

        $totalPages = ceil($totalRecords / $limit);

        // Get activities summary
        $activitiesSummary = $this->logModel->getActivitiesSummary($filters);

        // أعد التواريخ الأصلية للـ view (للروابط)
        $originalFilters = $filters;
        if (!empty($originalFilters['original_date_from'])) {
            $originalFilters['date_from'] = $originalFilters['original_date_from'];
        }
        if (!empty($originalFilters['original_date_to'])) {
            $originalFilters['date_to'] = $originalFilters['original_date_to'];
        }

        $data = [
            'page_main_title' => $page_main_title,
            'activities' => $activities ?? [],
            'activitiesSummary' => $activitiesSummary,
            'filters' => $originalFilters,
            'users' => $users,
            'teams' => $teams,
            'userRole' => $userRole,
            'showPoints' => $showPoints,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'limit' => $limit,
                'limitOptions' => $limitOptions,
                'totalRecords' => $totalRecords
            ]
        ];
        

        $this->view('logs/index', $data);
    }

    public function bulk_export()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['activity_ids'])) {
            redirect('logs');
            return;
        }

        $activity_ids = $_POST['activity_ids'];
        $export_type = $_POST['export_type'] ?? 'excel';

        $activities = $this->logModel->getActivitiesByIds($activity_ids);

        $userRole = $_SESSION['role_name'];
        $showPoints = in_array($userRole, $this->rolesWithPointsAccess);

        // Calculate points for each activity
        if ($showPoints) {
            $pointsService = new PointsService();
            foreach ($activities as $activity) {
                $pointsService->calculateForActivity($activity);
            }
        }

        // Convert datetime fields to Cairo timezone
        $this->convertArrayTimesToCairo($activities);

        if ($export_type === 'excel') {
            // Summary is not available for selected items, so we pass null
            $this->_exportToExcel($activities, null);
        } elseif ($export_type === 'json') {
            $this->_exportToJson($activities);
        }
    }

    private function _exportToExcel($activities, $summary)
    {
        // تحويل التاريخ مرة واحدة فقط في بداية الدالة
        $this->convertArrayTimesToCairo($activities);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Activity Log');

        $userRole = $_SESSION['role_name'];
        $showPoints = in_array($userRole, $this->rolesWithPointsAccess);

        // Headers
        $headers = ['Type', 'Is VIP', 'Details', 'Secondary Details', 'Employee', 'Team', 'Date'];
        if ($showPoints) {
            $headers[] = 'Points';
        }
        $sheet->fromArray($headers, null, 'A1');

        // Style Headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']]
        ];
        $headerRange = $showPoints ? 'A1:H1' : 'A1:G1';
        $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($activities as $activity) {
            $is_vip = ($activity->activity_type === 'Ticket' && $activity->is_vip) ? 'Yes' : 'No';

            $rowData = [
                $activity->activity_type,
                $is_vip,
                $activity->details_primary,
                $activity->details_secondary,
                $activity->username,
                $activity->team_name ?? 'N/A',
                date('Y-m-d h:i A', strtotime($activity->activity_date)), // التاريخ تم تحويله بالفعل
            ];
    
            if ($showPoints) {
                $rowData[] = $activity->points ?? 0;
            }
    
            $sheet->fromArray($rowData, null, 'A' . $row);
            $row++;
        }
    
        // Auto-size columns
        $lastCol = $showPoints ? 'H' : 'G';
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    
        // Add summary if available
        if ($summary) {
            $row += 2;
            $summary_start_row = $row;
    
            // Title
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue('A' . $row, 'Activity Summary');
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $row++;
    
            $summaryData = [
                ['Total Tickets', ($summary['Normal Ticket'] ?? 0) + ($summary['VIP Ticket'] ?? 0)],
                ['  Normal Tickets', $summary['Normal Ticket'] ?? 0],
                ['  VIP Tickets', $summary['VIP Ticket'] ?? 0],
                [], // spacer
                ['Total Calls', ($summary['Incoming Call'] ?? 0) + ($summary['Outgoing Call'] ?? 0)],
                ['  Incoming Calls', $summary['Incoming Call'] ?? 0],
                ['  Outgoing Calls', $summary['Outgoing Call'] ?? 0],
                [], // spacer
                ['Total Assignments', $summary['Assignment'] ?? 0]
            ];
    
            foreach ($summaryData as $summary_row) {
                if (empty($summary_row)) {
                    $row++;
                    continue;
                }
    
                $sheet->fromArray($summary_row, null, 'A' . $row);
    
                if (strpos($summary_row[0], 'Total') === 0) {
                    $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');
                }
    
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }
    
            // Add border
            $summary_end_row = $row - 1;
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '9CA3AF'],
                    ],
                ],
            ];
            $sheet->getStyle("A{$summary_start_row}:B{$summary_end_row}")->applyFromArray($borderStyle);
        }
    
        // Output
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
        header('Content-Disposition: attachment; filename="activity_log_' . date('Y-m-d') . '.xlsx"');
        $writer->save('php://output');
        exit;
    }
    

    private function _exportToJson($activities)
    {
        $userRole = $_SESSION['role_name'];
        $showPoints = in_array($userRole, $this->rolesWithPointsAccess);

        if (!$showPoints) {
            foreach ($activities as &$activity) {
                unset($activity->points);
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="activity_log_' . date('Y-m-d') . '.json"');
        echo json_encode($activities, JSON_PRETTY_PRINT);
        exit;
    }
}