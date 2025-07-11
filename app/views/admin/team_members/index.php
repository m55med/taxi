<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['team_member_message'])) {
    $flashMessage = [
        'message' => $_SESSION['team_member_message'],
        'type' => $_SESSION['team_member_message_type'] ?? 'success'
    ];
    unset($_SESSION['team_member_message'], $_SESSION['team_member_message_type']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>
<div x-data="teamMembersPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.500ms class="fixed top-5 right-5 z-50">
        <div class="px-6 py-3 rounded-lg shadow-md flex items-center text-white" :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
            <i class="mr-3" :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Team Members</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Assign User to Team</h2>
                    <p class="text-sm text-gray-600 mb-4">You can add a new user to a team or move an existing user to a different team.</p>
                    <form action="<?= BASE_PATH ?>/admin/team_members/store" method="POST">
                        <div class="mt-4">
                            <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                            <select name="user_id" id="user_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select a user</option>
                                <?php foreach ($data['users'] as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mt-4">
                            <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
                            <select name="team_id" id="team_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select a team</option>
                                <?php foreach ($data['teams'] as $team): ?>
                                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                            <i class="fas fa-user-plus mr-2"></i>
                            Assign Member
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Current Team Members</h2>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($data['team_members'])): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No team members found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['team_members'] as $index => $member): ?>
                                            <tr>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= $index + 1 ?></td>
                                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($member['user_name']) ?></td>
                                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($member['team_name']) ?></td>
                                                <td class="px-6 py-4 text-left text-sm font-medium">
                                                    <button @click="confirmDelete(<?= $member['id'] ?>, '<?= htmlspecialchars(addslashes($member['user_name'])) ?>', '<?= htmlspecialchars(addslashes($member['team_name'])) ?>')" class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="deleteModal.open = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="deleteModal.open = false">
            <h2 class="text-xl font-bold mb-4">Confirm Removal</h2>
            <p class="mb-6" x-html="deleteModal.message"></p>
            <div class="flex justify-end space-x-4">
                <button @click="deleteModal.open = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                <form :action="deleteModal.actionUrl" method="POST" class="inline">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Yes, Remove</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function teamMembersPage(flashMessage) {
    return {
        toast: { show: false, message: '', type: 'success' },
        deleteModal: { open: false, message: '', actionUrl: '' },
        init() {
            if (flashMessage) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        },
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 4000);
        },
        confirmDelete(id, userName, teamName) {
            this.deleteModal.message = `Are you sure you want to remove "<strong>${userName}</strong>" from the team "<strong>${teamName}</strong>"?`;
            this.deleteModal.actionUrl = `<?= BASE_PATH ?>/admin/team_members/delete/${id}`;
            this.deleteModal.open = true;
        }
    }
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html> 