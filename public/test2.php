<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// بيانات الاتصال
$host = 'localhost';
$db   = 'taxif_cstaxi';
$user = 'taxif_root';
$pass = 'lcU*bQuQDEB0';
$charset = 'utf8mb4';

// DSN
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// إعدادات PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // حذف البريكات الخاصة باليوزر ID = 9
    $stmt = $pdo->prepare("DELETE FROM breaks WHERE user_id = :user_id");
    $stmt->execute([':user_id' => 9]);

    echo "✅ تم حذف " . $stmt->rowCount() . " بريك.";

} catch (PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
