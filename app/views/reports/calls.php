<?php require_once APPROOT . '/views/inc/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">تقرير المكالمات</h2>
                <p class="mt-1 text-sm text-gray-600">عرض وتحليل بيانات المكالمات</p>
            </div>
            <div>
                <a href="<?= BASE_PATH ?>/reports/calls/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-export ml-1"></i>
                    تصدير التقرير
                </a>
            </div>
        </div>

        <!-- Call Stats -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">إحصائيات المكالمات</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-phone-alt text-blue-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">إجمالي المكالمات</h4>
                            <p class="text-2xl font-semibold text-blue-600"><?= $data['total_calls'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">المكالمات الناجحة</h4>
                            <p class="text-2xl font-semibold text-green-600"><?= $data['successful_calls'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">متوسط مدة المكالمة</h4>
                            <p class="text-2xl font-semibold text-yellow-600"><?= $data['avg_duration'] ?? '0:00' ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">المكالمات الفاشلة</h4>
                            <p class="text-2xl font-semibold text-red-600"><?= $data['failed_calls'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">تصفية النتائج</h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">حالة المكالمة</label>
                    <select name="status" class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">الكل</option>
                        <option value="completed" <?= isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : '' ?>>مكتملة</option>
                        <option value="no_answer" <?= isset($_GET['status']) && $_GET['status'] == 'no_answer' ? 'selected' : '' ?>>لا إجابة</option>
                        <option value="busy" <?= isset($_GET['status']) && $_GET['status'] == 'busy' ? 'selected' : '' ?>>مشغول</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">من تاريخ</label>
                    <input type="date" name="date_from" value="<?= isset($_GET['date_from']) ? $_GET['date_from'] : '' ?>" 
                           class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">إلى تاريخ</label>
                    <input type="date" name="date_to" value="<?= isset($_GET['date_to']) ? $_GET['date_to'] : '' ?>" 
                           class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-filter ml-1"></i>
                        تصفية
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم المكالمة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الهاتف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ والوقت</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($data['calls'] as $call): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($call['call_id']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($call['phone_number']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $call['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($call['status'] == 'no_answer' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= $call['status'] == 'completed' ? 'مكتملة' : 
                                    ($call['status'] == 'no_answer' ? 'لا إجابة' : 'مشغول') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $call['duration'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($call['staff_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('Y-m-d H:i', strtotime($call['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/inc/footer.php'; ?> 