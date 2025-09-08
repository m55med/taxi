<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= htmlspecialchars($data['title']) ?></h1>

        <!-- System Overview Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <a href="<?= BASE_PATH ?>/reports/users" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full"><i class="fas fa-users text-blue-600 fa-lg"></i></div>
                    <div class="mr-4">
                        <h3 class="text-gray-600 text-sm font-medium">إجمالي المستخدمين</h3>
                        <p class="text-3xl font-bold text-gray-800"><?= $data['overview']['total_users'] ?></p>
                    </div>
                </div>
            </a>
            <!-- Online Users -->
             <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full"><i class="fas fa-wifi text-green-600 fa-lg"></i></div>
                    <div class="mr-4">
                        <h3 class="text-gray-600 text-sm font-medium">المستخدمون المتصلون</h3>
                        <p class="text-3xl font-bold text-gray-800"><?= $data['overview']['online_users'] ?></p>
                    </div>
                </div>
            </div>
            <!-- Total Drivers -->
            <a href="#" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full"><i class="fas fa-id-card-alt text-yellow-600 fa-lg"></i></div>
                    <div class="mr-4">
                        <h3 class="text-gray-600 text-sm font-medium">إجمالي السائقين</h3>
                        <p class="text-3xl font-bold text-gray-800"><?= $data['overview']['total_drivers'] ?></p>
                    </div>
                </div>
            </a>
            <!-- Active Drivers -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full"><i class="fas fa-user-check text-purple-600 fa-lg"></i></div>
                    <div class="mr-4">
                        <h3 class="text-gray-600 text-sm font-medium">السائقون النشطون</h3>
                        <p class="text-3xl font-bold text-gray-800"><?= $data['overview']['active_drivers'] ?></p>
                    </div>
                </div>
            </div>
             <!-- Total Calls -->
            <a href="#" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full"><i class="fas fa-phone-alt text-red-600 fa-lg"></i></div>
                    <div class="mr-4">
                        <h3 class="text-gray-600 text-sm font-medium">إجمالي المكالمات</h3>
                        <p class="text-3xl font-bold text-gray-800"><?= $data['overview']['total_calls'] ?></p>
                    </div>
                </div>
            </a>
             <!-- Total Tickets -->
            <a href="#" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-full"><i class="fas fa-ticket-alt text-indigo-600 fa-lg"></i></div>
                    <div class="mr-4">
                        <h3 class="text-gray-600 text-sm font-medium">إجمالي التذاكر</h3>
                        <p class="text-3xl font-bold text-gray-800"><?= $data['overview']['total_tickets'] ?></p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Links to Detailed Reports -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">روابط سريعة للتقارير</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <a href="<?= BASE_PATH ?>/reports/users" class="text-indigo-600 hover:underline">تقرير المستخدمين</a>
                <a href="#" class="text-indigo-600 hover:underline">تقرير الفرق</a>
                <a href="#" class="text-indigo-600 hover:underline">تقرير السائقين المفصل</a>
                <a href="#" class="text-indigo-600 hover:underline">تقرير مكالمات السائقين</a>
                <!-- Add more links as reports are created -->
            </div>
        </div>
    </div>
</body>
</html> 