<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">

    <!-- Welcome message can be uncommented if needed -->
    <!-- <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h1>
        <p class="mt-2 text-gray-600">Your current role: <?= ucfirst(htmlspecialchars($_SESSION['role'])) ?></p>
    </div> -->

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($data['quickStats'])): ?>
    <!-- Admin Quick Stats -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Quick Stats</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Users -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <i class="fas fa-users text-2xl text-indigo-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($data['quickStats']['total_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <!-- Active Users -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-user-check text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Users</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($data['quickStats']['active_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <!-- Online Users -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-wifi text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Online Now</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($data['quickStats']['online_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <!-- Banned Users -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-user-slash text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Banned Users</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($data['quickStats']['blocked_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Dashboard Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Dashboard</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            
            <!-- Call Center -->
            <a href="<?= BASE_PATH ?>/calls" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-indigo-50 hover:border-indigo-300 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-indigo-800">Call Center</h3>
                    <i class="fas fa-headset text-2xl text-indigo-500 group-hover:text-indigo-600"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">Manage incoming and outgoing calls.</p>
            </a>

            <!-- Ticket Management -->
            <a href="<?= BASE_PATH ?>/create_ticket" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-purple-50 hover:border-purple-300 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-purple-800">Ticket Management</h3>
                    <i class="fas fa-ticket-alt text-2xl text-purple-500 group-hover:text-purple-600"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">Track and resolve customer support tickets.</p>
            </a>

            <!-- Discussions -->
            <a href="<?= BASE_PATH ?>/discussions" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-300 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-blue-800">Discussions</h3>
                    <i class="fas fa-comments text-2xl text-blue-500 group-hover:text-blue-600"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">Follow up on open discussions.</p>
            </a>

            <?php if (in_array($_SESSION['role'], ['admin', 'marketer'])): ?>
            <!-- Marketing -->
            <a href="<?= BASE_PATH ?>/referral/dashboard" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-yellow-50 hover:border-yellow-300 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-yellow-800">Marketing</h3>
                    <i class="fas fa-bullhorn text-2xl text-yellow-500 group-hover:text-yellow-600"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">Track marketer links and visits.</p>
            </a>
            <?php endif; ?>

            <?php if (in_array($_SESSION['role'], ['admin'])): ?>
            <!-- Reports Section -->
            <a href="<?= BASE_PATH ?>/reports/analytics" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-teal-50 hover:border-teal-300 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-teal-800">Reports</h3>
                    <i class="fas fa-chart-line text-2xl text-teal-500 group-hover:text-teal-600"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">View detailed analytics and stats.</p>
            </a>
            
             <!-- User Management -->
            <a href="<?= BASE_PATH ?>/admin/users" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-red-50 hover:border-red-300 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-red-800">User Management</h3>
                    <i class="fas fa-users-cog text-2xl text-red-500 group-hover:text-red-600"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">Add, edit, and manage users.</p>
            </a>

            <!-- Settings -->
            <a href="<?= BASE_PATH ?>/admin/permissions" class="group block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-200 hover:border-gray-400 transition-all duration-300">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 group-hover:text-black">Settings</h3>
                    <i class="fas fa-cogs text-2xl text-gray-500 group-hover:text-gray-700"></i>
                </div>
                <p class="text-sm text-gray-500 mt-2">Configure system settings.</p>
            </a>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>