<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

    <?php
    // Define variables early for breadcrumb navigation
    $ticketId = $data['ticket']['id'] ?? null;
    $ticketNumber = $data['ticket']['ticket_number'] ?? null;
    $ticketTitle = $data['ticket']['title'] ?? 'Unknown Ticket';
    $phone = $data['ticket']['phone'] ?? null;
    $platformName = $data['ticket']['platform_name'] ?? null;
    $categoryName = $data['ticket']['category_name'] ?? null;
    ?>

    <!-- Breadcrumb Navigation -->
 

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Logs - Ticket #<?= $ticketNumber ? htmlspecialchars($ticketNumber) : 'Unknown' ?>
            </h1>
            <?php if ($ticketTitle && $ticketTitle !== 'Unknown Ticket'): ?>
                <p class="text-gray-600 mt-1">
                    <strong>Title:</strong> <?= htmlspecialchars($ticketTitle) ?>
                    <?php if ($phone): ?>
                        | <strong>Phone:</strong> <?= htmlspecialchars($phone) ?>
                    <?php endif; ?>
                    <?php if ($platformName): ?>
                        | <strong>Platform:</strong> <?= htmlspecialchars($platformName) ?>
                    <?php endif; ?>
                    <?php if ($categoryName): ?>
                        | <strong>Category:</strong> <?= htmlspecialchars($categoryName) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
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
                                <?= htmlspecialchars($log['created_at_formatted']) ?>
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

    <!-- Deleted Details Section -->
    <?php
    // Get all logs including deleted ones for this ticket
    $allLogs = $data['listingModel']->getTicketLogs($ticketId);
    $deletedLogs = array_filter($allLogs, function($log) {
        return $log['field_name'] === 'DELETED';
    });
    ?>

    <?php if (!empty($deletedLogs)): ?>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mt-6">
            <div class="bg-red-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-red-800 flex items-center">
                    <i class="fas fa-trash text-red-500 mr-2"></i>
                    تفاصيل محذوفة (<?= count($deletedLogs) ?> حذف)
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                <?php foreach ($deletedLogs as $log): ?>
                    <div class="p-6 hover:bg-red-50 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                                <div>
                                    <h4 class="font-medium text-gray-900">تفصيلة محذوفة</h4>
                                    <p class="text-sm text-gray-600">
                                        تم الحذف بواسطة <strong><?= htmlspecialchars($log['editor_name'] ?? 'Unknown User') ?></strong>
                                        <?php if (!empty($log['editor_username'])): ?>
                                            (<?= htmlspecialchars($log['editor_username']) ?>)
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                <?= htmlspecialchars($log['created_at_formatted']) ?>
                            </div>
                        </div>

                        <div class="ml-5 pl-4 border-l-2 border-red-200">
                            <?php
                            $deleteDetails = json_decode($log['old_value'], true);
                            if ($deleteDetails):
                            ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php if (!empty($deleteDetails['ticket_number'])): ?>
                                        <div class="bg-gray-50 p-3 rounded">
                                            <label class="text-xs font-medium text-gray-600">رقم التذكرة</label>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($deleteDetails['ticket_number']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($deleteDetails['phone'])): ?>
                                        <div class="bg-gray-50 p-3 rounded">
                                            <label class="text-xs font-medium text-gray-600">رقم الهاتف</label>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($deleteDetails['phone']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($deleteDetails['notes'])): ?>
                                        <div class="bg-gray-50 p-3 rounded col-span-full">
                                            <label class="text-xs font-medium text-gray-600">الملاحظات</label>
                                            <div class="text-sm text-gray-900">
                                                <?= htmlspecialchars($deleteDetails['notes']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-gray-50 p-3 rounded">
                                    <code class="text-sm text-gray-800">
                                        <?= htmlspecialchars(substr($log['old_value'], 0, 500)) ?>
                                        <?= strlen($log['old_value']) > 500 ? '...' : '' ?>
                                    </code>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
