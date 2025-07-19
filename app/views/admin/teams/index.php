<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['team_message'])) {
    $flashMessage = [
        'message' => $_SESSION['team_message'],
        'type' => $_SESSION['team_message_type'] ?? 'success'
    ];
    unset($_SESSION['team_message'], $_SESSION['team_message_type']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>
<div x-data="teamsPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.500ms class="fixed top-5 right-5 z-50">
        <div class="px-6 py-3 rounded-lg shadow-md flex items-center text-white" :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
            <i class="mr-3" :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Teams</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Add/Edit Form Card -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4" x-text="form.id ? 'Edit Team' : 'Add New Team'"></h2>
                    <form @submit.prevent="submitForm">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Team Name</label>
                            <input type="text" id="name" x-model="form.name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="mt-4">
                            <label for="team_leader_id" class="block text-sm font-medium text-gray-700">Team Leader</label>
                            <select id="team_leader_id" x-model="form.team_leader_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select a leader</option>
                                <?php foreach ($data['users'] as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-center mt-4 space-x-2">
                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium flex items-center justify-center">
                                <i class="fas" :class="form.id ? 'fa-save' : 'fa-plus'"></i>
                                <span class="ml-2" x-text="form.id ? 'Save Changes' : 'Add Team'"></span>
                            </button>
                            <button type="button" x-show="form.id" @click="resetForm()" class="w-auto bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <pre><?php print_r($data['users']); ?></pre>

            <!-- Teams List -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Existing Teams</h2>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Leader</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($data['teams'])): ?>
                                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No teams found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($data['teams'] as $index => $team): ?>
                                            <tr>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= $index + 1 ?></td>
                                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($team['name']) ?></td>
                                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($team['team_leader_name']) ?></td>
                                                <td class="px-6 py-4 text-left text-sm font-medium">
                                                    <button @click="editTeam(<?= htmlspecialchars(json_encode($team)) ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit"><i class="fas fa-edit"></i></button>
                                                    <button @click="confirmDelete(<?= $team['id'] ?>, '<?= htmlspecialchars(addslashes($team['name'])) ?>')" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash-alt"></i></button>
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
            <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
            <p class="mb-6" x-html="deleteModal.message"></p>
            <div class="flex justify-end space-x-4">
                <button @click="deleteModal.open = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                <form :action="deleteModal.actionUrl" method="POST" class="inline">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Hidden Form for Add/Edit -->
    <form x-ref="form" action="" method="POST" class="hidden">
        <input type="hidden" name="id" x-model="form.id">
        <input type="hidden" name="name" x-model="form.name">
        <input type="hidden" name="team_leader_id" x-model="form.team_leader_id">
    </form>

</div>

<script>
function teamsPage(flashMessage) {
    return {
        toast: { show: false, message: '', type: 'success' },
        deleteModal: { open: false, message: '', actionUrl: '' },
        form: { id: null, name: '', team_leader_id: '' },
        init() {
            if (flashMessage) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        },
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 4000);
        },
        editTeam(team) {
            this.form.id = team.id;
            this.form.name = team.name;
            this.form.team_leader_id = team.team_leader_id;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        resetForm() {
            this.form.id = null;
            this.form.name = '';
            this.form.team_leader_id = '';
        },
        submitForm() {
            const formRef = this.$refs.form;
            formRef.action = this.form.id ? `<?= URLROOT ?>/admin/teams/update` : `<?= URLROOT ?>/admin/teams/store`;
            formRef.submit();
        },
        confirmDelete(id, name) {
            this.deleteModal.message = `Are you sure you want to delete the team "<strong>${name}</strong>"? This may not be possible if it has members.`;
            this.deleteModal.actionUrl = `<?= URLROOT ?>/admin/teams/delete/${id}`;
            this.deleteModal.open = true;
        }
    }
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html> 