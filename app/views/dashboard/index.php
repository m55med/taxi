<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
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
        <!-- ترحيب المستخدم -->
        <!-- <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-800">مرحبًا، <?= htmlspecialchars($_SESSION['username']) ?> 👋</h1>
            <p class="mt-2 text-gray-600">دورك الحالي: <?= $_SESSION['role'] === 'admin' ? 'مدير' : 'مستخدم عادي' ?></p>
        </div> -->

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- قسم إدارة المستخدمين (يظهر فقط للمدير) -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">إدارة المستخدمين</h2>
                <div class="space-x-2 space-x-reverse">
                    <button onclick="window.location.href='<?= BASE_PATH ?>/dashboard/users'" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-users-cog ml-1"></i>
                        إدارة المستخدمين
                    </button>
                    <button onclick="window.location.href='<?= BASE_PATH ?>/upload'" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-file-upload ml-1"></i>
                        رفع بيانات السائقين
                    </button>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-indigo-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <i class="fas fa-users text-indigo-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">إجمالي المستخدمين</p>
                            <p class="text-lg font-semibold text-indigo-600"><?= isset($quickStats['total_users']) ? number_format($quickStats['total_users']) : '0' ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-user-check text-green-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">المستخدمين النشطين</p>
                            <p class="text-lg font-semibold text-green-600"><?= isset($quickStats['active_users']) ? number_format($quickStats['active_users']) : '0' ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-circle text-blue-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">متصلين حالياً</p>
                            <p class="text-lg font-semibold text-blue-600"><?= isset($quickStats['online_users']) ? number_format($quickStats['online_users']) : '0' ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-user-slash text-red-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">المستخدمين المحظورين</p>
                            <p class="text-lg font-semibold text-red-600"><?= isset($quickStats['blocked_users']) ? number_format($quickStats['blocked_users']) : '0' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- محتوى لوحة التحكم العامة -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">لوحة التحكم</h2>
            
            <?php if (in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager'])): ?>
            <!-- قسم التقارير -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-4">التقارير</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- تقرير المستخدمين -->
                    <a href="<?= BASE_PATH ?>/reports/users" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="text-lg font-medium text-gray-800">تقرير المستخدمين</h4>
                                <p class="text-sm text-gray-600">إحصائيات وتحليل المستخدمين</p>
                            </div>
                        </div>
                    </a>

                    <!-- تقرير السائقين -->
                    <a href="<?= BASE_PATH ?>/reports/drivers" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-car text-green-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="text-lg font-medium text-gray-800">تقرير السائقين</h4>
                                <p class="text-sm text-gray-600">حالات وإحصائيات السائقين</p>
                            </div>
                        </div>
                    </a>

                    <!-- تقرير المستندات -->
                    <a href="<?= BASE_PATH ?>/reports/documents" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-file-alt text-yellow-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="text-lg font-medium text-gray-800">تقرير المستندات</h4>
                                <p class="text-sm text-gray-600">حالة وتحليل المستندات</p>
                            </div>
                        </div>
                    </a>

                    <!-- تقرير المكالمات -->
                    <a href="<?= BASE_PATH ?>/reports/calls" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fas fa-phone-alt text-purple-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="text-lg font-medium text-gray-800">تقرير المكالمات</h4>
                                <p class="text-sm text-gray-600">إحصائيات وتحليل المكالمات</p>
                            </div>
                        </div>
                    </a>

                    <!-- تقرير التحويلات -->
                    <a href="<?= BASE_PATH ?>/reports/assignments" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-full">
                                <i class="fas fa-exchange-alt text-red-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="text-lg font-medium text-gray-800">تقرير التحويلات</h4>
                                <p class="text-sm text-gray-600">تحليل تحويلات السائقين</p>
                            </div>
                        </div>
                    </a>

                    <!-- تقرير التحليلات -->
                    <a href="<?= BASE_PATH ?>/reports/analytics" class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 rounded-full">
                                <i class="fas fa-chart-line text-indigo-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="text-lg font-medium text-gray-800">التحليلات الذكية</h4>
                                <p class="text-sm text-gray-600">تحليلات وإحصائيات متقدمة</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- مركز الاتصال -->
                <a href="<?= BASE_PATH ?>/call" 
                   class="bg-white overflow-hidden shadow rounded-lg p-6 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <i class="fas fa-phone-alt text-white text-2xl"></i>
                        </div>
                        <div class="mr-5">
                            <h3 class="text-lg font-medium text-gray-900">مركز الاتصال</h3>
                            <p class="mt-1 text-sm text-gray-500">إدارة المكالمات والتواصل مع السائقين</p>
                        </div>
                    </div>
                </a>

                <!-- مراجعة المكالمات والمستندات -->
                <a href="<?= BASE_PATH ?>/review" 
                   class="bg-white overflow-hidden shadow rounded-lg p-6 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-clipboard-check text-white text-2xl"></i>
                        </div>
                        <div class="mr-5">
                            <h3 class="text-lg font-medium text-gray-900">مراجعة المكالمات والمستندات</h3>
                            <p class="mt-1 text-sm text-gray-500">التحقق من المستندات ومراجعة حالات السائقين</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <style>
    /* تحسينات التصميم */
    .container {
        max-width: 1280px;
    }

    /* تحسين البطاقات */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    /* تحسين الأزرار */
    .btn {
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* تحسين التجاوب */
    @media (max-width: 640px) {
        .container {
            padding: 1rem;
        }
        
        .grid {
            grid-template-columns: 1fr;
        }
    }

    /* تحسين التمرير */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>
</body>

</html>