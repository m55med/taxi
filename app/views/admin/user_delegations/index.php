<?php require_once APPROOT . '/views/includes/header.php'; ?>
<?php require_once APPROOT . '/views/includes/nav.php'; ?>

<?php
$users = $data['users'] ?? [];
$delegationTypes = $data['delegationTypes'] ?? [];
$userDelegations = $data['userDelegations'] ?? [];

$months = [];
for ($m = 1; $m <= 12; $m++) {
    $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
}
$currentYear = date('Y');
$years = range($currentYear - 2, $currentYear + 2);
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Assign Delegations</h1>
        <p class="text-gray-600 mt-1">Assign a percentage-based point bonus to a user for a specific month.</p>
    </div>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <!-- Assign Delegation Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Assign New Delegation</h2>
        <form action="<?= URLROOT ?>/admin/user-delegations/create" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                    <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Select User...</option>
                        <?php foreach ($users as $user): ?>
    <option value="<?= $user->id; ?>"><?= htmlspecialchars($user->username); ?></option>
<?php endforeach; ?>

                    </select>
                </div>
                <div>
                    <label for="delegation_type_id" class="block text-sm font-medium text-gray-700">Delegation Type</label>
                    <select id="delegation_type_id" name="delegation_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Select Type...</option>
                        <?php foreach ($delegationTypes as $type): ?>
                            <option value="<?= $type['id']; ?>"><?= htmlspecialchars($type['name']); ?> (<?= htmlspecialchars(number_format($type['percentage'], 2)); ?>%)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Applicable Month</label>
                    <select id="month" name="month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num; ?>" <?= (date('n') == $num) ? 'selected' : ''; ?>><?= $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Applicable Year</label>
                    <select id="year" name="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year; ?>" <?= ($currentYear == $year) ? 'selected' : ''; ?>><?= $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                <textarea id="reason" name="reason" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., For exceeding monthly targets"></textarea>
            </div>
            <div>
                <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white font-bold py-2 px-4 rounded-md hover:bg-indigo-700">Assign Delegation</button>
            </div>
        </form>
    </div>

    <!-- Existing User Delegations Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-700">Currently Assigned Delegations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($userDelegations)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No delegations have been assigned yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($userDelegations as $delegation): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($delegation['user_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($delegation['delegation_type_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars(number_format($delegation['percentage'], 2)); ?>%</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $months[$delegation['applicable_month']] . ' ' . $delegation['applicable_year']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($delegation['reason'] ?: 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($delegation['assigned_by_user_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('Y-m-d', strtotime($delegation['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="<?= URLROOT ?>/admin/user-delegations/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                        <input type="hidden" name="id" value="<?= $delegation['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 