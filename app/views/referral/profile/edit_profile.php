<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <div>
            <a href="<?= URLROOT ?>/referral/marketerDetails/<?= $data['user']['id'] ?>" class="text-sm text-blue-600 hover:underline mr-4">&larr; العودة للتفاصيل</a>
            <a href="<?= URLROOT ?>/referral/dashboard" class="text-sm text-gray-600 hover:underline">العودة للوحة التحكم</a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <form action="<?= URLROOT ?>/referral/saveAgentProfile" method="POST" class="space-y-8">
        <!-- Hidden input for user ID -->
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user']['id']) ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Profile & Location -->
            <div class="lg:col-span-2 space-y-8">
                <!-- User Details Card -->
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h2 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-800">تفاصيل المستخدم</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">اسم المستخدم</label>
                            <input type="text" id="username" value="<?= htmlspecialchars($data['user']['username']) ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed" readonly>
                            <p class="mt-1 text-xs text-gray-500">لا يمكن تغيير اسم المستخدم.</p>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($data['user']['email']) ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed" readonly>
                        </div>
                    </div>
                </div>

                <!-- Agent Profile & Location Card -->
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h2 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-800">الملف الشخصي والموقع</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">الولاية / المدينة</label>
                            <input type="text" id="state" name="state" value="<?= htmlspecialchars($data['agentProfile']['state'] ?? '') ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">رقم الهاتف</label>
                            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($data['agentProfile']['phone'] ?? '') ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="google_map_url" class="block text-sm font-medium text-gray-700">رابط خرائط جوجل</label>
                            <input type="url" id="google_map_url" name="google_map_url" value="<?= htmlspecialchars($data['agentProfile']['map_url'] ?? '') ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="https://maps.app.goo.gl/...">
                        </div>
                        <div class="md:col-span-2 flex items-center mt-2">
                            <input type="hidden" name="is_online_only" value="0">
                            <input type="checkbox" id="is_online_only" name="is_online_only" value="1" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" <?= !empty($data['agentProfile']['is_online_only']) ? 'checked' : '' ?>>
                            <label for="is_online_only" class="ml-2 block text-sm text-gray-900">هذا المسوق يعمل عبر الإنترنت فقط</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Working Hours -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                     <h2 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-800">أوقات العمل</h2>
                     <?php 
                        $working_hours_data = $data['working_hours'];
                        if (file_exists(APPROOT . '/views/referral/profile/_working_hours.php')) {
                            include APPROOT . '/views/referral/profile/_working_hours.php';
                        }
                     ?>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end pt-5 border-t">
            <button type="submit" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition-all duration-300">
                حفظ التغييرات
            </button>
        </div>
    </form>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 