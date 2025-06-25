<?php include_once __DIR__ . '/../../includes/header.php'; ?>
<?php require APPROOT . '/app/views/includes/nav.php'; ?>
<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Summary Table -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Notifications Summary</h2>
            <div class="overflow-x-auto max-h-screen">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0">Notification</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0">Read / Sent</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0">Details</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                    <?php foreach($data['summary'] as $item): ?>
                        <tr class="hover:bg-gray-50 <?= (isset($data['selected_notification']) && $data['selected_notification'] == $item['id']) ? 'bg-blue-100' : '' ?>">
                            <td class="px-5 py-4 border-b border-gray-200">
                                <p class="font-bold text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($item['title']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap text-xs"><?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200">
                                <p class="text-gray-900 whitespace-no-wrap"><?= $item['total_read'] ?> / <?= $item['total_sent'] ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200">
                                <a href="?notification_id=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold">View Readers</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Readers Details -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Users Who Read Notification</h2>
            <?php if(isset($data['selected_notification'])): ?>
                <?php if(empty($data['readers'])): ?>
                    <p class="text-gray-500">No one has read this notification yet.</p>
                <?php else: ?>
                    <div class="overflow-y-auto max-h-screen">
                        <table class="min-w-full leading-normal">
                             <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0">User</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0">Read At</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php foreach($data['readers'] as $reader): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 border-b border-gray-200"><?= htmlspecialchars($reader['username']) ?></td>
                                    <td class="px-5 py-4 border-b border-gray-200"><?= date('Y-m-d H:i:s', strtotime($reader['read_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-gray-500">Select a notification from the summary list to see who has read it.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?> 