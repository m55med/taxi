<?php require_once APPROOT . '/views/inc/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">تقرير المستخدمين</h2>
                <p class="mt-1 text-sm text-gray-600">عرض وتحليل بيانات المستخدمين</p>
            </div>
            <div>
                <a href="<?= BASE_PATH ?>/reports/users/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-export ml-1"></i>
                    تصدير التقرير
                </a>
            </div>
        </div>

        <!-- User Stats -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">إحصائيات المستخدمين</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <?php
                // Calculate statistics
                $total_users = 0;
                $active_users = 0;
                $online_users = 0;
                $blocked_users = 0;
                $pending_users = 0;

                if (isset($data['users']) && is_array($data['users'])) {
                    $total_users = count($data['users']);
                    foreach ($data['users'] as $user) {
                        if (isset($user['status'])) {
                            if ($user['status'] === 'active') {
                                $active_users++;
                            } elseif ($user['status'] === 'banned') {
                                $blocked_users++;
                            } elseif ($user['status'] === 'pending') {
                                $pending_users++;
                            }
                        }
                        if (isset($user['is_online']) && $user['is_online']) {
                            $online_users++;
                        }
                    }
                }
                ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">إجمالي المستخدمين</h4>
                            <p class="text-2xl font-semibold text-blue-600"><?= $total_users ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-user-check text-green-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">المستخدمين النشطين</h4>
                            <p class="text-2xl font-semibold text-green-600"><?= $active_users ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-user-tag text-purple-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">المستخدمين المعلقين</h4>
                            <p class="text-2xl font-semibold text-purple-600"><?= $pending_users ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-user-clock text-yellow-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">متصل حالياً</h4>
                            <p class="text-2xl font-semibold text-yellow-600"><?= $online_users ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-user-slash text-red-600"></i>
                        </div>
                        <div class="mr-4">
                            <h4 class="font-medium text-gray-800">المستخدمين المحظورين</h4>
                            <p class="text-2xl font-semibold text-red-600"><?= $blocked_users ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <?php if (!empty($data['summary_stats'])): ?>
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-4">ملخص إحصائيات المكالمات</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-phone-alt text-blue-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="font-medium text-gray-800">إجمالي المكالمات</h4>
                                <p class="text-2xl font-semibold text-blue-600">
                                    <?= number_format($data['summary_stats']['total_calls'] ?? 0) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="font-medium text-gray-800">تم الرد</h4>
                                <p class="text-2xl font-semibold text-green-600">
                                    <?= number_format($data['summary_stats']['answered'] ?? 0) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= number_format($data['summary_stats']['answered_rate'] ?? 0, 1) ?>%
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-full">
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="font-medium text-gray-800">لا يوجد رد</h4>
                                <p class="text-2xl font-semibold text-red-600">
                                    <?= number_format($data['summary_stats']['no_answer'] ?? 0) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= number_format($data['summary_stats']['no_answer_rate'] ?? 0, 1) ?>%
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div class="mr-4">
                                <h4 class="font-medium text-gray-800">مشغول</h4>
                                <p class="text-2xl font-semibold text-yellow-600">
                                    <?= number_format($data['summary_stats']['busy'] ?? 0) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= number_format($data['summary_stats']['busy_rate'] ?? 0, 1) ?>%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">تصفية النتائج</h3>
            <form method="GET" action="<?= BASE_PATH ?>/reports/users" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">الدور</label>
                    <select name="role_id"
                        class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">الكل</option>
                        <?php foreach ($data['roles'] as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= isset($_GET['role_id']) && $_GET['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">الحالة</label>
                    <select name="status"
                        class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">الكل</option>
                        <option value="active" <?= isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected' : '' ?>>نشط</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>معلق</option>
                        <option value="banned" <?= isset($_GET['status']) && $_GET['status'] == 'banned' ? 'selected' : '' ?>>محظور</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">من تاريخ</label>
                    <input type="date" name="date_from" value="<?= $data['filters']['date_from'] ?? '' ?>"
                        class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">إلى تاريخ</label>
                    <input type="date" name="date_to" value="<?= $data['filters']['date_to'] ?? '' ?>"
                        class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-filter ml-1"></i>
                        تصفية
                    </button>
                    <a href="<?= BASE_PATH ?>/reports/users"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-times ml-1"></i>
                        إعادة تعيين
                    </a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">اسم
                            المستخدم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            البريد الإلكتروني</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الدور</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            إجمالي المكالمات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تم
                            الرد</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">لا
                            يوجد رد</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            مشغول</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            مكالمات اليوم</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($data['users'] as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($user['username']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($user['role_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                switch ($user['status']) {
                                    case 'active':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        $statusText = 'نشط';
                                        break;
                                    case 'banned':
                                        $statusClass = 'bg-red-100 text-red-800';
                                        $statusText = 'محظور';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        $statusText = 'معلق';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                        $statusText = 'غير معروف';
                                        break;
                                }
                                ?>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= number_format($user['call_stats']['total_calls'] ?? 0) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span
                                        class="text-sm text-green-600 font-medium"><?= number_format($user['call_stats']['answered'] ?? 0) ?></span>
                                    <span
                                        class="mr-1 text-xs text-gray-500">(<?= number_format($user['call_stats']['answered_rate'] ?? 0, 1) ?>%)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span
                                        class="text-sm text-red-600 font-medium"><?= number_format($user['call_stats']['no_answer'] ?? 0) ?></span>
                                    <span
                                        class="mr-1 text-xs text-gray-500">(<?= number_format($user['call_stats']['no_answer_rate'] ?? 0, 1) ?>%)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span
                                        class="text-sm text-yellow-600 font-medium"><?= number_format($user['call_stats']['busy'] ?? 0) ?></span>
                                    <span
                                        class="mr-1 text-xs text-gray-500">(<?= number_format($user['call_stats']['busy_rate'] ?? 0, 1) ?>%)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <div class="flex items-center mb-1">
                                        <span
                                            class="text-sm text-gray-900 font-medium"><?= number_format($user['call_stats']['today_total'] ?? 0) ?></span>
                                    </div>
                                    <?php if (($user['call_stats']['today_total'] ?? 0) > 0): ?>
                                        <div class="flex flex-col text-xs space-y-1">
                                            <span
                                                class="text-green-600"><?= number_format($user['call_stats']['today_answered'] ?? 0) ?>
                                                تم الرد</span>
                                            <span
                                                class="text-red-600"><?= number_format($user['call_stats']['today_no_answer'] ?? 0) ?>
                                                لا رد</span>
                                            <span
                                                class="text-yellow-600"><?= number_format($user['call_stats']['today_busy'] ?? 0) ?>
                                                مشغول</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/inc/footer.php'; ?>