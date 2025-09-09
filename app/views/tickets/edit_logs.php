<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Logs - Ticket #<?= htmlspecialchars($data['ticket']['ticket_number']) ?></h1>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/tickets/view/<?= $data['ticket']['id'] ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Ticket
            </a>
        </div>
    </div>

    <?php if (empty($data['editLogs'])): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <i class="fas fa-info-circle text-blue-500 text-2xl mb-3"></i>
            <h3 class="text-lg font-medium text-blue-800 mb-2">No Edit History</h3>
            <p class="text-blue-600">This ticket has no recorded edits yet.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-edit text-gray-500 mr-2"></i>
                    Edit History (<?= count($data['editLogs']) ?> changes)
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                <?php foreach ($data['editLogs'] as $log): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                <div>
                                    <h4 class="font-medium text-gray-900"><?= htmlspecialchars($log['field_name']) ?> Changed</h4>
                                    <p class="text-sm text-gray-600">
                                        by <strong><?= htmlspecialchars($log['editor_name'] ?? 'Unknown User') ?></strong>
                                        <?php if (!empty($log['editor_username'])): ?>
                                            (<?= htmlspecialchars($log['editor_username']) ?>)
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                            </div>
                        </div>

                        <div class="ml-5 pl-4 border-l-2 border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-medium text-red-600 uppercase tracking-wider">From (Old Value)</label>
                                    <div class="mt-1 p-3 bg-red-50 border border-red-200 rounded-md">
                                        <code class="text-sm text-red-800">
                                            <?= $log['old_value'] ? htmlspecialchars($log['old_value']) : '<em class="text-red-400">Empty</em>' ?>
                                        </code>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-green-600 uppercase tracking-wider">To (New Value)</label>
                                    <div class="mt-1 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <code class="text-sm text-green-800">
                                            <?= $log['new_value'] ? htmlspecialchars($log['new_value']) : '<em class="text-green-400">Empty</em>' ?>
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
