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

    $sql = "
        SELECT td.*,
               t.ticket_number,
               c.name AS category_name,
               sc.name AS subcategory_name,
               co.name AS code_name,
               p.name AS platform_name,
               u.username AS edited_by_username
        FROM ticket_details td
        JOIN tickets t ON td.ticket_id = t.id
        LEFT JOIN ticket_categories c ON td.category_id = c.id
        LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id
        LEFT JOIN ticket_codes co ON td.code_id = co.id
        LEFT JOIN platforms p ON td.platform_id = p.id
        LEFT JOIN users u ON td.edited_by = u.id
        ORDER BY td.id DESC
        LIMIT 1
    ";

    $stmt = $pdo->query($sql);
    $lastTicket = $stmt->fetch();

    if ($lastTicket) {
        // تحويل created_at
        if (!empty($lastTicket['created_at'])) {
            $dt = new DateTime($lastTicket['created_at'], new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone('Africa/Cairo'));
            $lastTicket['created_at_cairo'] = $dt->format('Y-m-d h:i:s A');
        }

        // تحويل updated_at
        if (!empty($lastTicket['updated_at'])) {
            $dt2 = new DateTime($lastTicket['updated_at'], new DateTimeZone('UTC'));
            $dt2->setTimezone(new DateTimeZone('Africa/Cairo'));
            $lastTicket['updated_at_cairo'] = $dt2->format('Y-m-d h:i:s A');
        }

        echo "<pre>";
        print_r($lastTicket);
        echo "</pre>";
    } else {
        echo "لا توجد تذاكر في الجدول.";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
