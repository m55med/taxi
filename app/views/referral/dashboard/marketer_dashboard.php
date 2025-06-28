<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" x-data="{ activeTab: 'reports' }">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($data['page_main_title']); ?></h1>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>
    
    <!-- Referral Link for Marketer -->
    <div class="mb-6 bg-white p-5 rounded-lg shadow-sm border border-gray-200" x-data="{ link: '<?php echo htmlspecialchars($data['referral_link']); ?>' }">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">رابط التسويق الخاص بك</h2>
        <div class="flex items-center space-x-2 space-x-reverse">
            <input type="text" :value="link" readonly class="flex-grow bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
            <button @click="navigator.clipboard.writeText(link); alert('تم نسخ الرابط!');" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-300 ease-in-out text-sm font-medium">
                نسخ
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-4 space-x-reverse" aria-label="Tabs">
            <a href="#" @click.prevent="activeTab = 'reports'"
               :class="{ 'border-blue-500 text-blue-600': activeTab === 'reports', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reports' }"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                التقارير والإحصائيات
            </a>
            <a href="#" @click.prevent="activeTab = 'profile'"
               :class="{ 'border-blue-500 text-blue-600': activeTab === 'profile', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'profile' }"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                الملف الشخصي
            </a>
        </nav>
    </div>

    <!-- Tab Content -->
    <div>
        <!-- Reports Section -->
        <div x-show="activeTab === 'reports'" x-transition>
            <?php // Note: We might need to create this partial if it doesn't exist.
                if (file_exists(APPROOT . '/views/referral/dashboard/_reports.php')) {
                    include_once APPROOT . '/views/referral/dashboard/_reports.php';
                } else {
                    echo "<p>Reports view not found.</p>";
                }
            ?>
        </div>

        <!-- Profile Section -->
        <div x-show="activeTab === 'profile'" x-transition>
             <?php // Note: We might need to create this partial if it doesn't exist.
                if (file_exists(APPROOT . '/views/referral/profile/_profile.php')) {
                    include_once APPROOT . '/views/referral/profile/_profile.php';
                } else {
                    echo "<p>Profile view not found.</p>";
                }
            ?>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 