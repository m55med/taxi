<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($data['page_main_title']) ?></h1>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>
    <?php include APPROOT . '/views/referral/dashboard/_reports.php'; ?>
    <!-- Marketers Table -->
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">كل المسوقين</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">اسم المستخدم</th>
                        <th scope="col" class="px-4 py-3">الزيارات</th>
                        <th scope="col" class="px-4 py-3">تسجيلات السائقين</th>
                        <th scope="col" class="px-4 py-3">تسجيلات المطاعم</th>
                        <th scope="col" class="px-4 py-3">نسبة التحويل</th>
                        <th scope="col" class="px-4 py-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['marketers'])): ?>
                        <tr><td colspan="6" class="px-6 py-4 text-center">لم يتم العثور على مسوقين.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['marketers'] as $marketer): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($marketer['username']) ?></td>
                            <td class="px-4 py-4"><?= number_format($marketer['total_visits'] ?? 0) ?></td>
                            <td class="px-4 py-4"><?= number_format($marketer['total_registrations'] ?? 0) ?></td>
                            <td class="px-4 py-4"><?= number_format($marketer['total_restaurants'] ?? 0) ?></td>
                            <td class="px-4 py-4"><?= number_format($marketer['conversion_rate'] ?? 0, 2) ?>%</td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <a href="<?= URLROOT ?>/referral/marketerDetails/<?= $marketer['id'] ?>" class="font-medium text-blue-600 hover:underline mr-4">تفاصيل</a>
                                <a href="<?= URLROOT ?>/referral/editProfile/<?= $marketer['id'] ?>" class="font-medium text-green-600 hover:underline">تعديل</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>



</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 