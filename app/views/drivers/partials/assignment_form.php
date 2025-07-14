<?php
// We only show this form to users with specific roles
$allowedRoles = ['admin', 'developer', 'quality_manager', 'Team_leader'];
if (!isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], $allowedRoles)) {
    return;
}
?>

<div class="mt-6 bg-white p-6 rounded-lg shadow-md h-fit">
    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
        <i class="fas fa-user-exchange text-gray-400 mr-3"></i>
        Assign Driver
    </h2>
    <form action="<?= BASE_PATH ?>/drivers/assign" method="POST">
        <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
        <div class="mb-4">
            <label for="to_user_id" class="block text-sm font-medium text-gray-700 mb-1">Assign to staff:</label>
            <select id="to_user_id" name="to_user_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">-- Select Staff --</option>
                <?php foreach ($assignableUsers as $user): ?>
                    <?php if ($user['id'] === $currentUser['id'])
                        continue; // Don't allow assigning to self ?>
                    <option value="<?= $user['id'] ?>">
                        <?= htmlspecialchars($user['username']) ?>
                        <?= $user['is_online'] ? ' (Online)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional):</label>
            <textarea id="note" name="note" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Enter reason for assignment or any other notes..."></textarea>
        </div>

        <button type="submit"
            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-check mr-2"></i>
            Confirm Assignment
        </button>
    </form>
</div>