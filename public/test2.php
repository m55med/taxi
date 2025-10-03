<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'taxif_cstaxi';
$user = 'taxif_root';
$pass = 'lcU*bQuQDEB0';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // اختبار دالة getActiveSessions من PerformanceModel
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

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $activeSessions = $stmt->fetchAll();

    echo "<h2>Active Sessions Test</h2>";
    echo "<pre>";
    print_r($activeSessions);
    echo "</pre>";

    // اختبار عدد المستخدمين النشطين
    $countSql = "SELECT COUNT(*) as active_count FROM users WHERE is_online = 1";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute();
    $activeCount = $countStmt->fetch();

    echo "<h2>Active Users Count: " . $activeCount['active_count'] . "</h2>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
