<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// بيانات الاتصال
$host = 'localhost';
$db   = 'taxif_cstaxi';
$user = 'taxif_root';
$pass = 'lcU*bQuQDEB0';
$charset = 'utf8mb4';

// DSN = Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// إعدادات PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // يرمي Exception عند وجود خطأ
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // نتائج الاستعلام تكون associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // استخدام prepared statements الحقيقية
];

try {
    // إنشاء اتصال PDO
    $pdo = new PDO($dsn, $user, $pass, $options);

    // SQL لإنشاء الجدول
    $sql = "
    CREATE TABLE IF NOT EXISTS establishments (
        id INT AUTO_INCREMENT PRIMARY KEY, -- معرف أساسي تلقائي للمنشأة
        establishment_name VARCHAR(255) NULL, -- اسم المنشأة
        legal_name VARCHAR(255) NULL, -- الاسم القانوني
        taxpayer_number VARCHAR(50) NULL, -- الرقم الضريبي (اختياري)
        street VARCHAR(255) NULL, -- الشارع
        house_number VARCHAR(50) NULL, -- رقم المنزل
        postal_zip VARCHAR(20) NULL, -- الرمز البريدي
        establishment_email VARCHAR(255) NULL, -- ايميل المنشأة
        establishment_phone VARCHAR(50) NULL, -- رقم هاتف المنشأة
        owner_full_name VARCHAR(255) NULL, -- الاسم الكامل للمالك
        owner_position VARCHAR(100) NULL, -- منصب المالك
        owner_email VARCHAR(255) NULL, -- ايميل المالك
        owner_phone VARCHAR(50) NULL, -- هاتف المالك
        description TEXT NULL, -- وصف (اختياري)
        establishment_logo TEXT NULL, -- اللوجو (رابط أو base64)
        establishment_header_image TEXT NULL, -- صورة الرأس (رابط أو base64)
        marketer_id INT NULL, -- معرف المسوق المرتبط بالمنشأة
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- تاريخ الإنشاء

        FOREIGN KEY (marketer_id) REFERENCES users(id) 
            ON DELETE SET NULL -- لو المستخدم اتشال، يتحول العمود NULL
            ON UPDATE CASCADE  -- لو اتغير id المستخدم يتحدث تلقائيًا
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    // تنفيذ SQL
    $pdo->exec($sql);

    echo "✅ تم إنشاء الجدول establishments بنجاح";

} catch (PDOException $e) {
    // في حالة وجود خطأ
    echo "❌ خطأ في الاتصال أو إنشاء الجدول: " . $e->getMessage();
}
