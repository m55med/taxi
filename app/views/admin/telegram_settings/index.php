<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        code { padding: 2px 6px; border-radius: 4px; background-color: #f3f4f6; color: #4b5563; font-family: monospace; }
        .table-fixed { table-layout: fixed; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">إعدادات بوت تليجرام</h1>
            <i class="fab fa-telegram-plane text-5xl text-blue-500"></i>
        </div>

        <!-- Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?= $_SESSION['success']; ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?= $_SESSION['error']; ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Form to Add New Link -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">إضافة ربط جديد</h2>
                    <form action="<?= BASE_PATH ?>/admin/telegram_settings/add" method="POST">
                        <div class="mb-4">
                            <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">مستخدم النظام:</label>
                            <select name="user_id" id="user_id" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- اختر مستخدم --</option>
                                <?php foreach ($data['admin_users'] as $admin): ?>
                                    <option value="<?= $admin['id'] ?>"><?= htmlspecialchars($admin['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="telegram_user_id" class="block text-gray-700 text-sm font-bold mb-2">معرف مستخدم تليجرام:</label>
                            <input type="number" name="telegram_user_id" id="telegram_user_id" placeholder="e.g., 123456789" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-6">
                            <label for="telegram_chat_id" class="block text-gray-700 text-sm font-bold mb-2">معرف مجموعة تليجرام:</label>
                            <input type="number" name="telegram_chat_id" id="telegram_chat_id" placeholder="e.g., -100123456789" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <i class="fas fa-plus ml-2"></i> إضافة
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table of Existing Links -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">الروابط الحالية</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-1/4 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">مستخدم النظام</th>
                                    <th class="w-1/4 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">معرف مستخدم تليجرام</th>
                                    <th class="w-1/4 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">معرف المجموعة</th>
                                    <th class="w-1/4 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['current_settings'])): ?>
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">لا توجد روابط محفوظة حاليًا.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data['current_settings'] as $setting): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><i class="fas fa-user text-gray-400 mr-2"></i><?= htmlspecialchars($setting['username']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><code><?= htmlspecialchars($setting['telegram_user_id']) ?></code></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><code><?= htmlspecialchars($setting['telegram_chat_id']) ?></code></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="<?= BASE_PATH ?>/admin/telegram_settings/delete/<?= $setting['id'] ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الربط؟');">
                                                <button type="submit" class="text-red-600 hover:text-red-800 focus:outline-none">
                                                    <i class="fas fa-trash-alt"></i> حذف
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 