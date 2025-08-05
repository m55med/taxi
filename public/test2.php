<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// بيانات الاتصال
$host = 'localhost';
$db = 'taxif_cstaxi';
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

    // تغيير ترميز قاعدة البيانات نفسها
    $pdo->exec("ALTER DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // الحصول على كل الجداول
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "🔄 Converting table: $table...\n";

        // تغيير ترميز الجدول نفسه
        $pdo->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    }

    echo "\n✅ تم تحويل كل الجداول والأعمدة إلى utf8mb4 بنجاح.\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
