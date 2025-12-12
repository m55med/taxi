<?php
/**
 * Session Diagnostic Tool
 * This script checks for potential session security issues
 * that could cause users to see other users' accounts
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تشخيص الجلسات - Session Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: right;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <h1>🔍 تشخيص إعدادات الجلسات - Session Diagnostic</h1>
    
    <?php
    $issues = [];
    $warnings = [];
    
    // 1. Check session configuration
    echo '<div class="section">';
    echo '<h2>1. إعدادات الجلسة الحالية</h2>';
    
    $sessionParams = session_get_cookie_params();
    echo '<table>';
    echo '<tr><th>المعامل</th><th>القيمة الحالية</th><th>الحالة</th></tr>';
    
    // Check session cookie domain
    $domain = $sessionParams['domain'];
    echo '<tr><td>Cookie Domain</td><td>' . htmlspecialchars($domain ?: 'غير محدد (default)') . '</td>';
    if (empty($domain)) {
        echo '<td class="success">✓ جيد (سيستخدم domain الافتراضي)</td>';
    } elseif (strpos($domain, '.') === 0) {
        echo '<td class="warning">⚠️ قد يسمح بمشاركة الجلسات بين subdomains</td>';
        $warnings[] = 'Cookie domain يبدأ بنقطة - قد يسمح بمشاركة الجلسات بين subdomains';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    // Check session cookie path
    $path = $sessionParams['path'];
    echo '<tr><td>Cookie Path</td><td>' . htmlspecialchars($path) . '</td>';
    if ($path === '/') {
        echo '<td class="success">✓ جيد</td>';
    } else {
        echo '<td class="warning">⚠️ قد يسبب مشاكل إذا كان path محدود</td>';
        $warnings[] = 'Cookie path ليس "/" - قد يسبب مشاكل في بعض الحالات';
    }
    echo '</tr>';
    
    // Check secure flag
    $secure = $sessionParams['secure'];
    echo '<tr><td>Secure Flag</td><td>' . ($secure ? 'نعم' : 'لا') . '</td>';
    if (!$secure && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        echo '<td class="error">❌ خطير: يجب تفعيل secure flag على HTTPS</td>';
        $issues[] = 'Secure flag غير مفعل على HTTPS - قد يسمح بسرقة الجلسات';
    } elseif (!$secure) {
        echo '<td class="warning">⚠️ يجب تفعيله على HTTPS</td>';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    // Check httponly flag
    $httponly = $sessionParams['httponly'];
    echo '<tr><td>HttpOnly Flag</td><td>' . ($httponly ? 'نعم' : 'لا') . '</td>';
    if (!$httponly) {
        echo '<td class="error">❌ خطير: يجب تفعيل HttpOnly لمنع JavaScript من الوصول للجلسة</td>';
        $issues[] = 'HttpOnly flag غير مفعل - قد يسمح بسرقة الجلسات عبر XSS';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    // Check samesite
    $samesite = $sessionParams['samesite'] ?? 'غير محدد';
    echo '<tr><td>SameSite</td><td>' . htmlspecialchars($samesite) . '</td>';
    if ($samesite === 'Lax' || $samesite === 'Strict') {
        echo '<td class="success">✓ جيد</td>';
    } else {
        echo '<td class="warning">⚠️ يُفضل تعيين SameSite=Lax أو Strict</td>';
        $warnings[] = 'SameSite غير محدد - يُفضل تعيينه لـ Lax أو Strict';
    }
    echo '</tr>';
    
    // Check lifetime
    $lifetime = $sessionParams['lifetime'];
    echo '<tr><td>Cookie Lifetime</td><td>' . $lifetime . ' ثانية (' . round($lifetime/60) . ' دقيقة)</td>';
    if ($lifetime > 3600) {
        echo '<td class="warning">⚠️ عمر الجلسة طويل جداً</td>';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    echo '</table>';
    echo '</div>';
    
    // 2. Check session ID
    echo '<div class="section">';
    echo '<h2>2. معلومات الجلسة الحالية</h2>';
    echo '<table>';
    echo '<tr><th>المعلومة</th><th>القيمة</th></tr>';
    echo '<tr><td>Session ID</td><td>' . htmlspecialchars(session_id()) . '</td></tr>';
    echo '<tr><td>Session Name</td><td>' . htmlspecialchars(session_name()) . '</td></tr>';
    echo '<tr><td>Session Status</td><td>' . (session_status() === PHP_SESSION_ACTIVE ? 'نشط' : 'غير نشط') . '</td></tr>';
    echo '<tr><td>Session Save Path</td><td>' . htmlspecialchars(session_save_path()) . '</td></tr>';
    echo '</table>';
    echo '</div>';
    
    // 3. Check session data
    echo '<div class="section">';
    echo '<h2>3. بيانات الجلسة الحالية</h2>';
    if (empty($_SESSION)) {
        echo '<p class="warning">⚠️ الجلسة فارغة - لم يتم تسجيل الدخول</p>';
    } else {
        echo '<pre>' . htmlspecialchars(print_r($_SESSION, true)) . '</pre>';
        
        // Check for user data
        if (isset($_SESSION['user_id'])) {
            echo '<p class="success">✓ تم العثور على user_id في الجلسة: ' . htmlspecialchars($_SESSION['user_id']) . '</p>';
        }
        
        if (isset($_SESSION['user'])) {
            echo '<p class="success">✓ تم العثور على بيانات المستخدم في الجلسة</p>';
            if (isset($_SESSION['user']['id'])) {
                echo '<p>معرف المستخدم: ' . htmlspecialchars($_SESSION['user']['id']) . '</p>';
                echo '<p>اسم المستخدم: ' . htmlspecialchars($_SESSION['user']['username'] ?? 'غير محدد') . '</p>';
                echo '<p>الاسم: ' . htmlspecialchars($_SESSION['user']['name'] ?? 'غير محدد') . '</p>';
            }
        }
    }
    echo '</div>';
    
    // 4. Check PHP session configuration
    echo '<div class="section">';
    echo '<h2>4. إعدادات PHP للجلسات</h2>';
    echo '<table>';
    echo '<tr><th>الإعداد</th><th>القيمة</th><th>الحالة</th></tr>';
    
    $sessionGcMaxlifetime = ini_get('session.gc_maxlifetime');
    echo '<tr><td>session.gc_maxlifetime</td><td>' . $sessionGcMaxlifetime . ' ثانية</td>';
    if ($sessionGcMaxlifetime < 1800) {
        echo '<td class="warning">⚠️ قصير جداً</td>';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    $sessionCookieLifetime = ini_get('session.cookie_lifetime');
    echo '<tr><td>session.cookie_lifetime</td><td>' . ($sessionCookieLifetime == 0 ? '0 (حتى إغلاق المتصفح)' : $sessionCookieLifetime . ' ثانية') . '</td><td>-</td></tr>';
    
    $sessionUseStrictMode = ini_get('session.use_strict_mode');
    echo '<tr><td>session.use_strict_mode</td><td>' . ($sessionUseStrictMode ? 'مفعل' : 'معطل') . '</td>';
    if (!$sessionUseStrictMode) {
        echo '<td class="error">❌ خطير: يجب تفعيل use_strict_mode لمنع session fixation</td>';
        $issues[] = 'session.use_strict_mode معطل - قد يسمح بهجمات session fixation';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    $sessionUseCookies = ini_get('session.use_cookies');
    echo '<tr><td>session.use_cookies</td><td>' . ($sessionUseCookies ? 'مفعل' : 'معطل') . '</td>';
    if (!$sessionUseCookies) {
        echo '<td class="error">❌ خطير: يجب استخدام cookies للجلسات</td>';
        $issues[] = 'session.use_cookies معطل - قد يسبب مشاكل أمنية';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    $sessionUseOnlyCookies = ini_get('session.use_only_cookies');
    echo '<tr><td>session.use_only_cookies</td><td>' . ($sessionUseOnlyCookies ? 'مفعل' : 'معطل') . '</td>';
    if (!$sessionUseOnlyCookies) {
        echo '<td class="error">❌ خطير: يجب استخدام cookies فقط للجلسات</td>';
        $issues[] = 'session.use_only_cookies معطل - قد يسمح بتمرير session ID في URL';
    } else {
        echo '<td class="success">✓ جيد</td>';
    }
    echo '</tr>';
    
    echo '</table>';
    echo '</div>';
    
    // 5. Check for multiple session_start calls
    echo '<div class="section">';
    echo '<h2>5. فحص ملفات الكود</h2>';
    echo '<p>التحقق من وجود استدعاءات متعددة لـ session_start()...</p>';
    // This would require file reading, so we'll just note it
    echo '<p class="warning">⚠️ يُفضل فحص الكود يدوياً للتأكد من عدم وجود استدعاءات متعددة لـ session_start()</p>';
    echo '</div>';
    
    // 6. Check server environment
    echo '<div class="section">';
    echo '<h2>6. معلومات الخادم</h2>';
    echo '<table>';
    echo '<tr><th>المعلومة</th><th>القيمة</th></tr>';
    echo '<tr><td>PHP Version</td><td>' . phpversion() . '</td></tr>';
    echo '<tr><td>Server Software</td><td>' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'غير محدد') . '</td></tr>';
    echo '<tr><td>HTTPS</td><td>' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'نعم' : 'لا') . '</td></tr>';
    echo '<tr><td>HTTP Host</td><td>' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'غير محدد') . '</td></tr>';
    echo '<tr><td>Request URI</td><td>' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'غير محدد') . '</td></tr>';
    echo '</table>';
    echo '</div>';
    
    // Summary
    echo '<div class="section">';
    echo '<h2>📊 ملخص النتائج</h2>';
    
    if (empty($issues) && empty($warnings)) {
        echo '<div class="success">';
        echo '<h3>✓ لا توجد مشاكل حرجة</h3>';
        echo '<p>إعدادات الجلسة تبدو جيدة. إذا كانت المشكلة مستمرة، قد تكون بسبب:</p>';
        echo '<ul>';
        echo '<li>مشاكل في الكاش (Browser cache أو Server cache)</li>';
        echo '<li>مشاكل في قاعدة البيانات (مثل استخدام نفس الـ ID)</li>';
        echo '<li>مشاكل في الكود (مثل استخدام متغيرات عامة بدلاً من الجلسة)</li>';
        echo '<li>مشاكل في الشبكة (مثل استخدام proxy مشترك)</li>';
        echo '</ul>';
        echo '</div>';
    } else {
        if (!empty($issues)) {
            echo '<div class="error">';
            echo '<h3>❌ مشاكل حرجة يجب إصلاحها:</h3>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo '<li>' . htmlspecialchars($issue) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        if (!empty($warnings)) {
            echo '<div class="warning">';
            echo '<h3>⚠️ تحذيرات:</h3>';
            echo '<ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    echo '</div>';
    
    // Recommendations
    echo '<div class="section">';
    echo '<h2>💡 توصيات</h2>';
    echo '<ol>';
    echo '<li><strong>تأكد من إعدادات session cookie:</strong> يجب تفعيل HttpOnly و Secure (على HTTPS) و SameSite</li>';
    echo '<li><strong>تفعيل session.use_strict_mode:</strong> يمنع هجمات session fixation</li>';
    echo '<li><strong>استخدام session_regenerate_id(true):</strong> تأكد من استخدامه بعد تسجيل الدخول (موجود في الكود ✓)</li>';
    echo '<li><strong>فحص الكود:</strong> تأكد من عدم وجود متغيرات عامة أو كاش مشترك بين المستخدمين</li>';
    echo '<li><strong>فحص قاعدة البيانات:</strong> تأكد من أن كل مستخدم له ID فريد</li>';
    echo '<li><strong>فحص السيرفر:</strong> تأكد من أن session save path آمن وليس مشترك</li>';
    echo '<li><strong>فحص المتصفح:</strong> قد تكون المشكلة من كاش المتصفح - جرب وضع التصفح الخفي</li>';
    echo '</ol>';
    echo '</div>';
    ?>
    
    <div class="section">
        <h2>🔧 اختبار سريع</h2>
        <p>لاختبار ما إذا كانت المشكلة من الجلسة:</p>
        <ol>
            <li>افتح هذا الملف في متصفحين مختلفين أو وضع التصفح الخفي</li>
            <li>سجل الدخول من كل متصفح بمستخدم مختلف</li>
            <li>تحقق من أن Session ID مختلف في كل متصفح</li>
            <li>تحقق من أن بيانات المستخدم مختلفة في كل متصفح</li>
        </ol>
    </div>
    
    <div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 4px;">
        <p><strong>ملاحظة:</strong> هذا الملف للتشخيص فقط. يُفضل حذفه بعد الانتهاء من الفحص.</p>
    </div>
</body>
</html>

