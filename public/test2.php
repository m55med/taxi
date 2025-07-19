<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// بيانات الاتصال
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
    // الاتصال بقاعدة البيانات
    $pdo = new PDO($dsn, $user, $pass, $options);

    // مثال استعلام: عرض المستخدمين الفعالين
    $stmt = $pdo->query("SELECT id, username FROM users WHERE status = 'active'");
    $users = $stmt->fetchAll();

    echo "<pre>";
    print_r($users);
    echo "</pre>";

} catch (PDOException $e) {
    echo "❌ DB Error: " . $e->getMessage();
}
