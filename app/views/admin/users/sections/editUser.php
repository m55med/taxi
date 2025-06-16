<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المستخدم</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">تعديل المستخدم</h1>
                <a href="<?= BASE_PATH ?>/dashboard/users" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-arrow-right ml-1"></i>
                    العودة للقائمة
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($data['user'])): ?>
                <form action="<?= BASE_PATH ?>/dashboard/editUser/<?= htmlspecialchars($data['user']['id']) ?>" method="POST" class="space-y-6">
                    <?php if ($data['user']['id'] === $_SESSION['user_id']): ?>
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">يمكنك فقط تغيير كلمة المرور الخاصة بك</span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">اسم المستخدم</label>
                        <input type="text" name="username" id="username" required
                            value="<?= htmlspecialchars($data['user']['username']) ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            <?= $data['user']['id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" required
                            value="<?= htmlspecialchars($data['user']['email']) ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            dir="ltr"
                            <?= $data['user']['id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور الجديدة (اتركها فارغة إذا لم ترد تغييرها)</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            dir="ltr">
                    </div>

                    <?php if ($data['user']['id'] !== $_SESSION['user_id']): ?>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">الدور</label>
                            <select name="role_id" id="role" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <?php foreach ($data['roles'] ?? [] as $role): ?>
                                    <option value="<?= htmlspecialchars($role['id']) ?>"
                                        <?= $data['user']['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">الحالة</label>
                            <select name="status" id="status" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="active" <?= $data['user']['status'] === 'active' ? 'selected' : '' ?>>نشط</option>
                                <option value="pending" <?= $data['user']['status'] === 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                                <option value="banned" <?= $data['user']['status'] === 'banned' ? 'selected' : '' ?>>محظور</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-end space-x-3 space-x-reverse">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center text-gray-500">
                    لم يتم العثور على المستخدم
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html> 