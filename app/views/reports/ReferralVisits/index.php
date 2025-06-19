<?php
// File: /app/views/reports/ReferralVisits/index.php
view('includes/header', ['title' => $data['title']]);
?>

<body class="bg-gray-100">
    <?php view('includes/nav'); ?>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($data['title']) ?></h1>

        <!-- Filters -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">الفلاتر</h2>
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div>
                    <label for="affiliate_name" class="block text-sm font-medium text-gray-700">اسم المسوق</label>
                    <input type="text" name="affiliate_name" id="affiliate_name" value="<?= htmlspecialchars($data['filters']['affiliate_name'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>

                <div>
                    <label for="registration_status" class="block text-sm font-medium text-gray-700">حالة التسجيل</label>
                    <select name="registration_status" id="registration_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">الكل</option>
                        <option value="visit_only" <?= ($data['filters']['registration_status'] ?? '') == 'visit_only' ? 'selected' : '' ?>>مجرد زيارة</option>
                        <option value="form_opened" <?= ($data['filters']['registration_status'] ?? '') == 'form_opened' ? 'selected' : '' ?>>فتح الفورم</option>
                        <option value="attempted" <?= ($data['filters']['registration_status'] ?? '') == 'attempted' ? 'selected' : '' ?>>محاولة تسجيل</option>
                        <option value="successful" <?= ($data['filters']['registration_status'] ?? '') == 'successful' ? 'selected' : '' ?>>تسجيل ناجح</option>
                        <option value="duplicate_phone" <?= ($data['filters']['registration_status'] ?? '') == 'duplicate_phone' ? 'selected' : '' ?>>هاتف مكرر</option>
                        <option value="failed_other" <?= ($data['filters']['registration_status'] ?? '') == 'failed_other' ? 'selected' : '' ?>>فشل آخر</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">من تاريخ</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">إلى تاريخ</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>

                <div class="flex items-end space-x-2 space-x-reverse col-span-full justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter mr-1"></i> تطبيق الفلتر
                    </button>
                    <a href="<?= BASE_PATH ?>/reports/referral-visits" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        <i class="fas fa-eraser mr-1"></i> مسح
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="bg-white p-4 rounded-lg shadow-md overflow-x-auto">
            <table class="table-auto w-full text-sm text-left">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">وقت الزيارة</th>
                        <th class="px-4 py-2">اسم المسوق</th>
                        <th class="px-4 py-2">حالة التسجيل</th>
                        <th class="px-4 py-2">السائق المسجل</th>
                        <th class="px-4 py-2">IP Address</th>
                        <th class="px-4 py-2">User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['visits'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">لا توجد بيانات.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['visits'] as $visit): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['id']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['visit_recorded_at']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['affiliate_user_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['registration_status']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['registered_driver_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['ip_address']) ?></td>
                                <td class="px-4 py-2 text-xs"><?= htmlspecialchars($visit['user_agent']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 