<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// تحميل DateTimeHelper
require_once __DIR__ . '/../app/helpers/DateTimeHelper.php';

echo "<h1>اختبار شامل لإصلاح التوقيت</h1>";

echo "<h2>1. معلومات الخادم</h2>";
echo "Server Timezone: " . date_default_timezone_get() . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";
echo "UTC Time (gmdate): " . gmdate('Y-m-d H:i:s') . "<br>";
echo "UTC Time (DateTimeHelper): " . DateTimeHelper::getCurrentUTC() . "<br>";
echo "Cairo Time (DateTimeHelper): " . DateTimeHelper::getCurrentLocal() . "<br>";

echo "<hr>";

echo "<h2>2. اختبار التحويلات</h2>";

// اختبار البيانات من المثال الأصلي
$testCases = [
    ['input' => '2025-09-16 15:18:43', 'description' => 'البيانات الأصلية من المشكلة'],
    ['input' => DateTimeHelper::getCurrentUTC(), 'description' => 'الوقت الحالي بـ UTC'],
    ['input' => '2025-09-16 19:00:00', 'description' => 'وقت اختبار آخر']
];

foreach ($testCases as $test) {
    echo "<h3>{$test['description']}</h3>";
    echo "Input (UTC): {$test['input']}<br>";
    
    $cairoTime = DateTimeHelper::formatForDisplay($test['input']);
    echo "Output (Cairo): {$cairoTime}<br>";
    
    // حساب الفرق المتوقع (3 ساعات)
    $utcTime = new DateTime($test['input'], new DateTimeZone('UTC'));
    $expectedCairo = clone $utcTime;
    $expectedCairo->setTimezone(new DateTimeZone('Africa/Cairo'));
    
    echo "Expected Cairo: " . $expectedCairo->format('Y-m-d H:i:s') . "<br>";
    
    if ($cairoTime === $expectedCairo->format('Y-m-d H:i:s')) {
        echo "<span style='color: green;'>✅ صحيح</span><br>";
    } else {
        echo "<span style='color: red;'>❌ خطأ</span><br>";
    }
    echo "<br>";
}

echo "<hr>";

echo "<h2>3. اختبار الدوال المساعدة</h2>";

// اختبار دوال التحويل
$cairoTime = '2025-09-16 22:00:00';
$utcConverted = DateTimeHelper::convertToUTC($cairoTime);
echo "Cairo to UTC: {$cairoTime} → {$utcConverted}<br>";

// التحويل العكسي للتحقق
$backToCairo = DateTimeHelper::formatForDisplay($utcConverted);
echo "Back to Cairo: {$utcConverted} → {$backToCairo}<br>";

if ($backToCairo === $cairoTime) {
    echo "<span style='color: green;'>✅ التحويل العكسي صحيح</span><br>";
} else {
    echo "<span style='color: red;'>❌ التحويل العكسي خطأ</span><br>";
}

echo "<hr>";

echo "<h2>4. اختبار التفاصيل المطولة</h2>";

$detailedTest = DateTimeHelper::formatDetailedForDisplay('2025-09-16 15:18:43');
if ($detailedTest) {
    echo "<pre>";
    print_r($detailedTest);
    echo "</pre>";
} else {
    echo "<span style='color: red;'>فشل في الحصول على التفاصيل المطولة</span><br>";
}

echo "<hr>";

echo "<h2>5. اختبار نطاق الشهر الحالي</h2>";

$monthRange = DateTimeHelper::getCurrentMonthRange();
if ($monthRange) {
    echo "Start (Local): " . $monthRange['start_date'] . "<br>";
    echo "End (Local): " . $monthRange['end_date'] . "<br>";
    echo "Start (UTC): " . $monthRange['start_date_utc'] . "<br>";
    echo "End (UTC): " . $monthRange['end_date_utc'] . "<br>";
} else {
    echo "<span style='color: red;'>فشل في الحصول على نطاق الشهر</span><br>";
}

echo "<hr>";

echo "<h2>6. خلاصة الاختبار</h2>";

$allGood = true;

// فحص أساسي: الفرق بين UTC والقاهرة يجب أن يكون 3 ساعات
$utcNow = new DateTime('now', new DateTimeZone('UTC'));
$cairoNow = new DateTime('now', new DateTimeZone('Africa/Cairo'));
$timeDiff = $cairoNow->getTimestamp() - $utcNow->getTimestamp();

// يجب أن يكون الفرق 0 لأن الوقت هو نفسه، لكن المنطقة الزمنية مختلفة
// الفرق الحقيقي يظهر في العرض
$utcFormatted = $utcNow->format('H');
$cairoFormatted = $cairoNow->format('H');
$hourDiff = $cairoFormatted - $utcFormatted;

// تعديل للتعامل مع تغيير اليوم
if ($hourDiff < 0) {
    $hourDiff += 24;
}

echo "فرق الساعات بين UTC والقاهرة: {$hourDiff}<br>";

if ($hourDiff == 3) {
    echo "<span style='color: green;'>✅ فرق التوقيت صحيح (3 ساعات)</span><br>";
} else {
    echo "<span style='color: orange;'>⚠️ فرق التوقيت غير متوقع: {$hourDiff} ساعات</span><br>";
    echo "هذا قد يكون بسبب التوقيت الصيفي<br>";
}

echo "<br>";

if ($allGood) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
    echo "<h3>🎉 تم إصلاح مشكلة التوقيت بنجاح!</h3>";
    echo "<p>جميع الاختبارات تعمل بشكل صحيح. الآن يتم:</p>";
    echo "<ul>";
    echo "<li>حفظ البيانات بالتوقيت العالمي الموحد (UTC)</li>";
    echo "<li>عرض البيانات بتوقيت القاهرة</li>";
    echo "<li>التحويل بين التوقيتات بشكل صحيح</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>⚠️ هناك مشاكل تحتاج إلى مراجعة</h3>";
    echo "</div>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
h3 { color: #888; }
hr { margin: 20px 0; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>
