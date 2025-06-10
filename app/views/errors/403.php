<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - غير مصرح لك بالدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="px-8 py-6 mx-4 mt-4 text-center bg-white shadow-lg md:w-1/3 lg:w-1/3 sm:w-1/3 rounded-lg">
            <h1 class="text-6xl font-bold text-red-600">403</h1>
            <h2 class="text-2xl font-bold mt-4">غير مصرح لك بالدخول</h2>
            <p class="mt-2 text-gray-600">عفواً، ليس لديك الصلاحية للوصول إلى هذه الصفحة.</p>
            <p class="mt-1 text-gray-600">يجب أن تكون مسؤولاً (Admin) لعرض هذا المحتوى.</p>
            <a href="<?php echo BASE_PATH; ?>/dashboard" class="inline-block px-6 py-2 mt-6 text-white bg-blue-600 rounded-lg hover:bg-blue-900">
                العودة إلى لوحة التحكم
            </a>
        </div>
    </div>
</body>
</html> 