
<?php require_once APPROOT . '/views/includes/header.php'; ?>
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <div>
            <a href="<?= URLROOT ?>/referral/editProfile/<?= $data['marketer']['id'] ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium mr-2">تعديل الملف الشخصي</a>
            <a href="<?= URLROOT ?>/referral/dashboard" class="text-sm text-blue-600 hover:underline">&larr; العودة إلى لوحة التحكم</a>
        </div>
    </div>

    <!-- Visits Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 sm:p-6">
            <h2 class="text-xl font-semibold text-gray-700">جميع الزيارات المسجلة</h2>
            <p class="mt-1 text-sm text-gray-500">سجل بكل زيارة تم إنشاؤها بواسطة رابط الإحالة الخاص بهذا المسوق.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان IP</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الموقع</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الجهاز</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">حالة التسجيل</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['visits'])): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">لا توجد زيارات مسجلة بعد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['visits'] as $visit): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('Y-m-d H:i', strtotime($visit['visit_recorded_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono"><?= htmlspecialchars($visit['ip_address']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(implode(', ', array_filter([$visit['city'], $visit['country']]))) ?: 'N/A' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div><?= htmlspecialchars($visit['device_type'] ?? 'N/A') ?></div>
                                    <div class="text-xs"><?= htmlspecialchars($visit['browser_name'] ?? '') ?> على <?= htmlspecialchars($visit['operating_system'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php 
                                            switch ($visit['registration_status']) {
                                                case 'successful': echo 'bg-green-100 text-green-800'; break;
                                                case 'duplicate_phone': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'failed_other': echo 'bg-red-100 text-red-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                        ?>">
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $visit['registration_status']))) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Referred Restaurants Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
        <div class="p-4 sm:p-6">
            <h2 class="text-xl font-semibold text-gray-700">المطاعم المسجلة</h2>
            <p class="mt-1 text-sm text-gray-500">قائمة بكل مطعم تم تسجيله بواسطة رابط الإحالة الخاص بهذا المسوق.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم (انجليزي)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم (عربي)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الهاتف</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">العنوان</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['referredRestaurants'])): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">لا توجد مطاعم مسجلة بعد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['referredRestaurants'] as $restaurant): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($restaurant['name_en']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($restaurant['name_ar']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" dir="ltr"><?= htmlspecialchars($restaurant['phone']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($restaurant['address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 