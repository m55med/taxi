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
    $pdo = new PDO($dsn, $user, $pass, $options);

    // أمر إنشاء الجدول
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS driver_documents_required (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT NOT NULL,
        document_type_id INT NOT NULL,
        status ENUM('missing', 'submitted', 'rejected') DEFAULT 'missing',
        note TEXT,
        updated_by INT DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (driver_id) REFERENCES drivers(id),
        FOREIGN KEY (document_type_id) REFERENCES document_types(id),
        FOREIGN KEY (updated_by) REFERENCES users(id),
        
        UNIQUE(driver_id, document_type_id)
    );
    SQL;

    $pdo->exec($sql);
    echo "✅ تم إنشاء الجدول driver_documents_required بنجاح.";

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال أو التنفيذ: " . $e->getMessage();
}
