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
                <select name="coupon_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الكوبونات</option>
                     <?php foreach($data['coupons'] as $coupon): ?>
                        <option value="<?= $coupon['id'] ?>" <?= ($data['filters']['coupon_id'] ?? '') == $coupon['id'] ? 'selected' : '' ?>><?= htmlspecialchars($coupon['code']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="ticket_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل التذاكر</option>
                     <?php foreach($data['tickets'] as $ticket): ?>
                        <option value="<?= $ticket['id'] ?>" <?= ($data['filters']['ticket_id'] ?? '') == $ticket['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ticket['ticket_number']) ?></option>
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">كود الكوبون</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">أضيف بواسطة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['ticket_coupons'] as $tc): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($tc['ticket_number']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($tc['coupon_code']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($tc['created_by_user']) ?></td>
                        <td class="px-6 py-4"><?= date('Y-m-d H:i', strtotime($tc['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 