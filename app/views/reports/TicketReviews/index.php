<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Ticket Reviews Report</h1>
            <p class="text-lg text-gray-600">A detailed log of all quality reviews on tickets.</p>
        </div>
        <div class="flex items-center mt-4 md:mt-0">
             <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition mr-2">
                <i class="fas fa-file-excel mr-2"></i>Excel
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'json'])) ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
                <i class="fas fa-file-code mr-2"></i>JSON
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <form action="" method="get" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="reviewer_id" class="block text-sm font-medium text-gray-700">Reviewed By</label>
                    <select name="reviewer_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">All Reviewers</option>
                        <?php foreach($data['filter_options']['reviewers'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['reviewer_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-gray-700">Agent</label>
                    <select name="agent_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">All Agents</option>
                        <?php foreach($data['filter_options']['agents'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['agent_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="rating_from" class="block text-sm font-medium text-gray-700">Rating From</label>
                    <input type="number" name="rating_from" placeholder="0" value="<?= htmlspecialchars($data['filters']['rating_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="rating_to" class="block text-sm font-medium text-gray-700">Rating To</label>
                    <input type="number" name="rating_to" placeholder="100" value="<?= htmlspecialchars($data['filters']['rating_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ticket #</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Reviewed By</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['reviews'])): ?>
                        <tr><td colspan="6" class="text-center py-10">No ticket reviews found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['reviews'] as $review): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/tickets/view/<?= $review['ticket_id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($review['ticket_number']) ?></a></td>
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $review['agent_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($review['agent_name']) ?></a></td>
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $review['reviewer_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($review['reviewer_name']) ?></a></td>
                                <td class="px-6 py-4 text-center font-bold text-lg"><?= htmlspecialchars($review['rating']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-sm truncate" title="<?= htmlspecialchars($review['review_notes']) ?>"><?= htmlspecialchars($review['review_notes']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"><?= date('Y-m-d H:i', strtotime($review['reviewed_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4"><?php require APPROOT . '/views/includes/pagination_controls.php'; ?></div>
    </div>
</div>

<?php require APPROOT . '/views/includes/footer.php'; ?> 