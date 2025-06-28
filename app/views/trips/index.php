<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع بيانات الرحلات</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">رفع بيانات الرحلات</h1>
                <a href="<?= BASE_PATH ?>/dashboard" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-arrow-right ml-1"></i>
                    العودة للوحة التحكم
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-400"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm text-yellow-700">
                            <b>تعليمات هامة:</b>
                            <br>
                            - النظام سيقوم بإضافة رحلة جديدة إذا كان `order_id` غير موجود.
                            <br>
                            - سيتم تحديث بيانات الرحلة الحالية إذا كان `order_id` موجوداً بالفعل.
                            <br>
                            - تأكد من تطابق أسماء الأعمدة في الملف مع أسماء الحقول في قاعدة البيانات.
                        </p>
                    </div>
                </div>
            </div>

            <form id="upload-form" class="space-y-6">
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">اختر ملف Excel أو CSV للرحلات</label>
                    <div id="upload-area" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-file-excel text-gray-400 text-4xl mb-3"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>اختر ملف ليتم رفعه</span>
                                    <input id="file" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only" required>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">يُسمح بملفات CSV, XLSX, XLS</p>
                            <p id="file-name" class="text-sm text-gray-700 mt-2"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" id="submit-button"
                        class="w-full sm:w-auto bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-upload ml-2"></i>
                        بدء عملية الرفع والمعالجة
                    </button>
                </div>
            </form>
            
            <div id="progress-container" class="mt-8 hidden">
                <h3 class="text-lg font-semibold mb-2">تقدم المعالجة:</h3>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div id="progress-bar" class="bg-blue-600 h-4 rounded-full" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-center mt-2 text-gray-600"></p>
            </div>
            
            <div id="stats-container" class="mt-8 hidden">
                <h3 class="text-lg font-semibold mb-2">نتائج المعالجة:</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="bg-green-100 p-4 rounded-lg">
                        <p class="text-2xl font-bold text-green-700" id="inserted-count">0</p>
                        <p class="text-green-600">صفوف تمت إضافتها</p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-lg">
                        <p class="text-2xl font-bold text-yellow-700" id="updated-count">0</p>
                        <p class="text-yellow-600">صفوف تم تحديثها</p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-lg">
                        <p class="text-2xl font-bold text-red-700" id="error-count">0</p>
                        <p class="text-red-600">صفوف بها أخطاء</p>
                    </div>
                </div>
            </div>

            <div id="error-details" class="mt-4 hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <strong class="font-bold">حدث خطأ!</strong>
                <span class="block sm:inline" id="error-message"></span>
            </div>
        </div>
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="/taxi/app/views/trips/js/trips.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</html> 