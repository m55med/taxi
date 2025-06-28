<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <a href="<?= URLROOT ?>/referral/dashboard" class="text-sm text-blue-600 hover:underline">&larr; Back to Admin Dashboard</a>
    </div>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <form action="<?= URLROOT ?>/referral/saveAgentProfile" method="POST">
        <!-- Hidden input for user ID -->
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user']['id']) ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Profile Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">User Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($data['user']['username']) ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                            <p class="mt-1 text-xs text-gray-500">Username cannot be changed.</p>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['user']['email']) ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Agent Profile</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">State / City</label>
                            <input type="text" id="state" name="state" value="<?= htmlspecialchars($data['agentProfile']['state'] ?? '') ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($data['agentProfile']['phone'] ?? '') ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="google_map_url" class="block text-sm font-medium text-gray-700">Google Maps URL</label>
                            <input type="url" id="google_map_url" name="google_map_url" value="<?= htmlspecialchars($data['agentProfile']['map_url'] ?? '') ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="md:col-span-2 flex items-center">
                            <input type="checkbox" id="is_online_only" name="is_online_only" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" <?= isset($data['agentProfile']['is_online_only']) && $data['agentProfile']['is_online_only'] ? 'checked' : '' ?>>
                            <label for="is_online_only" class="ml-2 block text-sm text-gray-900">This is an online-only marketer</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Working Hours -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow-md">
                     <h2 class="text-xl font-semibold mb-4">Working Hours</h2>
                     <!-- This includes the working hours partial -->
                     <?php 
                        // To make the partial reusable, we'll pass the working_hours data to it.
                        $working_hours_data = $data['working_hours'];
                        if (file_exists(APPROOT . '/views/referral/profile/_working_hours.php')) {
                            include APPROOT . '/views/referral/profile/_working_hours.php';
                        }
                     ?>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 