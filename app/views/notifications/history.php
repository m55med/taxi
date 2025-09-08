<?php include_once __DIR__ . '/../includes/header.php'; ?>

<body class="bg-gray-100">
    <div class="container mx-auto p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?>
            </h1>
            <?php if (isset($_SESSION['user']['role_name']) && in_array($_SESSION['user']['role_name'], ['admin', 'developer'])): ?>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="<?= URLROOT ?>/notifications/create"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-plus-circle mr-2"></i> Create New Notification
                    </a>
                    <a href="<?= URLROOT ?>/notifications"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Management
                    </a>
                </div>
            <?php endif; ?>

        </div>

        <div class="space-y-6">
            <?php if (empty($data['notifications'])): ?>
                <div class="bg-white shadow-md rounded-lg p-10 text-center text-gray-500">
                    You don't have any notifications yet.
                </div>
            <?php else: ?>
                <?php foreach ($data['notifications'] as $notification): ?>
                    <div id="notification-<?= $notification['id'] ?>"
                        class="bg-white shadow-md rounded-lg p-6 border-l-4 <?= $notification['is_read'] ? 'border-gray-300' : 'border-blue-500' ?> scroll-mt-20">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <h2 class="text-xl font-bold text-gray-800 mb-2 sm:mb-0">
                                <?= htmlspecialchars($notification['title']) ?></h2>
                            <span
                                class="text-xs text-gray-500"><?= date('F j, Y, g:i a', strtotime($notification['created_at'])) ?></span>
                        </div>
                        <div class="mt-4 text-gray-700 leading-relaxed prose max-w-none">
                            <?= $notification['message'] // Allow HTML from admin, rendered within prose for styling ?>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <div class="mt-4 text-right">
                                <span
                                    class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">NEW</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>