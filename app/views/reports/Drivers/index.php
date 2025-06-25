<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير السائقين</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<?php include __DIR__ . '/../../includes/nav.php'; ?>


<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">تقرير السائقين</h2>
                <p class="mt-1 text-sm text-gray-600">عرض وتحليل بيانات السائقين</p>
            </div>
            <div>
                <a href="<?= BASE_PATH ?>/reports/drivers/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-export ml-1"></i>
                    تصدير التقرير
                </a>
            </div>
        </div>

        <!-- Enhanced Statistics Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">إحصائيات السائقين</h3>
            
            <!-- Main Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">إجمالي السائقين</p>
                            <h4 class="text-xl font-bold text-gray-800"><?= number_format($data['total_drivers'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">السائقين النشطين</p>
                            <h4 class="text-xl font-bold text-gray-800"><?= number_format($data['active_drivers'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">قيد الانتظار</p>
                            <h4 class="text-xl font-bold text-gray-800"><?= number_format($data['pending_drivers'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-ban text-red-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">المحظورين</p>
                            <h4 class="text-xl font-bold text-gray-800"><?= number_format($data['banned_drivers'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-gray-100 rounded-full">
                            <i class="fas fa-pause-circle text-gray-600"></i>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm text-gray-600">يجرى الاتصال بهم حاليا</p>
                            <h4 class="text-xl font-bold text-gray-800"><?= number_format($data['on_hold_drivers'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Document Status -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-file-alt text-purple-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">حالة المستندات</h4>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">مستندات مكتملة</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['complete_docs'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">مستندات ناقصة</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['missing_docs'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">نسبة الاكتمال</span>
                            <span class="font-semibold text-green-600"><?= number_format($data['docs_completion_rate'] ?? 0, 1) ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Registration Sources -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <i class="fas fa-chart-pie text-indigo-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">مصادر التسجيل</h4>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">النموذج المباشر</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['source_form'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">الإحالات</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['source_referral'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">تيليجرام</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['source_telegram'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">الموظفين</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['source_staff'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Processing Status -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-orange-100 rounded-full">
                            <i class="fas fa-tasks text-orange-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">حالة المعالجة</h4>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">بانتظار المحادثة</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['waiting_chat'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">لا يوجد رد</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['no_answer'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">معاد جدولته</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['rescheduled'] ?? 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">إعادة النظر</span>
                            <span class="font-semibold text-gray-800"><?= number_format($data['reconsider'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">تصفية النتائج</h3>
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">حالة السائق</label>
                    <select name="main_system_status" class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">الكل</option>
                    <option value="pending" <?= isset($_GET['main_system_status']) && $_GET['main_system_status'] == 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                    <option value="completed" <?= isset($_GET['main_system_status']) && $_GET['main_system_status'] == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                    <option value="rejected" <?= isset($_GET['main_system_status']) && $_GET['main_system_status'] == 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                </select>
            </div>

            <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">مصدر البيانات</label>
                    <select name="data_source" class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">الكل</option>
                    <option value="call_center" <?= isset($_GET['data_source']) && $_GET['data_source'] == 'call_center' ? 'selected' : '' ?>>مركز الاتصال</option>
                    <option value="website" <?= isset($_GET['data_source']) && $_GET['data_source'] == 'website' ? 'selected' : '' ?>>الموقع الإلكتروني</option>
                    <option value="app" <?= isset($_GET['data_source']) && $_GET['data_source'] == 'app' ? 'selected' : '' ?>>التطبيق</option>
                </select>
            </div>

            <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">المستندات</label>
                    <select name="has_missing_documents" class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">الكل</option>
                    <option value="1" <?= isset($_GET['has_missing_documents']) && $_GET['has_missing_documents'] == '1' ? 'selected' : '' ?>>مستندات ناقصة</option>
                    <option value="0" <?= isset($_GET['has_missing_documents']) && $_GET['has_missing_documents'] == '0' ? 'selected' : '' ?>>مستندات مكتملة</option>
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
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الهاتف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المصدر</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تمت الإضافة بواسطة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($data['drivers'] as $driver): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($driver['name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($driver['phone']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $driver['main_system_status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($driver['main_system_status'] == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= $driver['main_system_status'] == 'completed' ? 'مكتمل' : 
                                    ($driver['main_system_status'] == 'rejected' ? 'مرفوض' : 'قيد الانتظار') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= ucfirst(htmlspecialchars($driver['data_source'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($driver['added_by_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('Y-m-d H:i', strtotime($driver['created_at'])) ?></td>
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
                // To prevent empty filter values from being in the URL
                $filters = array_filter($filters);
                $queryParams = http_build_query(array_merge($filters, ['limit' => $pagination['limit']]));
                $currentPage = $pagination['page'];
                $totalPages = $pagination['total_pages'];

                $startRecord = ($currentPage - 1) * $pagination['limit'] + 1;
                $endRecord = $startRecord + count($data['drivers']) - 1;
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

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>

</rewritten_file>