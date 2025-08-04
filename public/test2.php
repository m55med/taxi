<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

    // البيانات الجديدة
    $userId = 9; // غيرها حسب المستخدم اللي عايز تضيفه
    $teamId = 2;

    // تنفيذ الإدخال
    $stmt = $pdo->prepare("INSERT INTO team_members (user_id, team_id) VALUES (:user_id, :team_id)");
    $stmt->execute([
        ':user_id' => $userId,
        ':team_id' => $teamId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'تمت إضافة العضو بنجاح.',
        'inserted_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
