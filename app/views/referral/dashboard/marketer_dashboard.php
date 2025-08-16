<?php require_once APPROOT . '/views/includes/header.php'; ?>


<div class="container mx-auto p-4 sm:p-6 lg:p-8" x-data="{ activeTab: 'reports' }">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($data['page_main_title']); ?></h1>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>
    
    <!-- Referral Link for Marketer -->
    <div class="mb-6 bg-white p-5 rounded-lg shadow-sm border border-gray-200" x-data="{ link: '<?php echo htmlspecialchars($data['referral_link']); ?>' }">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">رابط التسويق الخاص بك (للسائقين)</h2>
        <div class="flex items-center space-x-2 space-x-reverse mb-3">
            <input type="text" :value="link" readonly class="flex-grow bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
            <button @click="navigator.clipboard.writeText(link); alert('تم نسخ الرابط!');" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-300 ease-in-out text-sm font-medium">
                نسخ
            </button>
        </div>
        <?php include APPROOT . '/views/includes/_social_share.php'; ?>
    </div>

    <!-- Restaurant Referral Link for Marketer -->
    <div class="mb-6 bg-white p-5 rounded-lg shadow-sm border border-gray-200" x-data="{ link: '<?php echo htmlspecialchars($data['restaurant_referral_link']); ?>' }">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">رابط التسويق الخاص بك (للمطاعم)</h2>
        <div class="flex items-center space-x-2 space-x-reverse mb-3">
            <input type="text" :value="link" readonly class="flex-grow bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
            <button @click="navigator.clipboard.writeText(link); alert('تم نسخ الرابط!');" class="bg-purple-600 text-white px-4 py-2.5 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 transition duration-300 ease-in-out text-sm font-medium">
                نسخ
            </button>
        </div>
        <?php include APPROOT . '/views/includes/_social_share.php'; ?>
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
            <a href="#" @click.prevent="activeTab = 'restaurants'"
               :class="{ 'border-blue-500 text-blue-600': activeTab === 'restaurants', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'restaurants' }"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                المطاعم المسجلة
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
            <!-- Pagination for Visits Table -->
            <div class="mt-6">
                <?php include_once APPROOT . '/views/includes/_pagination.php'; ?>
            </div>
        </div>

        <!-- Profile Section -->
        <div x-show="activeTab === 'profile'" x-transition>
             <?php if (!empty($agentProfile)): ?>
                <?php // Note: We might need to create this partial if it doesn't exist.
                if (file_exists(APPROOT . '/views/referral/profile/_profile.php')) {
                    // Pass the necessary data to the partial view
                    $profile_data = [
                        'agentProfile' => $agentProfile,
                        'working_hours' => $working_hours,
                        'user' => (object) ['id' => $_SESSION['user_id']] // Pass user ID for the form action
                    ];
                    extract($profile_data);
                    include APPROOT . '/views/referral/profile/_profile.php';
                } else {
                    echo "<p>Profile view not found.</p>";
                }
            ?>
            <?php else: ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                    <p class="font-bold">ملف غير مكتمل</p>
                    <p>يرجى إكمال ملفك الشخصي لبدء استخدام جميع الميزات. <a href="<?= URLROOT ?>/referral/editProfile/<?= $_SESSION['user_id'] ?>" class="font-bold underline">إكمال الآن</a></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Restaurants Section -->
        <div x-show="activeTab === 'restaurants'" x-transition>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6">
                    <h2 class="text-xl font-semibold text-gray-700">المطاعم المسجلة عن طريقك</h2>
                    <p class="mt-1 text-sm text-gray-500">قائمة بكل مطعم تم تسجيله بواسطة رابط الإحالة الخاص بك.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم (انجليزي)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم (عربي)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الهاتف</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">العنوان</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($referredRestaurants)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">لا توجد مطاعم مسجلة بعد.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($referredRestaurants as $restaurant): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($restaurant['name_en']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($restaurant['name_ar']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" dir="ltr"><?= htmlspecialchars($restaurant['phone']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($restaurant['address']) ?></td>
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

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 