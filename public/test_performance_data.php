<?php
// Test script to check performance data
require_once '../app/bootstrap.php';

echo "<h1>Performance Data Test</h1>";

// Test database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=cs_taxif;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test queries
$tests = [
    "Active Users" => "SELECT COUNT(*) as count FROM users WHERE status = 'active'",
    "Total Tickets (Last 30 days)" => "SELECT COUNT(*) as count FROM ticket_details WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
    "Total Reviews (Last 30 days)" => "SELECT COUNT(*) as count FROM reviews WHERE reviewable_type LIKE '%TicketDetail' AND DATE(reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
    "Ticket Code Points" => "SELECT COUNT(*) as count FROM ticket_code_points",
    "Teams" => "SELECT COUNT(*) as count FROM teams"
];

echo "<h2>Data Counts</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Test</th><th>Count</th><th>Status</th></tr>";

foreach ($tests as $name => $sql) {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        $status = $count > 0 ? "<span style='color: green;'>✓ Has Data</span>" : "<span style='color: orange;'>⚠ No Data</span>";
        echo "<tr><td>$name</td><td>$count</td><td>$status</td></tr>";
    } catch(Exception $e) {
        echo "<tr><td>$name</td><td>Error</td><td><span style='color: red;'>✗ " . $e->getMessage() . "</span></td></tr>";
    }
}
echo "</table>";

// Test specific performance queries
echo "<h2>Performance Queries Test</h2>";

$performanceTests = [
    "Top Performers Query" => "SELECT u.id as user_id, u.name, COALESCE(t.name, 'No Team') as team_name, COALESCE(SUM(tcp.points), 0) as points
        FROM users u
        LEFT JOIN teams t ON u.team_id = t.id
        LEFT JOIN ticket_details td ON td.edited_by = u.id AND DATE(td.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        LEFT JOIN ticket_code_points tcp ON tcp.code_id = td.code_id AND tcp.is_vip = td.is_vip
        WHERE u.status = 'active'
        GROUP BY u.id, u.name, t.name
        ORDER BY points DESC LIMIT 5",

    "Quality Metrics Query" => "SELECT AVG(r.rating) as avg_rating, COUNT(CASE WHEN r.rating >= 80 THEN 1 END) * 100.0 / COUNT(*) as excellent_percentage
        FROM reviews r
        WHERE r.reviewable_type LIKE '%TicketDetail' AND DATE(r.reviewed_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",

    "Growth Query" => "SELECT COUNT(CASE WHEN DATE(td.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as recent_tickets,
        COUNT(CASE WHEN DATE(td.created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as previous_tickets
        FROM ticket_details td"
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Query</th><th>Result</th><th>Status</th></tr>";

foreach ($performanceTests as $name => $sql) {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultJson = json_encode($result);
        $status = "<span style='color: green;'>✓ Executed</span>";
        echo "<tr><td>$name</td><td><pre style='max-width: 400px; overflow: auto;'>$resultJson</pre></td><td>$status</td></tr>";
    } catch(Exception $e) {
        echo "<tr><td>$name</td><td>Error</td><td><span style='color: red;'>✗ " . $e->getMessage() . "</span></td></tr>";
    }
}
echo "</table>";

echo "<h2>Sample Data</h2>";

// Show sample users
echo "<h3>Sample Users (first 5)</h3>";
$stmt = $db->prepare("SELECT id, name, status, team_id FROM users LIMIT 5");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($users, JSON_PRETTY_PRINT) . "</pre>";

// Show sample tickets
echo "<h3>Sample Tickets (last 5)</h3>";
$stmt = $db->prepare("SELECT td.id, td.ticket_id, td.edited_by, td.created_at FROM ticket_details td ORDER BY td.created_at DESC LIMIT 5");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($tickets, JSON_PRETTY_PRINT) . "</pre>";

// Show sample reviews
echo "<h3>Sample Reviews (last 5)</h3>";
$stmt = $db->prepare("SELECT r.id, r.rating, r.reviewed_at, r.reviewable_type FROM reviews r ORDER BY r.reviewed_at DESC LIMIT 5");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($reviews, JSON_PRETTY_PRINT) . "</pre>";

?>
