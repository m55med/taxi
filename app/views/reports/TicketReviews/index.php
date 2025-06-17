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
                <select name="reviewer_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل المراجعين</option>
                    <?php foreach($data['reviewers'] as $reviewer): ?>
                        <option value="<?= $reviewer['id'] ?>" <?= ($data['filters']['reviewer_id'] ?? '') == $reviewer['id'] ? 'selected' : '' ?>><?= htmlspecialchars($reviewer['username']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="agent_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الموظفين</option>
                    <?php foreach($data['agents'] as $agent): ?>
                        <option value="<?= $agent['id'] ?>" <?= ($data['filters']['agent_id'] ?? '') == $agent['id'] ? 'selected' : '' ?>><?= htmlspecialchars($agent['username']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="review_result" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل النتائج</option>
                    <?php foreach(['return_to_agent', 'accepted', 'rejected'] as $result): ?>
                        <option value="<?= $result ?>" <?= ($data['filters']['review_result'] ?? '') == $result ? 'selected' : '' ?>><?= htmlspecialchars($result) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">بحث</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم التذكرة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نتيجة المراجعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الملاحظات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المراجع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ المراجعة</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['reviews'] as $review): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($review['ticket_number']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($review['review_result']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($review['review_notes']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($review['reviewer_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($review['agent_name']) ?></td>
                        <td class="px-6 py-4"><?= date('Y-m-d H:i', strtotime($review['reviewed_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 