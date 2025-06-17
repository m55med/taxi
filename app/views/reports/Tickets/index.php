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
                <select name="status" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الحالات</option>
                    <?php foreach($data['statuses'] as $status): ?>
                        <option value="<?= $status ?>" <?= ($data['filters']['status'] ?? '') == $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="priority" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الأولويات</option>
                    <?php foreach($data['priorities'] as $priority): ?>
                        <option value="<?= $priority ?>" <?= ($data['filters']['priority'] ?? '') == $priority ? 'selected' : '' ?>><?= htmlspecialchars($priority) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="user_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل المستخدمين</option>
                     <?php foreach($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($data['filters']['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">بحث</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الكود</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المنصة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">العميل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المُنشئ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الفئة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الإنشاء</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['tickets'] as $ticket): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($ticket['code_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($ticket['platform_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($ticket['phone'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($ticket['created_by_user'] ?? 'غير معروف') ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($ticket['category_name']) ?></td>
                        <td class="px-6 py-4"><?= date('Y-m-d H:i', strtotime($ticket['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 