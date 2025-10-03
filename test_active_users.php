<?php
// Test script to check active users query
require_once 'app/Core/Database.php';

try {
    // Initialize database connection
    $db = new App\Core\Database();

    echo "=== Testing Active Users Queries ===\n\n";

    // Test 1: Main query
    echo "1. Main Active Users Query:\n";
    $query1 = "SELECT COUNT(*) as active_users FROM users
               WHERE status = 'active'
               AND (
                   is_online = 1
                   OR (last_activity IS NOT NULL AND last_activity > DATE_SUB(NOW(), INTERVAL 2 HOUR))
               )";

    $stmt1 = $db->prepare($query1);
    $stmt1->execute();
    $result1 = $stmt1->fetch(\PDO::FETCH_ASSOC);
    echo "Result: " . ($result1['active_users'] ?? 0) . " users\n";
    echo "Query: $query1\n\n";

    // Test 2: Check total users
    echo "2. Total Users Count:\n";
    $query2 = "SELECT COUNT(*) as total FROM users";
    $stmt2 = $db->query($query2);
    $result2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
    echo "Total users: " . ($result2['total'] ?? 0) . "\n\n";

    // Test 3: Check active users by status
    echo "3. Active Status Users:\n";
    $query3 = "SELECT COUNT(*) as active_status FROM users WHERE status = 'active'";
    $stmt3 = $db->query($query3);
    $result3 = $stmt3->fetch(\PDO::FETCH_ASSOC);
    echo "Active status users: " . ($result3['active_status'] ?? 0) . "\n\n";

    // Test 4: Check online users
    echo "4. Online Users:\n";
    $query4 = "SELECT COUNT(*) as online FROM users WHERE is_online = 1";
    $stmt4 = $db->query($query4);
    $result4 = $stmt4->fetch(\PDO::FETCH_ASSOC);
    echo "Online users: " . ($result4['online'] ?? 0) . "\n\n";

    // Test 5: Check recent activity
    echo "5. Recent Activity (2 hours):\n";
    $query5 = "SELECT COUNT(*) as recent FROM users
               WHERE last_activity IS NOT NULL
               AND last_activity > DATE_SUB(NOW(), INTERVAL 2 HOUR)";
    $stmt5 = $db->query($query5);
    $result5 = $stmt5->fetch(\PDO::FETCH_ASSOC);
    echo "Recent activity users: " . ($result5['recent'] ?? 0) . "\n\n";

    // Test 6: Sample users data
    echo "6. Sample Users Data:\n";
    $query6 = "SELECT id, username, status, is_online, last_activity, created_at
               FROM users LIMIT 5";
    $stmt6 = $db->query($query6);
    $users = $stmt6->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        echo "User: {$user['username']} | Status: {$user['status']} | Online: {$user['is_online']} | Last Activity: {$user['last_activity']} | Created: {$user['created_at']}\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
