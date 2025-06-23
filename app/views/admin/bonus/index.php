<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منح بونص شهري</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include_once __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">منح بونص شهري للموظفين</h1>

        <?php flash('bonus_message'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Grant Bonus Form -->
            <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">نموذج منح البونص</h2>
                <form action="<?= BASE_PATH ?>/admin/bonus/grant" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">الموظف</label>
                            <select name="user_id" id="user_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">اختر موظف...</option>
                                <?php foreach($data['users'] as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="bonus_month" class="block text-sm font-medium text-gray-700">الشهر المستحق</label>
                            <input type="month" name="bonus_month" id="bonus_month" required value="<?= date('Y-m') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="bonus_percent" class="block text-sm font-medium text-gray-700">نسبة البونص (%)</label>
                            <input type="number" step="0.01" name="bonus_percent" id="bonus_percent" required placeholder="مثال: 5.5" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">السبب (اختياري)</label>
                            <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        <i class="fas fa-check-circle ml-2"></i>منح البونص
                    </button>
                </form>
            </div>

            <!-- Granted Bonuses List -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">سجل البونص الممنوح</h2>
                <div class="max-h-96 overflow-y-auto border rounded-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الموظف</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الشهر</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">النسبة</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">السبب</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">مُنح بواسطة</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             <?php if (empty($data['bonuses'])): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">لم يتم منح أي بونص بعد.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['bonuses'] as $bonus): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900"><?= htmlspecialchars($bonus['username']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-700"><?= $bonus['bonus_year'] . '-' . str_pad($bonus['bonus_month'], 2, '0', STR_PAD_LEFT) ?></td>
                                        <td class="px-4 py-2 text-sm text-green-600 font-bold"><?= $bonus['bonus_percent'] ?>%</td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($bonus['reason'] ?: 'N/A') ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-500"><?= htmlspecialchars($bonus['granter_name'] ?: 'System') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 