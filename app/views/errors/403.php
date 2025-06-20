<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - وصول مرفوض</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full mx-4 text-center">
        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100">
            <svg class="h-16 w-16 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-800 mt-6">وصول مرفوض</h1>
        <p class="mt-4 text-lg text-gray-600">
            <?php echo isset($message) && !empty($message) ? htmlspecialchars($message) : 'عفواً، ليس لديك الصلاحيات اللازمة لعرض هذا المحتوى.'; ?>
        </p>
        <p class="mt-2 text-sm text-gray-500">
            إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع مسؤول النظام.
        </p>
        <a href="<?php echo isset($_SESSION['user_id']) ? BASE_PATH . '/dashboard' : BASE_PATH . '/auth/login'; ?>" 
           class="inline-block w-full px-6 py-3 mt-8 text-lg font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-transform transform hover:scale-105">
           <?php echo isset($_SESSION['user_id']) ? 'العودة إلى لوحة التحكم' : 'الذهاب إلى صفحة الدخول'; ?>
        </a>
    </div>

    <?php if (isset($data['debug_info']) && !empty($data['debug_info'])) : ?>
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-gray-800 text-white font-mono text-sm">
            <h3 class="text-lg font-bold mb-2 border-b border-gray-600 pb-2">معلومات تشخيص الصلاحيات (للمطورين)</h3>
            
            <div class="mb-2">
                <strong class="text-red-400">الصلاحية المطلوبة للوصول لهذه الصفحة:</strong>
                <pre class="bg-red-900 text-white p-2 rounded mt-1"><?= htmlspecialchars($data['debug_info']['required_permission']) ?></pre>
            </div>

            <div class="mb-2">
                <strong class="text-yellow-400">دور المستخدم الحالي:</strong>
                 <pre class="bg-gray-900 p-2 rounded mt-1"><?= htmlspecialchars($data['debug_info']['user_role']) ?></pre>
            </div>

            <div>
                <strong class="text-cyan-400">الصلاحيات الممنوحة لهذا الدور (من الجلسة):</strong>
                <pre class="bg-gray-900 p-2 rounded mt-1"><?php print_r($data['debug_info']['user_permissions']); ?></pre>
            </div>
        </div>
    <?php endif; ?>

</body>
</html> 