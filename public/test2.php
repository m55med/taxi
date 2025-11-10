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

    // 1. وقت السيرفر
    $serverTime = new DateTime("now", new DateTimeZone("UTC"));
    echo "Server UTC Time: " . $serverTime->format('Y-m-d H:i:s') . "\n";

    // 2. وقت قاعدة البيانات
    $stmt = $pdo->query("SELECT NOW() AS db_time, @@global.time_zone AS db_global_tz, @@session.time_zone AS db_session_tz");
    $row = $stmt->fetch();
    echo "Database Time: " . $row['db_time'] . "\n";
    echo "Database Global Timezone: " . $row['db_global_tz'] . "\n";
    echo "Database Session Timezone: " . $row['db_session_tz'] . "\n";

    // 3. وقت المستخدم (مثال: توقيت القاهرة)
    $userTime = new DateTime("now", new DateTimeZone("Africa/Cairo"));
    echo "User Local Time (Cairo): " . $userTime->format('Y-m-d H:i:s') . "\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
