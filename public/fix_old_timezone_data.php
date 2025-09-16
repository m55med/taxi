<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// تحميل DateTimeHelper
require_once __DIR__ . '/../app/helpers/DateTimeHelper.php';

echo "<h1>إصلاح بيانات التوقيت القديمة</h1>";

echo "<h2>تحليل المشكلة</h2>";
echo "<p>التذكرة الحالية:</p>";
echo "<ul>";
echo "<li>تم حفظها عندما كان الوقت في القاهرة ≈ 10:18 م</li>";
echo "<li>البيانات المحفوظة: <code>2025-09-16 15:18:43</code> (UTC)</li>";
echo "<li>المتوقع: <code>2025-09-16 19:18:00</code> (UTC) إذا كان الوقت 10:18 م في القاهرة</li>";
echo "<li>الفرق: 4 ساعات (يجب إضافة 4 ساعات للبيانات القديمة)</li>";
echo "</ul>";

echo "<h2>الفرق المحسوب</h2>";
$oldTime = '2025-09-16 15:18:43';
$expectedTime = '2025-09-16 19:18:43'; // 10:18 م في القاهرة = 19:18 UTC

$oldTimestamp = strtotime($oldTime);
$expectedTimestamp = strtotime($expectedTime);
$diffHours = ($expectedTimestamp - $oldTimestamp) / 3600;

echo "الوقت القديم: {$oldTime}<br>";
echo "الوقت المتوقع: {$expectedTime}<br>";
echo "الفرق بالساعات: {$diffHours} ساعة<br>";
echo "<span style='color: red; font-weight: bold;'>الخلاصة: البيانات القديمة محفوظة بتوقيت خاطئ بمقدار {$diffHours} ساعة</span><br>";

echo "<hr>";

echo "<h2>سكريبت الإصلاح</h2>";
echo "<p>سكريبت لإصلاح البيانات القديمة:</p>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
// SQL لإصلاح البيانات
$sql_fix = "
-- إضافة 4 ساعات للبيانات القديمة
UPDATE tickets
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

UPDATE ticket_details
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';
";

echo htmlspecialchars($sql_fix);
echo "</pre>";

echo "<h2>التحقق من الإصلاح</h2>";
echo "<p>بعد تطبيق الإصلاح:</p>";
echo "<ul>";
echo "<li>البيانات المحفوظة: <code>2025-09-16 15:18:43</code> → <code>2025-09-16 19:18:43</code></li>";
echo "<li>العرض في القاهرة: <code>2025-09-16 19:18:43</code> UTC → <code>2025-09-16 10:18:43 PM</code></li>";
echo "<li>هذا يطابق الوقت الفعلي الذي تم فيه حفظ التذكرة</li>";
echo "</ul>";

echo "<hr>";

echo "<h2>خطوات التنفيذ</h2>";
echo "<ol>";
echo "<li><strong>نسخ احتياطي</strong> من قاعدة البيانات قبل التنفيذ</li>";
echo "<li><strong>تشغيل الاستعلامات</strong> أعلاه على قاعدة البيانات</li>";
echo "<li><strong>التحقق</strong> من صحة البيانات بعد الإصلاح</li>";
echo "<li><strong>اختبار النظام</strong> للتأكد من عدم وجود مشاكل</li>";
echo "</ol>";

echo "<hr>";

echo "<h2>تنفيذ الإصلاح (MySQL)</h2>";
echo "<p>يمكنك تشغيل هذا الاستعلام مباشرة في phpMyAdmin أو MySQL:</p>";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>⚠️ تحذير مهم:</strong> قم بعمل نسخة احتياطية من قاعدة البيانات قبل تنفيذ هذا الاستعلام!";
echo "</div>";

echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;'>";
// الاستعلام النهائي
$final_sql = "
-- إصلاح بيانات التذاكر القديمة
UPDATE tickets
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

UPDATE ticket_details
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جداول أخرى إذا لزم الأمر
UPDATE breaks
SET start_time = DATE_ADD(start_time, INTERVAL 4 HOUR),
    end_time = DATE_ADD(end_time, INTERVAL 4 HOUR)
WHERE start_time < '2025-09-17 00:00:00';

UPDATE driver_calls
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- وهكذا للجداول الأخرى...
";

echo htmlspecialchars($final_sql);
echo "</pre>";

echo "<hr>";

echo "<h2>التحقق بعد الإصلاح</h2>";
echo "<p>بعد تنفيذ الإصلاح، قم بتشغيل:</p>";
echo "<pre style='background: #d1ecf1; padding: 10px; border-radius: 5px;'>";
echo "SELECT id, ticket_number, created_at FROM tickets WHERE id = 8079;\n";
echo "SELECT id, created_at, updated_at FROM ticket_details WHERE ticket_id = 8079;";
echo "</pre>";

echo "<p>يجب أن تظهر النتائج:</p>";
echo "<ul>";
echo "<li><code>created_at: 2025-09-16 19:18:43</code></li>";
echo "<li>العرض في tickets.php: <code>2025-09-16 10:18:43 PM</code></li>";
echo "</ul>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ الحل الكامل</h3>";
echo "<p>بهذا الإصلاح:</p>";
echo "<ul>";
echo "<li>البيانات القديمة ستعرض التوقيت الصحيح</li>";
echo "<li>البيانات الجديدة ستحفظ وعرض بتوقيت صحيح</li>";
echo "<li>النظام سيعمل بدون مشاكل مستقبلية</li>";
echo "</ul>";
echo "</div>";

?>
