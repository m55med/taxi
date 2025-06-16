<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'إدارة المستخدمين') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/app/views/admin/users/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="bg-gray-100" x-data="usersPage()">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">إدارة المستخدمين</h1>
                <button onclick="window.location.href='<?= BASE_PATH ?>/dashboard/addUser'" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-user-plus ml-1"></i>
                    إضافة مستخدم جديد
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                المعرف
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                اسم المستخدم
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                البريد الإلكتروني
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الدور
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الحالة
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                آخر نشاط
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($data['users'])): ?>
                            <?php foreach ($data['users'] as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['id'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="online-badge <?= ($user['is_online'] ?? 0) ? 'active' : 'offline' ?>"></span>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($user['username'] ?? '') ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($user['role_name'] ?? 'مستخدم') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = $user['status'] ?? 'pending';
                                    $statusClasses = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'banned' => 'bg-red-100 text-red-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                    $statusText = [
                                        'active' => 'نشط',
                                        'banned' => 'محظور',
                                        'pending' => 'قيد المراجعة'
                                    ];
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasses[$status] ?>">
                                        <?= $statusText[$status] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" dir="ltr">
                                    <?php 
                                    if (!empty($user['updated_at'])) {
                                        $updated_at = strtotime($user['updated_at']);
                                        $now = time();
                                        $diff = $now - $updated_at;
                                        
                                        if ($diff < 0) {
                                            echo date('Y-m-d H:i', $updated_at);
                                        } elseif ($diff < 60) {
                                            echo 'منذ ' . $diff . ' ثانية';
                                        } elseif ($diff < 3600) {
                                            $minutes = floor($diff / 60);
                                            echo 'منذ ' . $minutes . ' ' . ($minutes === 1 ? 'دقيقة' : 'دقائق');
                                        } elseif ($diff < 86400) {
                                            $hours = floor($diff / 3600);
                                            echo 'منذ ' . $hours . ' ' . ($hours === 1 ? 'ساعة' : 'ساعات');
                                        } elseif ($diff < 604800) { // أقل من أسبوع
                                            $days = floor($diff / 86400);
                                            echo 'منذ ' . $days . ' ' . ($days === 1 ? 'يوم' : 'أيام');
                                        } else {
                                            echo date('Y-m-d H:i', $updated_at);
                                        }
                                    } else {
                                        echo 'غير متوفر';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <?php if (($user['id'] ?? '') !== ($_SESSION['user_id'] ?? '')): ?>
                                        <a href="<?= BASE_PATH ?>/dashboard/editUser/<?= htmlspecialchars($user['id'] ?? '') ?>" class="text-indigo-600 hover:text-indigo-900 ml-3">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button @click="openForceLogoutModal(<?= htmlspecialchars($user['id'] ?? '') ?>, '<?= htmlspecialchars($user['username'] ?? '') ?>')" class="text-yellow-600 hover:text-yellow-900 ml-3">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                        <a href="<?= BASE_PATH ?>/dashboard/deleteUser/<?= htmlspecialchars($user['id'] ?? '') ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_PATH ?>/dashboard/changePassword" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-key"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    لا يوجد مستخدمين حالياً
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Force Logout Modal -->
    <div x-show="forceLogoutModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeForceLogoutModal()">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="closeForceLogoutModal()">
            <h3 class="text-lg font-semibold mb-4">إجبار تسجيل الخروج لـ <span x-text="forceLogoutModal.username"></span></h3>
            <form @submit.prevent="submitForceLogout">
                <div class="mb-4">
                    <label for="logout_message" class="block text-sm font-medium text-gray-700">رسالة (اختياري)</label>
                    <input type="text" x-model="forceLogoutModal.message" id="logout_message" placeholder="مثال: تعال إلى مكتبي" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex justify-end space-x-2 space-x-reverse">
                    <button type="button" @click="closeForceLogoutModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">إلغاء</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">تأكيد الإجبار</button>
                </div>
            </form>
        </div>
    </div>
    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    const BASE_PATH = '<?= BASE_PATH ?>';
</script>
<script src="<?= BASE_PATH ?>/app/views/admin/users/js/users.js"></script>
</body>

</html> 