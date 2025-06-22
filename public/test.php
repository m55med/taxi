<?php
// دالة لتحويل الرابط المختصر إلى الرابط الكامل
function resolveGoogleMapsShortLink($shortUrl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $shortUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // يسمح بمتابعة التحويلات
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // لا نحتاج لمحتوى الصفحة

    curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return $finalUrl;
}

// دالة لاستخراج الإحداثيات من الرابط الكامل
function extractCoordinates($url) {
    // من !3d و !4d
    if (preg_match('/!3d([\d\.]+)!4d([\d\.]+)/', $url, $matches)) {
        return ['latitude' => $matches[1], 'longitude' => $matches[2]];
    }

    // من @lat,long
    if (preg_match('/@([\d\.]+),([\d\.]+)/', $url, $matches)) {
        return ['latitude' => $matches[1], 'longitude' => $matches[2]];
    }

    // من q=lat,long
    if (preg_match('/[?&]q=([\d\.]+),([\d\.]+)/', $url, $matches)) {
        return ['latitude' => $matches[1], 'longitude' => $matches[2]];
    }

    return null;
}


// معالجة النموذج إذا تم إرسال رابط
$coordinates = null;
$error = null;
$resolvedUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['map_url'])) {
    $shortLink = trim($_POST['map_url']);
    $resolvedUrl = resolveGoogleMapsShortLink($shortLink);
    $coordinates = extractCoordinates($resolvedUrl);

    if (!$coordinates) {
        $error = "تعذر استخراج الإحداثيات من الرابط.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>استخراج الإحداثيات من Google Maps</title>
</head>
<body style="font-family: Arial; direction: rtl; padding: 20px;">

    <h1>📍 استخراج إحداثيات من رابط Google Maps</h1>

    <form method="POST">
        <label for="map_url">أدخل رابط Google Maps المختصر:</label><br>
        <input type="text" name="map_url" id="map_url" style="width: 100%; padding: 10px;" placeholder="مثال: https://maps.app.goo.gl/xxxxx" required>
        <br><br>
        <button type="submit">استخراج الإحداثيات</button>
    </form>

    <hr>

    <?php if ($resolvedUrl): ?>
        <p><strong>🔗 الرابط النهائي:</strong> <a href="<?= htmlspecialchars($resolvedUrl) ?>" target="_blank"><?= htmlspecialchars($resolvedUrl) ?></a></p>
    <?php endif; ?>

    <?php if ($coordinates): ?>
        <p><strong>📌 خط العرض:</strong> <?= $coordinates['latitude'] ?></p>
        <p><strong>📌 خط الطول:</strong> <?= $coordinates['longitude'] ?></p>
        <p><a href="https://www.google.com/maps?q=<?= $coordinates['latitude'] ?>,<?= $coordinates['longitude'] ?>" target="_blank">🔍 عرض على الخريطة</a></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

</body>
</html>
