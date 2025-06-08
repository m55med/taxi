<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع بيانات السائقين</title>
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
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">رفع بيانات السائقين</h1>
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
                            يجب أن يحتوي الملف على الأعمدة التالية:
                            <br>
                            fullName, phone, email, rating, vehicleType, status
                        </p>
                    </div>
                </div>
            </div>

            <form action="<?= BASE_PATH ?>/upload/process" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">اختر ملف CSV/Excel</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-file-excel text-gray-400 text-3xl mb-3"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>اختر ملف</span>
                                    <input id="file" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only" required>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">CSV, XLSX أو XLS</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="data_source" class="block text-sm font-medium text-gray-700">مصدر البيانات</label>
                    <select name="data_source" id="data_source" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="excel">Excel/CSV</option>
                        <option value="form">نموذج</option>
                        <option value="referral">إحالة</option>
                        <option value="telegram">تيليجرام</option>
                        <option value="staff">موظف</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-upload ml-1"></i>
                        رفع البيانات
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html> 