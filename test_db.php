<?php
// تحقق من البيانات في قاعدة البيانات مباشرة
require_once 'app/Core/Database.php';
require_once 'app/Core/Model.php';

// Load environment if needed
if (file_exists('.env')) {
    $dotenv = parse_ini_file('.env');
    foreach ($dotenv as $key => $value) {
        $_ENV[$key] = $value;
    }
}

try {
    // استخدام نفس طريقة الاتصال المستخدمة في النظام
    $model = new App\Core\Model();
    $db = $model->getDb();
    
    echo "<h2>🔍 Database Connection Test</h2>";
    
    // 1. فحص عدد التذاكر الإجمالي
    $stmt1 = $db->query("SELECT COUNT(*) as total FROM tickets");
    $totalTickets = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📊 Total tickets in database: <strong>$totalTickets</strong></p>";
    
    // 2. فحص عدد تفاصيل التذاكر
    $stmt2 = $db->query("SELECT COUNT(*) as total FROM ticket_details");
    $totalDetails = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📋 Total ticket_details in database: <strong>$totalDetails</strong></p>";
    
    // 3. فحص أول 5 تذاكر
    $stmt3 = $db->query("
        SELECT t.id, t.ticket_number, td.phone, t.created_by 
        FROM tickets t 
        JOIN ticket_details td ON t.id = td.ticket_id 
        LIMIT 5
    ");
    $sampleTickets = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>🎫 Sample Tickets (first 5):</h3>";
    if (empty($sampleTickets)) {
        echo "<p style='color: red;'>❌ No tickets found with JOIN</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Ticket Number</th><th>Phone</th><th>Created By</th></tr>";
        foreach ($sampleTickets as $ticket) {
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td>{$ticket['phone']}</td>";
            echo "<td>{$ticket['created_by']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. فحص مع LEFT JOIN
    $stmt4 = $db->query("
        SELECT t.id, t.ticket_number, td.phone, t.created_by, creator.username 
        FROM tickets t 
        JOIN ticket_details td ON t.id = td.ticket_id 
        LEFT JOIN users creator ON t.created_by = creator.id 
        LIMIT 5
    ");
    $sampleTicketsLeft = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>🔗 Sample Tickets with LEFT JOIN (first 5):</h3>";
    if (empty($sampleTicketsLeft)) {
        echo "<p style='color: red;'>❌ No tickets found with LEFT JOIN</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Ticket Number</th><th>Phone</th><th>Created By</th><th>Username</th></tr>";
        foreach ($sampleTicketsLeft as $ticket) {
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td>{$ticket['phone']}</td>";
            echo "<td>{$ticket['created_by']}</td>";
            echo "<td>" . ($ticket['username'] ? $ticket['username'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. فحص البحث مباشرة
    $searchTerm = '875645090';
    $stmt5 = $db->prepare("
        SELECT t.id, t.ticket_number, td.phone, creator.username 
        FROM tickets t 
        JOIN ticket_details td ON t.id = td.ticket_id 
        LEFT JOIN users creator ON t.created_by = creator.id 
        WHERE t.ticket_number LIKE ? OR td.phone LIKE ? OR COALESCE(creator.username, '') LIKE ?
    ");
    $searchPattern = "%$searchTerm%";
    $stmt5->execute([$searchPattern, $searchPattern, $searchPattern]);
    $searchResults = $stmt5->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>🔍 Search Test for '$searchTerm':</h3>";
    if (empty($searchResults)) {
        echo "<p style='color: red;'>❌ No results found for search term</p>";
        
        // فحص إضافي - البحث في كل الجداول
        echo "<h4>🔍 Extended Search Test:</h4>";
        
        // البحث في ticket_number فقط
        $stmt6 = $db->prepare("SELECT ticket_number FROM tickets WHERE ticket_number LIKE ?");
        $stmt6->execute([$searchPattern]);
        $ticketResults = $stmt6->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>📋 Tickets matching '$searchTerm': " . count($ticketResults) . "</p>";
        foreach ($ticketResults as $result) {
            echo "<p>- {$result['ticket_number']}</p>";
        }
        
        // البحث في phone فقط
        $stmt7 = $db->prepare("SELECT phone FROM ticket_details WHERE phone LIKE ?");
        $stmt7->execute([$searchPattern]);
        $phoneResults = $stmt7->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>📞 Phones matching '$searchTerm': " . count($phoneResults) . "</p>";
        foreach ($phoneResults as $result) {
            echo "<p>- {$result['phone']}</p>";
        }
        
        // البحث في username فقط
        $stmt8 = $db->prepare("SELECT username FROM users WHERE username LIKE ?");
        $stmt8->execute([$searchPattern]);
        $userResults = $stmt8->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>👤 Usernames matching '$searchTerm': " . count($userResults) . "</p>";
        foreach ($userResults as $result) {
            echo "<p>- {$result['username']}</p>";
        }
        
    } else {
        echo "<p style='color: green;'>✅ Found " . count($searchResults) . " results</p>";
        foreach ($searchResults as $result) {
            echo "<p>- {$result['ticket_number']} | {$result['phone']} | {$result['username']}</p>";
        }
    }
    
    // 6. فحص الاستعلام الكامل من getFilteredTickets
    echo "<h3>🔧 Full Query Test (same as getFilteredTickets):</h3>";
    $fullQuery = "
        SELECT 
            t.id as ticket_id,
            t.ticket_number,
            t.created_by,
            td.phone,
            td.is_vip,
            td.notes,
            td.created_at,
            td.edited_by,
            creator.username as created_by_username,
            editor.username as edited_by_username,
            p.name as platform_name,
            c.name as country_name,
            cat.name as category_name,
            sub.name as subcategory_name,
            code.name as code_name,
            -- Reviews data
            AVG(r.rating) as avg_review_rating,
            COUNT(r.id) as review_count,
            GROUP_CONCAT(DISTINCT CONCAT(reviewer.username, ':', r.rating) SEPARATOR '|') as reviews_details,
            -- Team data
            tm.name as team_name,
            -- VIP assignment
            vip_marketer.name as vip_marketer_name
        FROM tickets t
        JOIN ticket_details td ON t.id = td.ticket_id
        LEFT JOIN users creator ON t.created_by = creator.id
        LEFT JOIN users editor ON td.edited_by = editor.id
        LEFT JOIN platforms p ON td.platform_id = p.id
        LEFT JOIN countries c ON td.country_id = c.id
        LEFT JOIN ticket_categories cat ON td.category_id = cat.id
        LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
        LEFT JOIN ticket_codes code ON td.code_id = code.id
        -- Reviews join
        LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
        LEFT JOIN users reviewer ON r.reviewed_by = reviewer.id
        -- Team join
        LEFT JOIN team_members team_mem ON td.edited_by = team_mem.user_id
        LEFT JOIN teams tm ON team_mem.team_id = tm.id
        -- VIP assignment join
        LEFT JOIN ticket_vip_assignments tva ON td.id = tva.ticket_detail_id
        LEFT JOIN users vip_marketer ON tva.marketer_id = vip_marketer.id
        WHERE td.id = (SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id)
        GROUP BY t.id, td.id
        ORDER BY td.created_at DESC
        LIMIT 5
    ";
    
    $stmt9 = $db->query($fullQuery);
    $fullResults = $stmt9->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fullResults)) {
        echo "<p style='color: red;'>❌ No results from full query</p>";
        
        // فحص مبسط بدون GROUP BY
        echo "<h4>🔧 Simplified Query Test (without GROUP BY):</h4>";
        $simpleQuery = "
            SELECT t.id, t.ticket_number, td.phone, td.created_at
            FROM tickets t
            JOIN ticket_details td ON t.id = td.ticket_id
            ORDER BY td.created_at DESC
            LIMIT 5
        ";
        
        $stmt10 = $db->query($simpleQuery);
        $simpleResults = $stmt10->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($simpleResults)) {
            echo "<p style='color: red;'>❌ No results even with simplified query</p>";
        } else {
            echo "<p style='color: green;'>✅ Found " . count($simpleResults) . " results with simplified query</p>";
            foreach ($simpleResults as $result) {
                echo "<p>- {$result['ticket_number']} | {$result['phone']} | Created: {$result['created_at']}</p>";
            }
        }
        
    } else {
        echo "<p style='color: green;'>✅ Found " . count($fullResults) . " results from full query</p>";
        foreach ($fullResults as $result) {
            echo "<p>- {$result['ticket_number']} | {$result['phone']} | Created: {$result['created_at']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
