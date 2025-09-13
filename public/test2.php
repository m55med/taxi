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

    // كل أوامر الإنـدكس اللي عايز تعملها
    $queries = [
        "CREATE INDEX idx_ticket_details_edited_by_created_at ON ticket_details (edited_by, created_at)",
        "CREATE INDEX idx_ticket_details_platform_id ON ticket_details (platform_id)",
        "CREATE INDEX idx_ticket_details_code_id ON ticket_details (code_id)",
        "CREATE INDEX idx_driver_calls_call_by_created_at ON driver_calls (call_by, created_at)",
        "CREATE INDEX idx_incoming_calls_call_received_by_started_at ON incoming_calls (call_received_by, call_started_at)",
        "CREATE INDEX idx_reviews_reviewable_type_id ON reviews (reviewable_type, reviewable_id)",
        "CREATE INDEX idx_reviews_reviewed_at ON reviews (reviewed_at)",
        "CREATE INDEX idx_ticket_code_points_code_id_vip_valid ON ticket_code_points (code_id, is_vip, valid_from, valid_to)",
        "CREATE INDEX idx_call_points_call_type_valid ON call_points (call_type, valid_from, valid_to)",
        "CREATE INDEX idx_platforms_name ON platforms (name)",
        "CREATE INDEX idx_users_role_id ON users (role_id)",
        "CREATE INDEX idx_users_status ON users (status)",
        "CREATE INDEX idx_team_members_user_id ON team_members (user_id)",
        "CREATE INDEX idx_team_members_team_id ON team_members (team_id)",
        "CREATE INDEX idx_ticket_details_complex ON ticket_details (edited_by, created_at, platform_id, code_id, is_vip)",
        "CREATE INDEX idx_driver_calls_complex ON driver_calls (call_by, created_at)",
        "CREATE INDEX idx_incoming_calls_complex ON incoming_calls (call_received_by, call_started_at)",
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
            echo "Executed: $sql<br>";
        } catch (PDOException $e) {
            echo "Failed: $sql<br>Error: " . $e->getMessage() . "<br><br>";
        }
    }

    echo "All queries processed.";

} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
