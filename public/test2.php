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

    // Lightweight AJAX: lookup team leader by user_id
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'lookup_tl') {
        header('Content-Type: application/json');
        $lookupUserId = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        if ($lookupUserId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user_id']);
            exit;
        }
        $stmtTl = $pdo->prepare("SELECT T.team_leader_id
                                 FROM teams T
                                 JOIN team_members TM ON T.id = TM.team_id
                                 WHERE TM.user_id = :user_id
                                 LIMIT 1");
        $stmtTl->execute([':user_id' => $lookupUserId]);
        $tlRow = $stmtTl->fetch();
        if ($tlRow && isset($tlRow['team_leader_id']) && (int)$tlRow['team_leader_id'] > 0) {
            echo json_encode(['success' => true, 'team_leader_id' => (int)$tlRow['team_leader_id']]);
        } else {
            echo json_encode(['success' => true, 'team_leader_id' => null]);
        }
        exit;
    }

    // If POST request -> create ticket detail with created_at/updated_at = now - 1 day
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isForm = isset($_POST['from_form']) && $_POST['from_form'] === '1';
        if (!$isForm) {
            header('Content-Type: application/json');
        }

        $action = $_POST['action'] ?? 'create_detail';
        if ($action !== 'create_detail') {
            if ($isForm) {
                echo "<!doctype html><html lang=\"ar\"><head><meta charset=\"utf-8\"><title>خطأ</title></head><body>";
                echo "<h3 style=\"color:#b91c1c;\">خطأ: يجب استخدام action=create_detail</h3>";
                echo "<a href=\"test2.php\">العودة للنموذج</a>";
                echo "</body></html>";
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid action. Use action=create_detail']);
            }
            exit;
        }

        // Read inputs
        $ticketId = isset($_POST['ticket_id']) && $_POST['ticket_id'] !== '' ? (int)$_POST['ticket_id'] : null;
        $ticketNumber = isset($_POST['ticket_number']) ? trim((string)$_POST['ticket_number']) : '';
        $userId = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? (int)$_POST['user_id'] : 1;

        $platformId = isset($_POST['platform_id']) ? (int)$_POST['platform_id'] : 0;
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $subcategoryId = isset($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : 0;
        $codeId = isset($_POST['code_id']) ? (int)$_POST['code_id'] : 0;
        $assignedTeamLeaderId = isset($_POST['assigned_team_leader_id']) ? (int)$_POST['assigned_team_leader_id'] : 0;

        $isVip = isset($_POST['is_vip']) ? (int)$_POST['is_vip'] : 0;
        $phone = isset($_POST['phone']) && $_POST['phone'] !== '' ? (string)$_POST['phone'] : null;
        $notes = isset($_POST['notes']) && $_POST['notes'] !== '' ? (string)$_POST['notes'] : null;
        $countryId = isset($_POST['country_id']) && $_POST['country_id'] !== '' ? (int)$_POST['country_id'] : null;

        // Validate minimal required fields
        if (!$ticketId && $ticketNumber === '') {
            if ($isForm) {
                echo "<!doctype html><html lang=\"ar\"><head><meta charset=\"utf-8\"><title>خطأ</title></head><body>";
                echo "<h3 style=\"color:#b91c1c;\">خطأ: يجب إدخال رقم التذكرة (ticket_id) أو رقمها النصي (ticket_number)</h3>";
                echo "<a href=\"test2.php\">العودة للنموذج</a>";
                echo "</body></html>";
            } else {
                echo json_encode(['success' => false, 'error' => 'ticket_id or ticket_number is required']);
            }
            exit;
        }
        if ($platformId <= 0 || $categoryId <= 0 || $subcategoryId <= 0 || $codeId <= 0) {
            if ($isForm) {
                echo "<!doctype html><html lang=\"ar\"><head><meta charset=\"utf-8\"><title>خطأ</title></head><body>";
                echo "<h3 style=\"color:#b91c1c;\">خطأ: الحقول التالية مطلوبة ويجب أن تكون أكبر من صفر: platform_id, category_id, subcategory_id, code_id</h3>";
                echo "<a href=\"test2.php\">العودة للنموذج</a>";
                echo "</body></html>";
            } else {
                echo json_encode(['success' => false, 'error' => 'platform_id, category_id, subcategory_id, code_id are required and must be > 0']);
            }
            exit;
        }

        // Auto-detect assigned team leader if not provided or invalid
        if ($assignedTeamLeaderId <= 0) {
            $stmtTl = $pdo->prepare("SELECT T.team_leader_id
                                     FROM teams T
                                     JOIN team_members TM ON T.id = TM.team_id
                                     WHERE TM.user_id = :user_id
                                     LIMIT 1");
            $stmtTl->execute([':user_id' => $userId]);
            $tl = $stmtTl->fetch();
            if ($tl && isset($tl['team_leader_id']) && (int)$tl['team_leader_id'] > 0) {
                $assignedTeamLeaderId = (int)$tl['team_leader_id'];
            } else {
                $assignedTeamLeaderId = null;
            }
        }

        // Compute timestamps: if Cairo time between 00:00-06:00, use -1 day; otherwise use now (all saved in UTC)
        $utcNow = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $cairoTime = $utcNow->setTimezone(new DateTimeZone('Africa/Cairo'));
        $hour = (int)$cairoTime->format('H');
        if ($hour >= 0 && $hour < 6) {
            $cairoTime = $cairoTime->modify('-1 day');
        }
        $effectiveUtcTs = $cairoTime->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        try {
            $pdo->beginTransaction();

            // If no ticket_id provided, try to find by ticket_number; create if not exists
            if (!$ticketId) {
                if ($ticketNumber !== '') {
                    $stmtLookupTicket = $pdo->prepare("SELECT id FROM tickets WHERE ticket_number = :ticket_number LIMIT 1");
                    $stmtLookupTicket->execute([':ticket_number' => $ticketNumber]);
                    $existingTicket = $stmtLookupTicket->fetch();
                    if ($existingTicket && isset($existingTicket['id'])) {
                        $ticketId = (int)$existingTicket['id'];
                    }
                }

                if (!$ticketId) {
                    $stmtCreateTicket = $pdo->prepare(
                        "INSERT INTO tickets (ticket_number, created_by) VALUES (:ticket_number, :created_by)"
                    );
                    $stmtCreateTicket->execute([
                        ':ticket_number' => $ticketNumber,
                        ':created_by' => $userId
                    ]);
                    $ticketId = (int)$pdo->lastInsertId();
                }
            }

            // Insert ticket detail with -1 day timestamps
            $stmtDetail = $pdo->prepare(
                "INSERT INTO ticket_details (
                    ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id,
                    assigned_team_leader_id, created_by, edited_by, created_at, updated_at
                ) VALUES (
                    :ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id,
                    :assigned_team_leader_id, :created_by, :edited_by, :created_at, :updated_at
                )"
            );

            $stmtDetail->execute([
                ':ticket_id' => $ticketId,
                ':is_vip' => $isVip,
                ':platform_id' => $platformId,
                ':phone' => $phone,
                ':category_id' => $categoryId,
                ':subcategory_id' => $subcategoryId,
                ':code_id' => $codeId,
                ':notes' => $notes,
                ':country_id' => $countryId,
                ':assigned_team_leader_id' => $assignedTeamLeaderId,
                ':created_by' => $userId,
                ':edited_by' => $userId,
                ':created_at' => $effectiveUtcTs,
                ':updated_at' => $effectiveUtcTs
            ]);

            $ticketDetailId = (int)$pdo->lastInsertId();
            $pdo->commit();

            if ($isForm) {
                echo "<!doctype html><html lang=\"ar\"><head><meta charset=\"utf-8\"><title>تم الحفظ</title></head><body>";
                echo "<h3 style=\"color:#065f46;\">تم إنشاء تفصيلة التذكرة بنجاح</h3>";
                echo "<ul>";
                echo "<li>Ticket ID: <strong>" . htmlspecialchars((string)$ticketId, ENT_QUOTES, 'UTF-8') . "</strong></li>";
                echo "<li>Ticket Detail ID: <strong>" . htmlspecialchars((string)$ticketDetailId, ENT_QUOTES, 'UTF-8') . "</strong></li>";
                echo "<li>Created At (UTC): <strong>" . htmlspecialchars($effectiveUtcTs, ENT_QUOTES, 'UTF-8') . "</strong></li>";
                echo "</ul>";
                echo "<a href=\"test2.php\">العودة للنموذج</a>";
                echo "</body></html>";
            } else {
                echo json_encode([
                    'success' => true,
                    'ticket_id' => $ticketId,
                    'ticket_detail_id' => $ticketDetailId,
                    'created_at' => $effectiveUtcTs,
                    'updated_at' => $effectiveUtcTs
                ]);
            }
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($isForm) {
                echo "<!doctype html><html lang=\"ar\"><head><meta charset=\"utf-8\"><title>خطأ</title></head><body>";
                echo "<h3 style=\"color:#b91c1c;\">حدث خطأ أثناء الحفظ</h3>";
                echo "<pre style=\"background:#fee2e2;padding:12px;\">" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
                echo "<a href=\"test2.php\">العودة للنموذج</a>";
                echo "</body></html>";
            } else {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
    }

    // Prefetch lists for selects (platforms, categories, subcategories, codes)
    $platforms = [];
    $categories = [];
    $subcategories = [];
    $codes = [];
    $countries = [];
    try {
        $stmt = $pdo->query("SELECT id, name FROM platforms ORDER BY name ASC");
        $platforms = $stmt->fetchAll();
    } catch (\Throwable $e) {}
    try {
        $stmt = $pdo->query("SELECT id, name FROM ticket_categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();
    } catch (\Throwable $e) {}
    try {
        $stmt = $pdo->query("SELECT id, name, category_id FROM ticket_subcategories ORDER BY name ASC");
        $subcategories = $stmt->fetchAll();
    } catch (\Throwable $e) {}
    try {
        $stmt = $pdo->query("SELECT id, name, subcategory_id FROM ticket_codes ORDER BY name ASC");
        $codes = $stmt->fetchAll();
    } catch (\Throwable $e) {}
    try {
        $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
        $countries = $stmt->fetchAll();
    } catch (\Throwable $e) {}

    // Render simple HTML form (GET)
    echo "<!doctype html><html lang=\"ar\"><head><meta charset=\"utf-8\"><title>إنشاء تفصيلة تذكرة (-1 يوم)</title>";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />";
    echo "<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;padding:20px;direction:rtl}";
    echo "label{display:block;margin:8px 0 4px}input,textarea,select{width:100%;max-width:520px;padding:8px;border:1px solid #ddd;border-radius:6px}";
    echo ".row{margin-bottom:12px}.btn{background:#2563eb;color:#fff;border:none;padding:10px 16px;border-radius:8px;cursor:pointer}";
    echo ".grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;max-width:1080px}";
    echo ".card{border:1px solid #eee;border-radius:10px;padding:16px;margin-bottom:20px}";
    echo "</style></head><body>";

    echo "<h2>إنشاء تفصيلة تذكرة مع ضبط التاريخ -1 يوم (UTC)</h2>";
    echo "<div class=\"card\">";
    echo "<form method=\"post\" action=\"test2.php\">";
    echo "<input type=\"hidden\" name=\"action\" value=\"create_detail\" />";
    echo "<input type=\"hidden\" name=\"from_form\" value=\"1\" />";

    echo "<div class=\"grid\">";
    echo "<div class=\"row\"><label>Ticket ID (اختياري إذا أدخلت Ticket Number)</label><input type=\"number\" name=\"ticket_id\" /></div>";
    echo "<div class=\"row\"><label>Ticket Number (اختياري إذا أدخلت Ticket ID)</label><input type=\"text\" name=\"ticket_number\" placeholder=\"مثال: TCK-2025-0001\" /></div>";
    echo "<div class=\"row\"><label>User ID (معرّف الشخص)</label><input type=\"number\" name=\"user_id\" id=\"user_id\" required /></div>";

    // Platform select
    echo "<div class=\"row\"><label>المنصة (Platform)</label><select name=\"platform_id\" id=\"platform_id\" required>";
    echo "<option value=\"\">— اختر المنصة —</option>";
    foreach ($platforms as $p) {
        echo "<option value=\"" . htmlspecialchars((string)$p['id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
    echo "</select></div>";

    // Category select
    echo "<div class=\"row\"><label>التصنيف (Category)</label><select name=\"category_id\" id=\"category_id\" required>";
    echo "<option value=\"\">— اختر التصنيف —</option>";
    foreach ($categories as $c) {
        echo "<option value=\"" . htmlspecialchars((string)$c['id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
    echo "</select></div>";

    // Subcategory select (dependent)
    echo "<div class=\"row\"><label>التصنيف الفرعي (Subcategory)</label><select name=\"subcategory_id\" id=\"subcategory_id\" required disabled>";
    echo "<option value=\"\">— اختر التصنيف الفرعي —</option>";
    echo "</select></div>";

    // Code select (dependent)
    echo "<div class=\"row\"><label>الكود (Code)</label><select name=\"code_id\" id=\"code_id\" required disabled>";
    echo "<option value=\"\">— اختر الكود —</option>";
    echo "</select></div>";

    // Assigned Team Leader ID (auto)
    echo "<div class=\"row\"><label>Assigned Team Leader ID (يُحدّد تلقائيًا حسب User ID)</label><input type=\"number\" name=\"assigned_team_leader_id\" id=\"assigned_team_leader_id\" placeholder=\"سيتم تعبئته تلقائيًا\" readonly /></div>";

    echo "<div class=\"row\"><label>Is VIP</label><input type=\"number\" name=\"is_vip\" value=\"0\" min=\"0\" max=\"1\" /></div>";
    echo "<div class=\"row\"><label>Phone</label><input type=\"text\" name=\"phone\" /></div>";
    // Country select
    echo "<div class=\"row\"><label>الدولة (Country)</label><select name=\"country_id\" id=\"country_id\">";
    echo "<option value=\"\">— اختر الدولة —</option>";
    foreach ($countries as $cn) {
        echo "<option value=\"" . htmlspecialchars((string)$cn['id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars((string)$cn['name'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
    echo "</select></div>";
    echo "<div class=\"row\" style=\"grid-column:1/-1;\"><label>Notes</label><textarea name=\"notes\" rows=\"3\" placeholder=\"ملاحظات (اختياري)\"></textarea></div>";
    echo "</div>";

    echo "<div class=\"row\"><button class=\"btn\" type=\"submit\">إنشاء التفصيلة</button></div>";
    echo "</form>";
    echo "</div>";

    // Inject datasets and simple JS for dependent selects and TL lookup
    echo "<script>";
    echo "var ALL_CATEGORIES = " . json_encode($categories, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) . ";";
    echo "var ALL_SUBCATEGORIES = " . json_encode($subcategories, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) . ";";
    echo "var ALL_CODES = " . json_encode($codes, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) . ";";
    echo "function populateSubcategories(catId){var subSel=document.getElementById('subcategory_id');var codeSel=document.getElementById('code_id');subSel.innerHTML='<option value=\"\">— اختر التصنيف الفرعي —</option>';codeSel.innerHTML='<option value=\"\">— اختر الكود —</option>';codeSel.disabled=true;if(!catId){subSel.disabled=true;return;}var subs=ALL_SUBCATEGORIES.filter(function(s){return String(s.category_id)===String(catId);});subs.forEach(function(s){var opt=document.createElement('option');opt.value=s.id;opt.textContent=s.name;subSel.appendChild(opt);});subSel.disabled=false;}";
    echo "function populateCodes(subId){var codeSel=document.getElementById('code_id');codeSel.innerHTML='<option value=\"\">— اختر الكود —</option>';if(!subId){codeSel.disabled=true;return;}var list=ALL_CODES.filter(function(c){return String(c.subcategory_id)===String(subId);});list.forEach(function(c){var opt=document.createElement('option');opt.value=c.id;opt.textContent=c.name;codeSel.appendChild(opt);});codeSel.disabled=false;}";
    echo "document.addEventListener('DOMContentLoaded',function(){var category=document.getElementById('category_id');var sub=document.getElementById('subcategory_id');var user=document.getElementById('user_id');category.addEventListener('change',function(){populateSubcategories(this.value);});sub.addEventListener('change',function(){populateCodes(this.value);});user.addEventListener('change',function(){var uid=this.value;var tlInput=document.getElementById('assigned_team_leader_id');tlInput.value='';if(uid&&Number(uid)>0){fetch('test2.php?action=lookup_tl&user_id='+encodeURIComponent(uid)).then(function(r){return r.json();}).then(function(d){if(d&&d.success){tlInput.value=(d.team_leader_id||'');}}).catch(function(){tlInput.value='';});}});});";
    echo "</script>";

    // 1. وقت السيرفر
    $serverTime = new DateTime("now", new DateTimeZone("UTC"));
    echo "<div class=\"card\"><h3>معلومات الوقت</h3>";
    echo "<pre>Server UTC Time: " . $serverTime->format('Y-m-d H:i:s') . "</pre>";

    // 2. وقت قاعدة البيانات
    $stmt = $pdo->query("SELECT NOW() AS db_time, @@global.time_zone AS db_global_tz, @@session.time_zone AS db_session_tz");
    $row = $stmt->fetch();
    echo "<pre>Database Time: " . $row['db_time'] . "\nDatabase Global Timezone: " . $row['db_global_tz'] . "\nDatabase Session Timezone: " . $row['db_session_tz'] . "</pre>";

    // 3. وقت المستخدم (مثال: توقيت القاهرة)
    $userTime = new DateTime("now", new DateTimeZone("Africa/Cairo"));
    echo "<pre>User Local Time (Cairo): " . $userTime->format('Y-m-d H:i:s') . "</pre>";
    echo "</div>";

    echo "</body></html>";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
