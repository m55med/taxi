<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المستخدمين</title>
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
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">تقرير المستخدمين</h1>
            <p class="text-gray-600">تحليل وإحصائيات المستخدمين في النظام</p>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-medium text-gray-500">إجمالي النقاط</h3>
                <p class="mt-2 text-3xl font-bold text-indigo-600"><?= number_format($summary_stats['total_points'] ?? 0, 2) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-medium text-gray-500">إجمالي المكالمات</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($summary_stats['total_calls'] ?? 0) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-medium text-gray-500">التذاكر العادية</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($summary_stats['normal_tickets'] ?? 0) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-medium text-gray-500">تذاكر VIP</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($summary_stats['vip_tickets'] ?? 0) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-medium text-gray-500">إجمالي التحويلات</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($summary_stats['assignments_count'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الدور</label>
                        <select name="role_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">جميع الأدوار</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= ($filters['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الموظف</label>
                        <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">جميع الموظفين</option>
                            <?php foreach ($all_users as $user_filter): ?>
                                <option value="<?= $user_filter['id'] ?>" <?= ($filters['user_id'] ?? '') == $user_filter['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user_filter['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">جميع الحالات</option>
                            <option value="active" <?= ($filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>نشط</option>
                            <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>معلق</option>
                            <option value="banned" <?= ($filters['status'] ?? '') == 'banned' ? 'selected' : '' ?>>محظور</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                        <input type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                        <input type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 flex items-center justify-end space-x-2 space-x-reverse">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 flex items-center">
                        <i class="fas fa-search ml-2"></i>
                        بحث
                    </button>
                    <a href="<?= BASE_PATH ?>/reports/users/export?<?= http_build_query($filters) ?>" 
                       class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center">
                        <i class="fas fa-file-excel ml-2"></i>
                        تصدير Excel
                    </a>
                    <a href="<?= BASE_PATH ?>/reports/users" 
                       class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center">
                        <i class="fas fa-times ml-2"></i>
                        الغاء الفلتر
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">البريد الإلكتروني</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدور</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">متصل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي المكالمات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التذاكر العادية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تذاكر VIP</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التحويلات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النقاط النهائية</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                لا توجد بيانات لعرضها تطابق معايير البحث.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($user['role_name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                switch($user['status']) {
                                    case 'active':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'pending':
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'banned':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                }
                                ?>">
                                <?= $user['status'] === 'active' ? 'نشط' : ($user['status'] === 'pending' ? 'معلق' : 'محظور') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $user['is_online'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $user['is_online'] ? 'متصل' : 'غير متصل' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($user['call_stats']['total_calls'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($user['normal_tickets'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($user['vip_tickets'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($user['assignments_count'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                            <div class="flex items-center">
                                <span class="text-indigo-600"><?= number_format($user['points_details']['final_total_points'] ?? 0, 2) ?></span>
                                <?php if (!empty($user['points_details']['bonus_reasons'])): ?>
                                    <div x-data="{ tooltip: false }" class="relative z-10 ml-2">
                                        <i @mouseenter="tooltip = true" @mouseleave="tooltip = false" class="fas fa-info-circle text-blue-500 cursor-pointer"></i>
                                        <div x-show="tooltip" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                                             x-transition:enter-end="opacity-100 transform translate-y-0"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 transform translate-y-0"
                                             x-transition:leave-end="opacity-0 transform -translate-y-2"
                                             class="absolute z-20 bottom-full left-1/2 -translate-x-1/2 mb-2 p-3 w-max max-w-sm bg-gray-800 text-white text-sm rounded-lg shadow-xl"
                                             style="display: none;">
                                            <h4 class="font-bold border-b border-gray-600 pb-2 mb-2">أسباب البونص:</h4>
                                            <ul class="list-disc list-inside space-y-1">
                                                <?php foreach($user['points_details']['bonus_reasons'] as $reason): ?>
                                                    <li><?= htmlspecialchars($reason) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="mt-2 border-t border-gray-600 pt-2 text-xs">
                                                <div class="flex justify-between">
                                                    <span>النقاط الأساسية:</span>
                                                    <span><?= number_format($user['points_details']['total_base_points'] ?? 0, 2) ?></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>نقاط البونص:</span>
                                                    <span class="text-green-400">+<?= number_format($user['points_details']['total_bonus_amount'] ?? 0, 2) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th colspan="5" class="px-6 py-3 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">
                            الإجمالي
                        </th>
                        <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">
                            <?= number_format($summary_stats['total_calls'] ?? 0) ?>
                        </th>
                        <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">
                            <?= number_format($summary_stats['normal_tickets'] ?? 0) ?>
                        </th>
                        <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">
                            <?= number_format($summary_stats['vip_tickets'] ?? 0) ?>
                        </th>
                        <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">
                             <?= number_format($summary_stats['assignments_count'] ?? 0) ?>
                        </th>
                        <th class="px-6 py-3 text-right text-sm font-bold text-indigo-700">
                            <?= number_format($summary_stats['total_points'] ?? 0, 2) ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html> 