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
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <select name="referrer_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل المُحيلين</option>
                    <?php foreach($data['referrers'] as $referrer): ?>
                        <option value="<?= $referrer['id'] ?>" <?= ($data['filters']['referrer_id'] ?? '') == $referrer['id'] ? 'selected' : '' ?>><?= htmlspecialchars($referrer['username']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">بحث</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم السائق</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">هاتف السائق</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم المُحيل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">كود الإحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['registrations'] as $registration): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($registration['driver_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($registration['driver_phone']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($registration['referrer_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($registration['referral_code']) ?></td>
                        <td class="px-6 py-4"><?= date('Y-m-d', strtotime($registration['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 