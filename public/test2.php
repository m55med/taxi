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

    $user_id = 28;
    $today   = date('Y-m-d');

    echo "============================ 1️⃣ النتائج الصحيحة (edited_by فقط) ============================\n";

    // ✅ الاستعلام الصحيح (16 صف)
    $sql_correct = "
        SELECT 
            td.id AS ticket_detail_id,
            td.ticket_id,
            t.ticket_number,
            td.phone,
            td.is_vip,
            td.created_at
        FROM ticket_details td
        JOIN tickets t ON t.id = td.ticket_id
        WHERE td.edited_by = :user_id
          AND DATE(td.created_at) = :today
        ORDER BY td.created_at ASC
    ";
    $stmt = $pdo->prepare($sql_correct);
    $stmt->execute([
        ':user_id' => $user_id,
        ':today'   => $today
    ]);
    $correct_rows = $stmt->fetchAll();

    echo "عدد الصفوف الصحيحة: " . count($correct_rows) . "\n";
    foreach ($correct_rows as $row) {
        echo "TicketDetail ID: {$row['ticket_detail_id']}, Ticket ID: {$row['ticket_id']}, Ticket Number: {$row['ticket_number']}, Created At: {$row['created_at']}, Phone: {$row['phone']}, VIP: {$row['is_vip']}\n";
    }

    echo "\n\n============================ 2️⃣ النتائج كما في JSON (نشاط المستخدم) ============================\n";

    // ⚠️ الاستعلام المستخدم في JSON (22 صف)
    $sql_json = "
        SELECT 
            td.id AS ticket_detail_id,
            td.ticket_id,
            t.ticket_number,
            td.phone,
            td.is_vip,
            td.created_at,
            t.created_by,
            td.edited_by,
            td.assigned_team_leader_id
        FROM ticket_details td
        JOIN tickets t ON t.id = td.ticket_id
        WHERE DATE(td.created_at) = :today
          AND (
               t.created_by = :user_id_created
            OR td.edited_by = :user_id_edited
            OR td.assigned_team_leader_id = :user_id_assigned
          )
        ORDER BY td.created_at ASC
    ";
    $stmt = $pdo->prepare($sql_json);
    $stmt->execute([
        ':today'               => $today,
        ':user_id_created'     => $user_id,
        ':user_id_edited'      => $user_id,
        ':user_id_assigned'    => $user_id,
    ]);
    $json_rows = $stmt->fetchAll();

    echo "عدد الصفوف (حسب JSON): " . count($json_rows) . "\n";
    foreach ($json_rows as $row) {
        echo "TicketDetail ID: {$row['ticket_detail_id']}, Ticket ID: {$row['ticket_id']}, Ticket Number: {$row['ticket_number']}, Created At: {$row['created_at']}, Phone: {$row['phone']}, VIP: {$row['is_vip']}, created_by: {$row['created_by']}, edited_by: {$row['edited_by']}, assigned_team_leader_id: {$row['assigned_team_leader_id']}\n";
    }

    echo "\n\n============================ 3️⃣ الفرق بين المجموعتين ============================\n";

    // عملنا مصفوفة IDs عشان نقارن
    $correct_ids = array_column($correct_rows, 'ticket_detail_id');
    $json_ids    = array_column($json_rows, 'ticket_detail_id');

    $extra_ids = array_diff($json_ids, $correct_ids);

    echo "عدد الصفوف الزائدة في JSON: " . count($extra_ids) . "\n";
    foreach ($json_rows as $row) {
        if (in_array($row['ticket_detail_id'], $extra_ids)) {
            echo "⚠️ زيادة => TicketDetail ID: {$row['ticket_detail_id']}, Ticket ID: {$row['ticket_id']}, Ticket Number: {$row['ticket_number']}, Created At: {$row['created_at']}, Phone: {$row['phone']}\n";
        }
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
