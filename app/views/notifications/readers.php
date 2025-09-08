<?php include_once __DIR__ . '/../includes/header.php'; ?>

<body class="bg-gray-100">
<div class="container mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-0 text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <a href="<?= URLROOT ?>/notifications" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i> Back to All Notifications
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Read At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['readers'])): ?>
                        <tr>
                            <td colspan="2" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">No one has read this notification yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['readers'] as $reader): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($reader['username']) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= date('F j, Y, g:i a', strtotime($reader['read_at'])) ?></p>
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