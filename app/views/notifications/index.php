<?php include_once __DIR__ . '/../includes/header.php'; ?>

<body class="bg-gray-100">
<div class="container mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-0 text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <div class="flex space-x-2 space-x-reverse">
             <a href="<?= BASE_PATH ?>/notifications/history" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                <i class="fas fa-history mr-2"></i> View History
            </a>
            <a href="<?= BASE_PATH ?>/notifications/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                <i class="fas fa-plus mr-2"></i> Create New Notification
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Message</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Sent At</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Read / Sent</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['notifications'])): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">No notifications have been sent yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['notifications'] as $notification): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap font-semibold"><?= htmlspecialchars($notification['title']) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <div class="max-w-xs truncate" title="<?= htmlspecialchars(strip_tags($notification['message'])) ?>">
                                        <?= htmlspecialchars(strip_tags($notification['message'])) ?>
                                    </div>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= date('Y-m-d H:i', strtotime($notification['created_at'])) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= $notification['total_read'] ?> / <?= $notification['total_recipients'] ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <a href="<?= BASE_PATH ?>/notifications/readers/<?= $notification['id'] ?>" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        View Readers
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 