<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// اتصال مباشر بـPDO
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

    $sql = "SELECT COUNT(*) as total FROM ticket_details";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    echo "📊 إجمالي عدد تفصيلات التذاكر من أول السيستم: " . $result['total'];
    
} catch (PDOException $e) {
    echo "خطأ في الاتصال أو التنفيذ: " . $e->getMessage();
}
