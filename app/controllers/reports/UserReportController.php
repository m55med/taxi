<?php

namespace App\Controllers\Reports;

use App\Core\Controller;

class UserReportController extends Controller
{
    private $userReportModel;
    private $roleModel;

    public function __construct()
    {
        parent::__construct();

        // التحقق من الصلاحيات
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        // صلاحيات محددة لهذه الصفحة يمكن تعديلها
        if (!in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager', 'team_leader'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->userReportModel = $this->model('reports/UserReport');
        $this->roleModel = $this->model('Role');
    }

    public function index()
    {
        $filters = [
            'role_id' => $_GET['role_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $usersData = $this->userReportModel->getUsersReport($filters);

        $data = [
            'users' => $usersData['users'],
            'summary_stats' => $usersData['summary_stats'],
            'roles' => $this->roleModel->getAll(),
            'filters' => $filters,
        ];

        // لا يوجد تصدير حاليا، سيتم التعامل مع مسار مختلف
        // if (isset($_GET['export']) && $_GET['export'] === 'excel') {
        //     $this->exportUsersToExcel($data['users'], 'users_report');
        //     exit;
        // }

        $this->view('reports/users', $data);
    }

    public function export()
    {
        $filters = [
            'role_id' => $_GET['role_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $usersData = $this->userReportModel->getUsersReport($filters);

        $this->exportUsersToExcel($usersData['users'], 'users_report');
    }


    private function exportUsersToExcel($data, $filename)
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $excluded_keys = ['call_stats'];

        echo '<table border="1">';

        // رأس الجدول
        if (!empty($data)) {
            echo '<tr>';
            foreach (array_keys($data[0]) as $key) {
                if (in_array($key, $excluded_keys)) {
                    continue;
                }
                echo '<th>' . htmlspecialchars($key) . '</th>';
            }
            // Add call_stats headers
            echo '<th>total_calls</th>';
            echo '<th>answered</th>';
            echo '<th>no_answer</th>';
            echo '<th>busy</th>';
            echo '<th>answered_rate</th>';
            echo '<th>no_answer_rate</th>';
            echo '<th>busy_rate</th>';
            echo '<th>today_total</th>';
            echo '<th>today_answered</th>';
            echo '<th>today_no_answer</th>';
            echo '<th>today_busy</th>';
            echo '</tr>';
        }

        // بيانات الجدول
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) {
                if (in_array($key, $excluded_keys)) {
                    continue;
                }
                if (is_array($value)) {
                    echo '<td>' . htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE)) . '</td>';
                } else {
                    echo '<td>' . htmlspecialchars($value ?? '') . '</td>';
                }
            }
            // Add call_stats data
            if (isset($row['call_stats'])) {
                $stats = $row['call_stats'];
                echo '<td>' . htmlspecialchars($stats['total_calls'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars($stats['answered'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars($stats['no_answer'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars($stats['busy'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars(number_format($stats['answered_rate'] ?? 0, 1)) . '%</td>';
                echo '<td>' . htmlspecialchars(number_format($stats['no_answer_rate'] ?? 0, 1)) . '%</td>';
                echo '<td>' . htmlspecialchars(number_format($stats['busy_rate'] ?? 0, 1)) . '%</td>';
                echo '<td>' . htmlspecialchars($stats['today_total'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars($stats['today_answered'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars($stats['today_no_answer'] ?? '0') . '</td>';
                echo '<td>' . htmlspecialchars($stats['today_busy'] ?? '0') . '</td>';
            } else {
                for ($i = 0; $i < 11; $i++) {
                    echo '<td>0</td>';
                }
            }
            echo '</tr>';
        }

        echo '</table>';
    }
} 