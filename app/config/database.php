<?php
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=taxi;charset=utf8mb4",
        "root",  // اسم المستخدم الافتراضي لـ XAMPP
        ""       // كلمة المرور فارغة في XAMPP
    );
    
    // إعداد PDO للتعامل مع الأخطاء
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $db;
} catch (PDOException $e) {
    // تسجيل الخطأ وإظهار رسالة مناسبة للمستخدم
    error_log("Database Connection Error: " . $e->getMessage());
    die("عذراً، حدث خطأ في الاتصال بقاعدة البيانات");
} 