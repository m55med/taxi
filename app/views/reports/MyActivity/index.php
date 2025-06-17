<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= $data['title'] ?></h1>
                <p class="text-gray-600">مراجعة لنشاطك الشخصي ومقاييس الأداء.</p>
            </div>
            <div>
                 <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" 
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                     <i class="fas fa-file-excel ml-1"></i>
                     تصدير Excel
                 </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex items-end space-x-2 space-x-reverse">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        <i class="fas fa-search ml-1"></i>
                        بحث
                    </button>
                    <a href="<?= BASE_PATH ?>/reports/myactivity" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">إعادة تعيين</a>
                </div>
            </form>
        </div>

        <!-- Summary Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600 text-sm font-medium">إجمالي المكالمات</h3>
                <p class="text-3xl font-bold text-indigo-600"><?= $data['summary']['total_calls'] ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600 text-sm font-medium">إجمالي التذاكر</h3>
                <p class="text-3xl font-bold text-indigo-600"><?= $data['summary']['total_tickets'] ?></p>
            </div>
        </div>

        <!-- Detailed Tables -->
        <div>
            <!-- Calls Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <h2 class="text-xl font-semibold text-gray-800 p-6">تفاصيل المكالمات</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السائق</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة المكالمة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ المكالمة</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($data['calls'] as $call): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($call['driver_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($call['call_status']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($call['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($data['calls'])): ?>
                                <tr><td colspan="3" class="text-center py-4">لا توجد بيانات</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <h2 class="text-xl font-semibold text-gray-800 p-6">تفاصيل التذاكر</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم التذكرة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">VIP</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنصة</th>
                                 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التصنيف</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإنشاء</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($data['tickets'] as $ticket): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($ticket['ticket_number']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $ticket['is_vip'] ? 'نعم' : 'لا' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($ticket['platform_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($ticket['category_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($ticket['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                             <?php if (empty($data['tickets'])): ?>
                                <tr><td colspan="5" class="text-center py-4">لا توجد بيانات</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 