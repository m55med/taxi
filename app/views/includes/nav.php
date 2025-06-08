<?php if (isset($_SESSION['user_id'])): ?>
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_PATH ?>/dashboard" class="text-2xl font-bold text-indigo-600">تاكسي</a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8 sm:space-x-reverse">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_PATH ?>/dashboard/users" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-users-cog ml-1"></i>
                        إدارة المستخدمين
                    </a>
                    <a href="<?= BASE_PATH ?>/upload" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-file-upload ml-1"></i>
                        رفع بيانات السائقين
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_PATH ?>/dashboard" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-tachometer-alt ml-1"></i>
                        لوحة التحكم
                    </a>
                    <a href="<?= BASE_PATH ?>/call" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-phone-alt ml-1"></i>
                        مركز الاتصال
                    </a>
                </div>
            </div>
            <div class="flex items-center">
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <span class="text-sm text-gray-500">مرحباً، <?= htmlspecialchars($_SESSION['username']) ?></span>
                            <form method="POST" action="<?= BASE_PATH ?>/auth/logout" class="inline">
                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-sign-out-alt ml-1"></i>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?> 