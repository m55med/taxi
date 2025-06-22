<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة غير موجودة</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center p-12 bg-white rounded-2xl shadow-2xl max-w-lg mx-auto">
        <div class="text-9xl font-extrabold text-indigo-600 tracking-wider">404</div>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">عفواً! الصفحة غير موجودة.</h1>
        <p class="text-gray-600 mt-4 mb-8 text-lg">
            الصفحة التي تبحث عنها قد تكون حُذفت، أو تم تغيير اسمها، أو أنها غير متاحة مؤقتاً.
        </p>
        <a href="<?= BASE_PATH ?>/dashboard" 
           class="inline-block bg-indigo-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
            <i class="fas fa-home ml-2"></i>
            العودة إلى لوحة التحكم
        </a>

        <?php if (isset($diagnostics)): ?>
        <div class="mt-8 pt-6 border-t border-gray-300 text-left text-xs">
            <h3 class="text-lg font-bold text-gray-700 mb-2">Developer Diagnostics</h3>
            <div class="bg-gray-100 p-3 rounded-lg font-mono">
                <p class="font-bold text-sm text-red-700 break-words mb-3 p-2 bg-red-100 rounded">
                    <strong>Message:</strong> <?= htmlspecialchars($debug_message ?? 'No message') ?>
                </p>
                <pre class="whitespace-pre-wrap"><code><?php print_r($diagnostics); ?></code></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 