<!-- Filters -->

<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-center">
        <h3 class="text-lg font-semibold text-gray-700 lg:col-span-1">الفلاتر:</h3>

        <?php if (($user_role ?? '') === 'admin'): ?>
        <div class="flex-grow">
            <label for="marketer_id" class="sr-only">المسوق</label>
            <select name="marketer_id" id="marketer_id" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">كل المسوقين</option>
                <?php foreach (($marketers ?? []) as $marketer): ?>
                    <option value="<?= $marketer['id'] ?>" <?= (isset($filters['marketer_id']) && $filters['marketer_id'] == $marketer['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($marketer['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div>
            <label for="registration_status" class="sr-only">حالة التسجيل</label>
            <select name="registration_status" id="registration_status" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">كل الحالات</option>
                <option value="successful" <?= (isset($filters['registration_status']) && $filters['registration_status'] == 'successful') ? 'selected' : '' ?>>ناجح</option>
                <option value="duplicate_phone" <?= (isset($filters['registration_status']) && $filters['registration_status'] == 'duplicate_phone') ? 'selected' : '' ?>>مكرر</option>
                <option value="attempted" <?= (isset($filters['registration_status']) && $filters['registration_status'] == 'attempted') ? 'selected' : '' ?>>محاولة</option>
                <option value="visit_only" <?= (isset($filters['registration_status']) && $filters['registration_status'] == 'visit_only') ? 'selected' : '' ?>>زيارة فقط</option>
            </select>
        </div>

        <div>
            <label for="device_type" class="sr-only">نوع الجهاز</label>
            <select name="device_type" id="device_type" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">كل الأجهزة</option>
                <option value="Desktop" <?= (isset($filters['device_type']) && $filters['device_type'] == 'Desktop') ? 'selected' : '' ?>>كمبيوتر</option>
                <option value="Mobile" <?= (isset($filters['device_type']) && $filters['device_type'] == 'Mobile') ? 'selected' : '' ?>>هاتف</option>
                <option value="Tablet" <?= (isset($filters['device_type']) && $filters['device_type'] == 'Tablet') ? 'selected' : '' ?>>جهاز لوحي</option>
            </select>
        </div>
        
        <div class="flex-grow">
            <label for="start_date" class="sr-only">من تاريخ</label>
            <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex-grow">
            <label for="end_date" class="sr-only">إلى تاريخ</label>
            <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center space-x-2 space-x-reverse lg:col-start-5">
            <button type="submit" class="w-full bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">تطبيق</button>
            <a href="<?= URLROOT ?>/referral/dashboard" class="w-full text-center bg-gray-200 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-300 text-sm font-medium">مسح</a>
        </div>
    </form>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php if (($user_role ?? '') === 'admin'): ?>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-indigo-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي المسوقين</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= htmlspecialchars($summary_stats['total_marketers'] ?? 0) ?></p>
    </div>
    <?php endif; ?>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي الزيارات</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($dashboardStats['total_visits'] ?? 0); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي التسجيلات</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($dashboardStats['total_registrations'] ?? 0); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
        <h3 class="text-sm font-medium text-gray-500">نسبة التحويل</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($dashboardStats['conversion_rate'] ?? 0, 2); ?>%</p>
    </div>
</div>

<!-- Restaurant Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي زيارات المطاعم</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($restaurantStats['total_visits'] ?? 0); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي تسجيلات المطاعم</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($restaurantStats['total_registrations'] ?? 0); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-pink-500">
        <h3 class="text-sm font-medium text-gray-500">نسبة تحويل المطاعم</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($restaurantStats['conversion_rate'] ?? 0, 2); ?>%</p>
    </div>
</div>

<!-- Captain Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-cyan-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي زيارات الكباتن</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($dashboardStats['total_driver_visits'] ?? 0); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-teal-500">
        <h3 class="text-sm font-medium text-gray-500">إجمالي تسجيلات الكباتن</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format($dashboardStats['total_driver_registrations'] ?? 0); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-lime-500">
        <h3 class="text-sm font-medium text-gray-500">نسبة تحويل الكباتن</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900"><?= number_format(($dashboardStats['total_driver_visits'] > 0 ? ($dashboardStats['total_driver_registrations'] / $dashboardStats['total_driver_visits']) * 100 : 0), 2); ?>%</p>
    </div>
</div>

<!-- Detailed Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">أهم مصادر الزيارات</h3>
        <ul class="space-y-2 text-sm">
            <?php if (empty($dashboardStats['top_referers'])): ?>
                <p class="text-gray-500">لا توجد بيانات.</p>
            <?php else: ?>
                <?php foreach(($dashboardStats['top_referers'] ?? []) as $item): ?>
                    <li class="flex justify-between items-center text-gray-600">
                        <span><?php $host = parse_url($item['referer_url'] ?? '', PHP_URL_HOST); echo htmlspecialchars($host ? str_replace('www.', '', $host) : 'مباشر'); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">أهم الدول</h3>
        <ul class="space-y-2 text-sm">
            <?php if (empty($dashboardStats['top_countries'])): ?>
                <p class="text-gray-500">لا توجد بيانات.</p>
            <?php else: ?>
                <?php foreach(($dashboardStats['top_countries'] ?? []) as $item): ?>
                        <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['country'] ?? 'N/A'); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">أنواع الأجهزة</h3>
        <ul class="space-y-2 text-sm">
            <?php if (empty($dashboardStats['top_device_types'])): ?>
                <p class="text-gray-500">لا توجد بيانات.</p>
            <?php else: ?>
                <?php foreach(($dashboardStats['top_device_types'] ?? []) as $item): ?>
                        <li class="flex justify-between items-center text-gray-600">
                        <span><?php echo htmlspecialchars($item['device_type']); ?></span>
                        <span class="font-bold bg-gray-100 px-2 py-1 rounded"><?php echo number_format($item['count']); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Visits Table -->
<div class="bg-white p-4 sm:p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">سجل الزيارات والتسجيلات</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                    <th scope="col" class="px-4 py-3">التاريخ</th>
                    <?php if (($user_role ?? '') === 'admin'): ?><th scope="col" class="px-4 py-3">المسوق</th><?php endif; ?>
                    <th scope="col" class="px-4 py-3">IP / الدولة</th>
                    <th scope="col" class="px-4 py-3">الجهاز / المتصفح</th>
                    <th scope="col" class="px-4 py-3">المصدر</th>
                    <th scope="col" class="px-4 py-3">حالة التسجيل</th>
                    <th scope="col" class="px-4 py-3">السائق المسجل</th>
                    <th scope="col" class="px-4 py-3">نوع الإحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visits)): ?>
                    <tr><td colspan="<?php echo (($user_role ?? '') === 'admin') ? '8' : '7'; ?>" class="px-6 py-4 text-center">لا توجد بيانات تطابق الفلاتر المحددة.</td></tr>
                <?php else: ?>
                    <?php foreach ($visits as $visit): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($visit['visit_recorded_at']))); ?></td>
                        <?php if (($user_role ?? '') === 'admin'): ?><td class="px-4 py-4"><?php echo htmlspecialchars($visit['affiliate_name'] ?? 'تسجيل مباشر'); ?></td><?php endif; ?>
                        <td class="px-4 py-4"><span class="block"><?php echo htmlspecialchars($visit['ip_address']); ?></span><span class="text-xs text-gray-500"><?php echo htmlspecialchars($visit['country'] ?? 'N/A'); ?></span></td>
                        <td class="px-4 py-4"><span class="block"><?php echo htmlspecialchars($visit['device_type'] ?? 'N/A'); ?></span><span class="text-xs text-gray-500"><?php echo htmlspecialchars($visit['browser_name'] ?? 'N/A'); ?></span></td>
                        <td class="px-4 py-4"><?php $referer = $visit['referer_url'] ?? ''; $source = 'Direct'; if (!empty($referer)) { $host = parse_url($referer, PHP_URL_HOST); if($host) { $source = str_replace('www.', '', $host); } } echo htmlspecialchars($source); ?></td>
                        <td class="px-4 py-4"><span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs <?php switch ($visit['registration_status']) { case 'successful': echo 'bg-green-100 text-green-800'; break; case 'duplicate_phone': echo 'bg-yellow-100 text-yellow-800'; break; case 'failed_other': echo 'bg-red-100 text-red-800'; break; default: echo 'bg-gray-200 text-gray-800'; } ?>"><?php echo htmlspecialchars($visit['registration_status']); ?></span></td>
                        <td class="px-4 py-4"><?php echo htmlspecialchars($visit['driver_name'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs
                                <?php echo !empty($visit['registered_driver_id']) ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                                <?php echo !empty($visit['registered_driver_id']) ? 'كابتن' : 'مطعم'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 