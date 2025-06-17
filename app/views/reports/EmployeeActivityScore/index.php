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
                <select name="team_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الفرق</option>
                     <?php foreach($data['teams'] as $team): ?>
                        <option value="<?= $team['id'] ?>" <?= ($data['filters']['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= htmlspecialchars($team['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date_from" value="<?= $data['filters']['date_from'] ?? '' ?>" class="w-full px-4 py-2 border rounded-md" placeholder="من تاريخ">
                <input type="date" name="date_to" value="<?= $data['filters']['date_to'] ?? '' ?>" class="w-full px-4 py-2 border rounded-md" placeholder="إلى تاريخ">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">بحث</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الفريق</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المكالمات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التذاكر المنشأة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التذاكر المراجعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجمالي النقاط</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['scores'] as $score): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($score['username']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($score['team_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($score['calls_made']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($score['tickets_created']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($score['tickets_reviewed']) ?></td>
                        <td class="px-6 py-4 font-bold text-green-600"><?= htmlspecialchars($score['activity_score']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 