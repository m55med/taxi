<?php require_once APPROOT . '/views/inc/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">تقرير التحويلات</h2>

        <!-- Filters -->
        <form method="GET" class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">من موظف</label>
                <select name="from_staff_id" class="shadow border rounded w-full py-2 px-3">
                    <option value="">الكل</option>
                    <?php foreach ($data['staff_members'] as $staff): ?>
                    <option value="<?= $staff['id'] ?>" <?= isset($_GET['from_staff_id']) && $_GET['from_staff_id'] == $staff['id'] ? 'selected' : '' ?>>
                        <?= $staff['username'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">إلى موظف</label>
                <select name="to_staff_id" class="shadow border rounded w-full py-2 px-3">
                    <option value="">الكل</option>
                    <?php foreach ($data['staff_members'] as $staff): ?>
                    <option value="<?= $staff['id'] ?>" <?= isset($_GET['to_staff_id']) && $_GET['to_staff_id'] == $staff['id'] ? 'selected' : '' ?>>
                        <?= $staff['username'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">سبب التحويل</label>
                <select name="reason" class="shadow border rounded w-full py-2 px-3">
                    <option value="">الكل</option>
                    <option value="workload" <?= isset($_GET['reason']) && $_GET['reason'] == 'workload' ? 'selected' : '' ?>>عبء العمل</option>
                    <option value="expertise" <?= isset($_GET['reason']) && $_GET['reason'] == 'expertise' ? 'selected' : '' ?>>الخبرة</option>
                    <option value="availability" <?= isset($_GET['reason']) && $_GET['reason'] == 'availability' ? 'selected' : '' ?>>التوفر</option>
                    <option value="other" <?= isset($_GET['reason']) && $_GET['reason'] == 'other' ? 'selected' : '' ?>>أخرى</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">من تاريخ</label>
                <input type="date" name="date_from" value="<?= isset($_GET['date_from']) ? $_GET['date_from'] : '' ?>" class="shadow border rounded w-full py-2 px-3">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">إلى تاريخ</label>
                <input type="date" name="date_to" value="<?= isset($_GET['date_to']) ? $_GET['date_to'] : '' ?>" class="shadow border rounded w-full py-2 px-3">
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    تصفية
                </button>
                <a href="<?= BASE_PATH ?>/reports/assignments/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                    تصدير Excel
                </a>
            </div>
        </form>

        <!-- Summary -->
        <?php if (!empty($data['summary'])): ?>
        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4">ملخص التحويلات</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h4 class="font-bold mb-2">إجمالي التحويلات</h4>
                    <p class="text-2xl"><?= $data['summary']['total_assignments'] ?></p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h4 class="font-bold mb-2">الموظف الأكثر تحويلاً</h4>
                    <p class="text-2xl"><?= $data['summary']['most_active_staff'] ?></p>
                    <p class="text-sm text-gray-600"><?= $data['summary']['most_active_count'] ?> تحويل</p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h4 class="font-bold mb-2">السبب الأكثر شيوعاً</h4>
                    <p class="text-2xl"><?= $data['summary']['most_common_reason'] == 'workload' ? 'عبء العمل' : 
                        ($data['summary']['most_common_reason'] == 'expertise' ? 'الخبرة' : 
                        ($data['summary']['most_common_reason'] == 'availability' ? 'التوفر' : 'أخرى')) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            السائق
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            من موظف
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            إلى موظف
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            تاريخ التحويل
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            السبب
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            ملاحظات
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['assignments'] as $assignment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $assignment['driver_name'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $assignment['from_staff_name'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $assignment['to_staff_name'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= date('Y-m-d H:i', strtotime($assignment['assigned_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= $assignment['reason'] == 'workload' ? 'عبء العمل' : 
                                    ($assignment['reason'] == 'expertise' ? 'الخبرة' : 
                                    ($assignment['reason'] == 'availability' ? 'التوفر' : 'أخرى')) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $assignment['notes'] ?? '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/inc/footer.php'; ?> 