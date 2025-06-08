<?php
// بدء تخزين المخرجات
ob_start();

// ضبط الأخطاء (اختياري أثناء التطوير)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// تأكد من عدم وجود أي مخرجات قبل بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحميل ملف التكوين
require_once '../app/config/config.php';

// تحميل الملفات الأساسية
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Model.php';
require_once '../app/models/User.php';
require_once '../app/models/Driver.php';
require_once '../app/models/Call.php';
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/DashboardController.php';
require_once '../app/controllers/UploadController.php';
require_once '../app/controllers/CallController.php';
require_once '../app/controllers/DriverController.php';

// تشغيل التطبيق
$app = new App();

// إنهاء وإرسال المخرجات المخزنة
ob_end_flush();

