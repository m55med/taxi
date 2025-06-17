<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'تقرير أداء الفريق' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= $data['title'] ?? 'تقرير أداء الفريق' ?></h1>

        <?php if (isset($data['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($data['error']) ?></span>
            </div>
        <?php else: ?>
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    
                    <?php if (!empty($data['all_teams'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اختر فريق</label>
                        <select name="team_id" onchange="this.form.submit()" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <?php foreach($data['all_teams'] as $team_item): ?>
                                <option value="<?= $team_item['id'] ?>" <?= (isset($data['team']['id']) && $data['team']['id'] == $team_item['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team_item['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عضو الفريق</label>
                        <select name="member_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">كل الأعضاء</option>
                            <?php foreach($data['members'] as $member): ?>
                                <option value="<?= $member['user_id'] ?>" <?= (isset($data['filters']['member_id']) && $data['filters']['member_id'] == $member['user_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="flex items-end space-x-2 space-x-reverse">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">بحث</button>
                        <a href="<?= BASE_PATH ?>/reports/teamperformance" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">إعادة تعيين</a>
                    </div>
                </form>
            </div>

            <!-- Summary Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-gray-600">إجمالي المكالمات</h3><p class="text-3xl font-bold text-indigo-600"><?= $data['summary']['total_calls'] ?></p></div>
                <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-gray-600">إجمالي التذاكر</h3><p class="text-3xl font-bold text-indigo-600"><?= $data['summary']['total_tickets'] ?></p></div>
                <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-gray-600">عدد الأعضاء</h3><p class="text-3xl font-bold text-indigo-600"><?= $data['summary']['member_count'] ?></p></div>
            </div>

            <!-- Members Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <h2 class="text-xl font-semibold text-gray-800 p-6">أداء الأعضاء</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">العضو</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجمالي المكالمات</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجمالي التذاكر</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($data['members'] as $member): ?>
                        <tr>
                            <td class="px-6 py-4"><?= htmlspecialchars($member['username']) ?></td>
                            <td class="px-6 py-4"><?= $member['total_calls'] ?></td>
                            <td class="px-6 py-4"><?= $member['total_tickets'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 