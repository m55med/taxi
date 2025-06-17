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
                <select name="status" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل الحالات</option>
                    <option value="approved" <?= ($data['filters']['status'] ?? '') == 'approved' ? 'selected' : '' ?>>مقبول</option>
                    <option value="rejected" <?= ($data['filters']['status'] ?? '') == 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                    <option value="pending" <?= ($data['filters']['status'] ?? '') == 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                    <option value="expiring_soon" <?= ($data['filters']['status'] ?? '') == 'expiring_soon' ? 'selected' : '' ?>>تنتهي قريباً</option>
                </select>
                <select name="document_type_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">كل أنواع الوثائق</option>
                     <?php foreach($data['document_types'] as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= ($data['filters']['document_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع الوثيقة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الرفع</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($data['documents'] as $doc): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($doc['driver_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($doc['document_type_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($doc['status']) ?></td>
                        <td class="px-6 py-4 text-red-600 font-semibold"><?= $doc['expiry_date'] ? date('Y-m-d', strtotime($doc['expiry_date'])) : 'N/A' ?></td>
                        <td class="px-6 py-4"><?= date('Y-m-d H:i', strtotime($doc['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 