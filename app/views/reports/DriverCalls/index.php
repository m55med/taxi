<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= htmlspecialchars($data['title']) ?></h1>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select name="country_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الدول</option>
                    <?php foreach($data['countries'] as $country): ?>
                        <option value="<?= $country['id'] ?>" <?= ($data['filters']['country_id'] ?? '') == $country['id'] ? 'selected' : '' ?>><?= htmlspecialchars($country['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="car_type_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل أنواع السيارات</option>
                     <?php foreach($data['car_types'] as $car_type): ?>
                        <option value="<?= $car_type['id'] ?>" <?= ($data['filters']['car_type_id'] ?? '') == $car_type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($car_type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الحالات</option>
                    <option value="answered" <?= ($data['filters']['status'] ?? '') == 'answered' ? 'selected' : '' ?>>تم الرد</option>
                    <option value="not-answered" <?= ($data['filters']['status'] ?? '') == 'not-answered' ? 'selected' : '' ?>>لم يتم الرد</option>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">بحث</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم السائق</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم المستخدم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الدولة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع السيارة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ المكالمة</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['calls'] as $call): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($call['driver_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($call['user_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($call['country_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($call['car_type_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($call['status']) ?></td>
                        <td class="px-6 py-4"><?= date('Y-m-d H:i', strtotime($call['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 