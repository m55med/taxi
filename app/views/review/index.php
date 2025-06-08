<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مراجعة المكالمات والمستندات</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .status-badge {
            @apply px-3 py-1 rounded-full text-sm font-medium;
        }

        .status-waiting_chat {
            @apply bg-yellow-100 text-yellow-800;
        }

        .status-completed {
            @apply bg-green-100 text-green-800;
        }

        .status-reconsider {
            @apply bg-red-100 text-red-800;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- Notification Component -->
    <div id="notification" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg z-50">
        <div class="p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i id="notificationIcon" class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p id="notificationMessage" class="text-sm font-medium text-gray-900"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">مراجعة المكالمات والمستندات</h1>
            <p class="text-gray-600">مراجعة السائقين في انتظار التحقق من المستندات</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">حالة السائق</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <option value="">جميع الحالات</option>
                        <option value="waiting_chat" <?= isset($_GET['status']) && $_GET['status'] == 'waiting_chat' ? 'selected' : '' ?>>في انتظار المراجعة</option>
                        <option value="completed" <?= isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                        <option value="reconsider" <?= isset($_GET['status']) && $_GET['status'] == 'reconsider' ? 'selected' : '' ?>>إعادة النظر</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">بحث</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                           placeholder="اسم السائق أو رقم الهاتف..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                        بحث
                    </button>
                </div>
            </form>
        </div>

        <!-- Drivers List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السائق</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الهاتف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">آخر تحديث</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($driver['name']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($driver['nationality'] ?? '') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($driver['phone']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="status-badge status-<?= $driver['main_system_status'] ?>">
                                <?= $status_text[$driver['main_system_status']] ?? $driver['main_system_status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('Y/m/d H:i', strtotime($driver['updated_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                            <button onclick="showDriverModal(<?= $driver['id'] ?>)" 
                                    class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-edit ml-1"></i>
                                مراجعة
                            </button>
                            <button onclick="showTransferModal(<?= $driver['id'] ?>)"
                                    class="text-gray-600 hover:text-gray-900 mr-4">
                                <i class="fas fa-exchange-alt ml-1"></i>
                                تحويل
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="driverModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-full overflow-y-auto">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-xl font-semibold text-gray-800">مراجعة بيانات السائق</h3>
                <button onclick="hideDriverModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            
            <form id="reviewForm" class="space-y-6">
                <input type="hidden" name="driver_id" id="reviewDriverId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تغيير حالة السائق</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="completed">مكتمل</option>
                                <option value="reconsider">إعادة النظر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">إضافة ملاحظات</label>
                            <textarea name="notes" rows="4" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="أدخل ملاحظاتك هنا..."></textarea>
                        </div>
                    </div>

                    <!-- Left Column -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">التحقق من المستندات</label>
                        <div id="documentsList" class="space-y-2 border rounded-md p-3 bg-gray-50 max-h-60 overflow-y-auto">
                            <!-- سيتم ملء هذا القسم بالجافاسكريبت -->
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 space-x-reverse border-t pt-4">
                    <button type="button" onclick="hideDriverModal()"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-semibold">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl p-6 w-96">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">تحويل السائق</h3>
                <button onclick="hideTransferModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="transferForm" class="space-y-4">
                <input type="hidden" name="driver_id" id="transferDriverId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تحويل إلى</label>
                    <select name="to_user_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['username']) ?>
                                    <?= $user['is_online'] ? '(متصل)' : '' ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات التحويل</label>
                    <textarea name="note" rows="2" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md"
                        placeholder="أدخل سبب التحويل هنا..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="hideTransferModal()"
                        class="ml-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        تأكيد التحويل
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const BASE_PATH = '<?= BASE_PATH ?>';
    </script>
    <script src="<?= BASE_PATH ?>/js/review.js"></script>
</body>
</html> 