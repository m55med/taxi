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

    $sql = "
    CREATE TABLE IF NOT EXISTS restaurants (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name_ar VARCHAR(255) NULL,
        name_en VARCHAR(255) NULL,
        category VARCHAR(100) NULL,
        governorate VARCHAR(100) NULL,
        city VARCHAR(100) NULL,
        address TEXT NULL,
        is_chain TINYINT(1) NULL, -- 0 أو 1
        num_stores INT NULL,
        contact_name VARCHAR(255) NULL,
        email VARCHAR(255) NULL,
        phone VARCHAR(50) NULL,
        pdf_path VARCHAR(500) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "تم إنشاء جدول المطاعم بنجاح.";
} catch (PDOException $e) {
    echo "فشل الاتصال أو إنشاء الجدول: " . $e->getMessage();
}
