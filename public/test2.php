<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connection details
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
    echo "<h1>تحديث قاعدة البيانات لنظام إحالة المطاعم</h1>";

    // 1. Add 'referred_by_user_id' to 'restaurants' table
    try {
        $pdo->exec("
            ALTER TABLE `restaurants`
            ADD COLUMN `referred_by_user_id` INT NULL DEFAULT NULL AFTER `pdf_path`,
            ADD CONSTRAINT `fk_restaurants_referred_by`
                FOREIGN KEY (`referred_by_user_id`)
                REFERENCES `users`(`id`)
                ON DELETE SET NULL
                ON UPDATE CASCADE;
        ");
        echo "<p style='color:green;'>تم تحديث جدول `restaurants` بنجاح.</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42S21' || strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p style='color:orange;'>تم تحديث جدول `restaurants` مسبقًا.</p>";
        } else {
            throw $e;
        }
    }

    // 2. Create 'restaurant_referral_visits' table
    try {
        $pdo->exec("
            CREATE TABLE `restaurant_referral_visits` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `affiliate_user_id` INT NULL DEFAULT NULL,
                `visit_recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `ip_address` VARCHAR(45) NOT NULL,
                `user_agent` TEXT DEFAULT NULL,
                `referer_url` TEXT DEFAULT NULL,
                `registration_status` ENUM(
                    'visit_only',
                    'form_opened',
                    'attempted',
                    'successful'
                ) DEFAULT 'visit_only',
                `registered_restaurant_id` INT UNSIGNED NULL DEFAULT NULL,
                `visit_date` DATE AS (DATE(visit_recorded_at)) STORED,
                INDEX `idx_affiliate_user_id` (`affiliate_user_id`),
                INDEX `idx_registered_restaurant_id` (`registered_restaurant_id`),
                FOREIGN KEY (`affiliate_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`registered_restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "<p style='color:green;'>تم إنشاء جدول `restaurant_referral_visits` بنجاح.</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42S01' || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<p style='color:orange;'>جدول `restaurant_referral_visits` موجود بالفعل.</p>";
        } else {
            throw $e;
        }
    }

    echo "<b>اكتمل تحديث قاعدة البيانات.</b>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>فشل تحديث قاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</p>";
}
