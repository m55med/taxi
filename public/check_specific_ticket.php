<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// تحميل DateTimeHelper
require_once __DIR__ . '/../app/helpers/DateTimeHelper.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 8079;

echo "<h1>فحص التذكرة رقم: {$ticket_id}</h1>";

$host = 'localhost';
$db   = 'taxif_cstaxi';
$user = 'taxif_root';
$pass = 'lcU*bQuQDEB0';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // جلب التذكرة
    $sql = "
        SELECT td.*, t.ticket_number, t.created_at as ticket_created_at
        FROM ticket_details td
        JOIN tickets t ON td.ticket_id = t.id
        WHERE td.id = :id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $ticket_id]);
    $ticket = $stmt->fetch();

    if ($ticket) {
        echo "<h2>البيانات الخام من قاعدة البيانات</h2>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        foreach ($ticket as $key => $value) {
            echo "<tr><td><b>{$key}</b></td><td>{$value}</td></tr>";
        }
        echo "</table>";

        echo "<h2>التحويل باستخدام DateTimeHelper</h2>";
        $cairoTime = DateTimeHelper::formatForDisplay($ticket['created_at'], 'Y-m-d h:i:s A');
        echo "created_at (Cairo): <strong>{$cairoTime}</strong><br>";

        $ticketCairoTime = DateTimeHelper::formatForDisplay($ticket['ticket_created_at'], 'Y-m-d h:i:s A');
        echo "ticket_created_at (Cairo): <strong>{$ticketCairoTime}</strong><br>";

        echo "<h2>التحليل</h2>";
        echo "<p>إذا كان الوقت في القاهرة ≈ 10:18 م عند حفظ التذكرة، فيجب أن يكون UTC = 19:18<br>";
        echo "البيانات المحفوظة حالياً: <code>{$ticket['created_at']}</code><br>";

        $savedTime = strtotime($ticket['created_at']);
        $expectedTime = strtotime('2025-09-16 19:18:43');
        $diff = $expectedTime - $savedTime;

        echo "الفرق بالثواني: {$diff} (يجب أن يكون 0)<br>";
        echo "الفرق بالساعات: " . ($diff / 3600) . "<br>";

        if ($diff == 0) {
            echo "<span style='color: green; font-weight: bold;'>✅ التوقيت صحيح!</span><br>";
        } else {
            echo "<span style='color: red; font-weight: bold;'>❌ التوقيت خاطئ - يحتاج إصلاح</span><br>";
            echo "الحل: إضافة " . ($diff / 3600) . " ساعة للبيانات<br>";
        }

    } else {
        echo "<span style='color: red;'>لم يتم العثور على التذكرة رقم {$ticket_id}</span>";
    }

} catch (PDOException $e) {
    echo "<span style='color: red;'>خطأ في قاعدة البيانات: " . $e->getMessage() . "</span>";
}

echo "<hr>";
echo "<p><a href='?id=8079'>فحص التذكرة 8079</a> | <a href='?id=14905'>فحص التذكرة 14905</a></p>";
?>
