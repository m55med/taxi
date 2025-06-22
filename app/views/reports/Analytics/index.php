<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير التحليلات</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<?php include __DIR__ . '/../../includes/nav.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">تقرير التحليلات</h2>

        <!-- Filters -->
        <form method="GET" class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <a href="<?= BASE_PATH ?>/reports/analytics/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                    تصدير Excel
                </a>
            </div>
        </form>

        <!-- Conversion Rates -->
        <?php if (!empty($data['conversion_rates'])): ?>
        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4">معدلات التحويل حسب المصدر</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($data['conversion_rates'] as $rate): ?>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h4 class="font-bold mb-2"><?= ucfirst($rate['data_source']) ?></h4>
                    <p>إجمالي السائقين: <?= $rate['total_drivers'] ?></p>
                    <p>السائقين المكتملين: <?= $rate['completed_drivers'] ?></p>
                    <p>معدل التحويل: <?= $rate['conversion_rate'] ?>%</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Call Analysis -->
        <?php if (!empty($data['call_analysis'])): ?>
        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4">تحليل المكالمات</h3>
            
            <!-- Call Outcomes -->
            <div class="mb-8">
                <h4 class="text-lg font-bold mb-4">نتائج المكالمات</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">مهتم</h5>
                        <p class="text-2xl text-green-600"><?= $data['call_analysis']['outcomes']['interested'] ?></p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">غير مهتم</h5>
                        <p class="text-2xl text-red-600"><?= $data['call_analysis']['outcomes']['not_interested'] ?></p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">معاودة الاتصال</h5>
                        <p class="text-2xl text-blue-600"><?= $data['call_analysis']['outcomes']['callback'] ?></p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">لا يوجد رد</h5>
                        <p class="text-2xl text-gray-600"><?= $data['call_analysis']['outcomes']['no_answer'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Call Duration -->
            <div class="mb-8">
                <h4 class="text-lg font-bold mb-4">مدة المكالمات</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">متوسط مدة المكالمة</h5>
                        <p class="text-2xl"><?= round($data['call_analysis']['duration']['average'] / 60, 1) ?> دقيقة</p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">أقصى مدة</h5>
                        <p class="text-2xl"><?= round($data['call_analysis']['duration']['max'] / 60, 1) ?> دقيقة</p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h5 class="font-bold mb-2">أدنى مدة</h5>
                        <p class="text-2xl"><?= round($data['call_analysis']['duration']['min'] / 60, 1) ?> دقيقة</p>
                    </div>
                </div>
            </div>

            <!-- Staff Performance -->
            <div>
                <h4 class="text-lg font-bold mb-4">أداء الموظفين</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    الموظف
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    عدد المكالمات
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    معدل التحويل
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    متوسط مدة المكالمة
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['call_analysis']['staff_performance'] as $staff): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                    <?= $staff['name'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                    <?= $staff['total_calls'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                    <?= $staff['conversion_rate'] ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                    <?= round($staff['avg_duration'] / 60, 1) ?> دقيقة
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APPROOT . '/app/views/inc/footer.php'; ?> 