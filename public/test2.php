<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connection details
$host = 'localhost';
$db   = 'taxif_cstaxi';
$user = 'taxif_root';
$pass = 'lcU*bQuQDEB0';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // حط هنا الـ marketer_id اللي عايز تختبره
    $marketerId = 115;

    // الاستعلام الصحيح على جدول referral_visits
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_visits FROM referral_visits WHERE affiliate_user_id = ?");
    $stmt->execute([$marketerId]);

    $row = $stmt->fetch();

    echo "Marketer ID {$marketerId} has " . $row['total_visits'] . " visits.";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
