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



    // تعيين المنطقة الزمنية UTC

    $pdo->exec("SET @@session.time_zone = '+00:00'");



    // ========================================

    // جميع استعلامات تعديل الجداول

    // ========================================

    $queries = [



        // 1. جدول roles

        "ALTER TABLE roles 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER name,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 2. جدول users

        "ALTER TABLE users 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",

        "ALTER TABLE users 

         ADD INDEX IF NOT EXISTS idx_status (status),

         ADD INDEX IF NOT EXISTS idx_role_id (role_id),

         ADD INDEX IF NOT EXISTS idx_is_online (is_online)",



        // 3. جدول permissions

        "ALTER TABLE permissions 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER description,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 4. جدول user_permissions

        "ALTER TABLE user_permissions 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",



        // 5. جدول role_permissions

        "ALTER TABLE role_permissions 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",

        "ALTER TABLE role_permissions 

         ADD CONSTRAINT IF NOT EXISTS unique_role_permission UNIQUE (role_id, permission_id)",



        // 6. جدول telegram_links

        "ALTER TABLE telegram_links 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",

        "ALTER TABLE telegram_links 

         ADD CONSTRAINT IF NOT EXISTS unique_telegram_user UNIQUE (telegram_user_id)",



        // 7. جدول car_types

        "ALTER TABLE car_types 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER name,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 8. جدول countries

        "ALTER TABLE countries 

         ADD COLUMN IF NOT EXISTS code VARCHAR(3) DEFAULT NULL AFTER name,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER code,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 9. جدول teams

        "ALTER TABLE teams 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER team_leader_id,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 10. جدول team_members

        "ALTER TABLE team_members 

         MODIFY COLUMN joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER joined_at,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",

        "ALTER TABLE team_members 

         ADD CONSTRAINT IF NOT EXISTS unique_user_team UNIQUE (user_id, team_id)",



        // 11. جدول drivers

        "ALTER TABLE drivers 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",

        "ALTER TABLE drivers 

         ADD INDEX IF NOT EXISTS idx_phone (phone),

         ADD INDEX IF NOT EXISTS idx_status_hold (main_system_status, hold),

         ADD INDEX IF NOT EXISTS idx_hold_by (hold_by),

         ADD INDEX IF NOT EXISTS idx_country_id (country_id)",



        // 12. جدول driver_calls

        "ALTER TABLE driver_calls 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",

        "ALTER TABLE driver_calls 

         ADD INDEX IF NOT EXISTS idx_driver_id (driver_id),

         ADD INDEX IF NOT EXISTS idx_call_by (call_by),

         ADD INDEX IF NOT EXISTS idx_call_status (call_status)",



        // 13. جدول driver_assignments

        "ALTER TABLE driver_assignments 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",

        "ALTER TABLE driver_assignments 

         ADD INDEX IF NOT EXISTS idx_to_user_id (to_user_id),

         ADD INDEX IF NOT EXISTS idx_is_seen (is_seen)",



        // 14. جدول document_types

        "ALTER TABLE document_types 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER is_required,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 15. جدول driver_documents_required

        "ALTER TABLE driver_documents_required 

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER updated_by",



        // 16. جدول platforms

        "ALTER TABLE platforms 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER name,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 17. جدول incoming_calls

        "ALTER TABLE incoming_calls 

         MODIFY COLUMN call_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER team_id_at_action,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 18. جدول ticket_categories

        "ALTER TABLE ticket_categories 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER name,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 19. جدول ticket_subcategories

        "ALTER TABLE ticket_subcategories 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER name,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 20. جدول ticket_codes

        "ALTER TABLE ticket_codes 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER name,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 21. جدول tickets

        "ALTER TABLE tickets 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 22. جدول ticket_details

        "ALTER TABLE ticket_details 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 23. جدول reviews

        "ALTER TABLE reviews 

         MODIFY COLUMN reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER reviewed_at,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 24. جدول discussions

        "ALTER TABLE discussions 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",



        // 25. جدول discussion_replies

        "ALTER TABLE discussion_replies 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 26. جدول coupons

        "ALTER TABLE coupons 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN held_at DATETIME NULL DEFAULT NULL,

         MODIFY COLUMN used_at DATETIME NULL DEFAULT NULL,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 27. جدول ticket_coupons

        "ALTER TABLE ticket_coupons 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 28. جدول ticket_vip_assignments

        "ALTER TABLE ticket_vip_assignments 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 29. جدول referral_visits

        "ALTER TABLE referral_visits 

         MODIFY COLUMN visit_recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN registration_attempted_at DATETIME NULL DEFAULT NULL,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER visit_date,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 30. جدول driver_snoozes

        "ALTER TABLE driver_snoozes 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN snoozed_until DATETIME NOT NULL,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 31. جدول agents

        "ALTER TABLE agents 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER map_url,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 32. جدول working_hours

        "ALTER TABLE working_hours 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER is_closed,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 33. جدول notifications

        "ALTER TABLE notifications 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",



        // 34. جدول user_notifications

        "ALTER TABLE user_notifications 

         MODIFY COLUMN read_at DATETIME NULL DEFAULT NULL,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER read_at,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 35. جدول ticket_code_points

        "ALTER TABLE ticket_code_points 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER valid_to,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 36. جدول call_points

        "ALTER TABLE call_points 

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER valid_to,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 37. جدول user_monthly_bonus

        "ALTER TABLE user_monthly_bonus 

         MODIFY COLUMN granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER granted_at,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 38. جدول bonus_settings

        "ALTER TABLE bonus_settings 

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP BEFORE updated_at",



        // 39. جدول knowledge_base

        "ALTER TABLE knowledge_base 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",



        // 40. جدول user_discussion_read_status

        "ALTER TABLE user_discussion_read_status 

         MODIFY COLUMN last_read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER last_read_at,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 41. جدول password_resets

        "ALTER TABLE password_resets 

         MODIFY COLUMN expires_at DATETIME NOT NULL,

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 42. جدول delegation_types

        "ALTER TABLE delegation_types 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 43. جدول user_delegations

        "ALTER TABLE user_delegations 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 44. جدول employee_evaluations

        "ALTER TABLE employee_evaluations 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 45. جدول restaurants

        "ALTER TABLE restaurants 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",



        // 46. جدول restaurant_referral_visits

        "ALTER TABLE restaurant_referral_visits 

         MODIFY COLUMN visit_recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER visit_date,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 47. جدول establishments

        "ALTER TABLE establishments 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",



        // 48. جدول breaks

        "ALTER TABLE breaks 

         MODIFY COLUMN start_time DATETIME NOT NULL,

         MODIFY COLUMN end_time DATETIME DEFAULT NULL,

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",



        // 49. جدول knowledge_base_folders

        "ALTER TABLE knowledge_base_folders 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",



        // 50. جدول ticket_edit_logs

        "ALTER TABLE ticket_edit_logs 

         MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

         ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",



        // فهارس إضافية

        "ALTER TABLE drivers ADD INDEX IF NOT EXISTS idx_created_at (created_at)",

        "ALTER TABLE drivers ADD INDEX IF NOT EXISTS idx_updated_at (updated_at)",

        "ALTER TABLE tickets ADD INDEX IF NOT EXISTS idx_created_at (created_at)",

        "ALTER TABLE ticket_details ADD INDEX IF NOT EXISTS idx_created_at (created_at)",

        "ALTER TABLE driver_calls ADD INDEX IF NOT EXISTS idx_created_at (created_at)",

        "ALTER TABLE incoming_calls ADD INDEX IF NOT EXISTS idx_call_started_at (call_started_at)",

        "ALTER TABLE reviews ADD INDEX IF NOT EXISTS idx_reviewed_at (reviewed_at)",

        "ALTER TABLE coupons ADD INDEX IF NOT EXISTS idx_created_at (created_at)",

        "ALTER TABLE referral_visits ADD INDEX IF NOT EXISTS idx_visit_recorded_at (visit_recorded_at)",



        // تحسين الجداول

        "OPTIMIZE TABLE users",

        "OPTIMIZE TABLE drivers",

        "OPTIMIZE TABLE tickets",

        "OPTIMIZE TABLE ticket_details",

        "OPTIMIZE TABLE driver_calls",

        "OPTIMIZE TABLE incoming_calls",



        // تحديث الإحصائيات

        "ANALYZE TABLE users",

        "ANALYZE TABLE drivers",

        "ANALYZE TABLE tickets",

        "ANALYZE TABLE ticket_details"

    ];



    foreach ($queries as $sql) {

        $pdo->exec($sql);

    }



    echo "تم تطبيق جميع التعديلات بنجاح ✅";



} catch (PDOException $e) {

    echo "حدث خطأ: " . $e->getMessage();

}

