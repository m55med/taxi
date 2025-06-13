<?php require_once APPROOT . '/app/views/inc/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($page_main_title); ?></h1>

    <?php if ($user_role === 'marketer'): ?>
    <div class="mb-6 bg-white p-5 rounded-lg shadow-sm border border-gray-200" x-data="{ link: '<?php echo htmlspecialchars($referral_link); ?>' }">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">رابط التسويق الخاص بك</h2>
        <div class="flex items-center space-x-2 space-x-reverse">
            <input type="text" :value="link" readonly class="flex-grow bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
            <button @click="navigator.clipboard.writeText(link); alert('تم نسخ الرابط!');" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-300 ease-in-out text-sm font-medium">
                نسخ
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-blue-500">
            <h3 class="text-sm font-medium text-gray-500">إجمالي الزيارات</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_visits']); ?></p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-green-500">
            <h3 class="text-sm font-medium text-gray-500">إجمالي التسجيلات الناجحة</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_registrations']); ?></p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-yellow-500">
            <h3 class="text-sm font-medium text-gray-500">نسبة التحويل</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stats['conversion_rate']; ?>%</p>
        </div>
    </div>

    <!-- Detailed Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- By Source -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">أهم مصادر الزيارات</h3>
            <ul class="space-y-2 text-sm">
                <?php foreach($dashboardStats['by_source'] as $item): ?>
                    <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['item']); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
                 <?php if (empty($dashboardStats['by_source'])): ?>
                    <p class="text-gray-500">لا توجد بيانات.</p>
                <?php endif; ?>
            </ul>
        </div>

        <!-- By Country -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">أهم الدول</h3>
            <ul class="space-y-2 text-sm">
                <?php foreach($dashboardStats['by_country'] as $item): ?>
                     <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['item']); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
                 <?php if (empty($dashboardStats['by_country'])): ?>
                    <p class="text-gray-500">لا توجد بيانات.</p>
                <?php endif; ?>
            </ul>
        </div>

        <!-- By Device Type -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">أنواع الأجهزة</h3>
            <ul class="space-y-2 text-sm">
                <?php foreach($dashboardStats['by_device'] as $item): ?>
                     <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['item']); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
                 <?php if (empty($dashboardStats['by_device'])): ?>
                    <p class="text-gray-500">لا توجد بيانات.</p>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- By Browser -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">أهم المتصفحات</h3>
            <ul class="space-y-2 text-sm">
                <?php foreach($dashboardStats['by_browser'] as $item): ?>
                     <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['item']); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
                 <?php if (empty($dashboardStats['by_browser'])): ?>
                    <p class="text-gray-500">لا توجد بيانات.</p>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- By OS -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">أنظمة التشغيل</h3>
            <ul class="space-y-2 text-sm">
                <?php foreach($dashboardStats['by_os'] as $item): ?>
                     <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['item']); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
                 <?php if (empty($dashboardStats['by_os'])): ?>
                    <p class="text-gray-500">لا توجد بيانات.</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="" method="GET" class="flex flex-wrap items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700 mr-4">الفلاتر:</h3>
            <?php if ($user_role === 'admin'): ?>
            <div class="flex-grow md:flex-grow-0">
                <label for="marketer_id" class="sr-only">المسوق</label>
                <select name="marketer_id" id="marketer_id" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">كل المسوقين</option>
                    <?php foreach ($marketers as $marketer): ?>
                        <option value="<?php echo $marketer['id']; ?>" <?php echo (isset($filters['marketer_id']) && $filters['marketer_id'] == $marketer['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($marketer['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="flex-grow md:flex-grow-0">
                <label for="date_from" class="sr-only">من تاريخ</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-grow md:flex-grow-0">
                <label for="date_to" class="sr-only">إلى تاريخ</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-center space-x-2 space-x-reverse">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">تطبيق</button>
                <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-300 text-sm font-medium">مسح</a>
            </div>
        </form>
    </div>

    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">سجل الزيارات والتسجيلات</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">التاريخ</th>
                        <?php if ($user_role === 'admin'): ?>
                        <th scope="col" class="px-4 py-3">المسوق</th>
                        <?php endif; ?>
                        <th scope="col" class="px-4 py-3">IP / الدولة</th>
                        <th scope="col" class="px-4 py-3">الجهاز / المتصفح</th>
                        <th scope="col" class="px-4 py-3">المصدر</th>
                        <th scope="col" class="px-4 py-3">حالة التسجيل</th>
                        <th scope="col" class="px-4 py-3">السائق المسجل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visits)): ?>
                        <tr>
                            <td colspan="<?php echo ($user_role === 'admin') ? '7' : '6'; ?>" class="px-6 py-4 text-center">لا توجد بيانات تطابق الفلاتر المحددة.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($visits as $visit): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($visit['visit_recorded_at']))); ?></td>
                            <?php if ($user_role === 'admin'): ?>
                            <td class="px-4 py-4"><?php echo htmlspecialchars($visit['affiliate_name'] ?? 'تسجيل مباشر'); ?></td>
                            <?php endif; ?>
                            <td class="px-4 py-4">
                                <span class="block"><?php echo htmlspecialchars($visit['ip_address']); ?></span>
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($visit['country'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="block"><?php echo htmlspecialchars($visit['device_type'] ?? 'N/A'); ?></span>
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($visit['browser_name'] ?? 'N/A'); ?> / <?php echo htmlspecialchars($visit['operating_system'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <?php
                                    $referer = $visit['referer_url'];
                                    $source = 'Direct';
                                    if (!empty($referer)) {
                                        $host = parse_url($referer, PHP_URL_HOST);
                                        $source = str_replace('www.', '', $host);
                                    }
                                    echo htmlspecialchars($source);
                                ?>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs
                                <?php 
                                    switch ($visit['registration_status']) {
                                        case 'successful': echo 'bg-green-100 text-green-800'; break;
                                        case 'duplicate_phone': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'failed_other': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-200 text-gray-800';
                                    }
                                ?>">
                                    <?php echo htmlspecialchars($visit['registration_status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-4"><?php echo htmlspecialchars($visit['driver_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/app/views/inc/footer.php'; ?> 