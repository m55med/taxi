<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المكالمات</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<?php include __DIR__ . '/../../includes/nav.php'; ?>


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
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
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

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">عدد النتائج</label>
                    <select name="limit" class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="25" <?= ($data['pagination']['limit'] ?? 25) == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($data['pagination']['limit'] ?? 25) == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($data['pagination']['limit'] ?? 25) == 100 ? 'selected' : '' ?>>100</option>
                        <option value="500" <?= ($data['pagination']['limit'] ?? 25) == 500 ? 'selected' : '' ?>>500</option>
                    </select>
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

        <!-- Pagination -->
        <div class="mt-6">
        <?php
            $pagination = $data['pagination'];
            if ($pagination['total_records'] > 0) {
                $filters = $data['filters'];
                $filters = array_filter($filters);
                $queryParams = http_build_query(array_merge($filters, ['limit' => $pagination['limit']]));
                $currentPage = $pagination['page'];
                $totalPages = $pagination['total_pages'];

                $startRecord = ($currentPage - 1) * $pagination['limit'] + 1;
                $endRecord = $startRecord + count($data['calls']) - 1;
        ?>
            <div class="flex flex-col sm:flex-row items-center justify-between">
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    عرض <span class="font-medium"><?= $startRecord ?></span> إلى <span class="font-medium"><?= $endRecord ?></span> من <span class="font-medium"><?= $pagination['total_records'] ?></span> نتيجة
                </div>
                
                <?php if ($totalPages > 1): ?>
                <nav class="flex items-center space-x-1 space-x-reverse">
                    <a href="?page=1&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage == 1 ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-double-right"></i></a>
                    <a href="?page=<?= max(1, $currentPage - 1) ?>&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage <= 1 ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-right"></i></a>
                    
                    <?php
                        $window = 1;
                        if ($totalPages > 10) {
                             if($currentPage > $window + 2) {
                                echo '<a href="?page=1&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium bg-white text-gray-600 hover:bg-gray-50">1</a>';
                                if($currentPage > $window + 3) {
                                    echo '<span class="px-4 py-2 text-sm font-medium text-gray-500">...</span>';
                                }
                            }

                            for ($i = max(1, $currentPage - $window); $i <= min($totalPages, $currentPage + $window); $i++) {
                                echo '<a href="?page='.$i.'&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium '.($i == $currentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50').'">'.$i.'</a>';
                            }
                            
                            if($currentPage < $totalPages - ($window + 2)) {
                                if($currentPage < $totalPages - ($window + 3)) {
                                     echo '<span class="px-4 py-2 text-sm font-medium text-gray-500">...</span>';
                                }
                                echo '<a href="?page='.$totalPages.'&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium bg-white text-gray-600 hover:bg-gray-50">'.$totalPages.'</a>';
                            }
                        } else {
                             for ($i = 1; $i <= $totalPages; $i++) {
                                echo '<a href="?page='.$i.'&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium '.($i == $currentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50').'">'.$i.'</a>';
                            }
                        }
                    ?>

                    <a href="?page=<?= min($totalPages, $currentPage + 1) ?>&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage >= $totalPages ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-left"></i></a>
                    <a href="?page=<?= $totalPages ?>&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage == $totalPages ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-double-left"></i></a>
                </nav>
                <?php endif; ?>
            </div>
        <?php } ?>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/app/views/inc/footer.php'; ?>

</rewritten_file>