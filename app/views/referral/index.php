<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" x-data="{ activeTab: 'reports' }">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($page_main_title); ?></h1>

    <!-- Temporary Debug Block -->
    <!-- <div class="my-4 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">معلومات للمطور:</strong>
        <span class="block sm:inline">الدور الحالي للمستخدم هو: '<?php echo isset($user_role) ? htmlspecialchars($user_role) : 'غير محدد'; ?>'</span>
    </div> -->

    <!-- Referral Link for Marketer -->
    <?php if (isset($user_role) && strtolower($user_role) === 'marketer'): ?>
    <div class="mb-6 bg-white p-5 rounded-lg shadow-sm border border-gray-200" x-data="{ link: '<?php echo htmlspecialchars($referral_link); ?>' }">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">رابط التسويق الخاص بك</h2>
        <div class="flex items-center space-x-2 space-x-reverse">
            <input type="text" :value="link" readonly class="flex-grow bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
            <button @click="navigator.clipboard.writeText(link); alert('تم نسخ الرابط!');" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-300 ease-in-out text-sm font-medium">
                نسخ
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-4 space-x-reverse" aria-label="Tabs">
            <a href="#" @click.prevent="activeTab = 'reports'"
               :class="{ 'border-blue-500 text-blue-600': activeTab === 'reports', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reports' }"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                التقارير والإحصائيات
            </a>
            <?php if (isset($user_role) && strtolower($user_role) === 'marketer'): ?>
            <a href="#" @click.prevent="activeTab = 'profile'"
               :class="{ 'border-blue-500 text-blue-600': activeTab === 'profile', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'profile' }"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                الملف الشخصي
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Tab Content -->
    <div>
        <!-- Reports Section -->
        <div x-show="activeTab === 'reports'" x-transition>
            <?php include_once APPROOT . '/views/referral/dashboard/_reports.php'; ?>
        </div>

        <!-- Profile Section -->
        <?php if (isset($user_role) && strtolower($user_role) === 'marketer'): ?>
        <div x-show="activeTab === 'profile'" x-transition>
             <?php include_once APPROOT . '/views/referral/profile/_profile.php'; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>