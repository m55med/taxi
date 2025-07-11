<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['user_message'])) {
    $flashMessage = [
        'message' => $_SESSION['user_message'],
        'type' => $_SESSION['user_message_type'] ?? 'success'
    ];
    unset($_SESSION['user_message'], $_SESSION['user_message_type']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<body class="bg-gray-100" x-data="usersPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.500ms class="fixed top-5 right-5 z-50">
        <div class="px-6 py-3 rounded-lg shadow-md flex items-center text-white" :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
            <i class="mr-3" :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <div class="bg-blue-500 text-white rounded-full h-12 w-12 flex items-center justify-center mr-4">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $data['stats']['total_users'] ?? 0 ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <div class="bg-green-500 text-white rounded-full h-12 w-12 flex items-center justify-center mr-4">
                    <i class="fas fa-wifi text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Online Users</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $data['stats']['online_users'] ?? 0 ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <div class="bg-yellow-500 text-white rounded-full h-12 w-12 flex items-center justify-center mr-4">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Active Users</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $data['stats']['active_users'] ?? 0 ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <div class="bg-red-500 text-white rounded-full h-12 w-12 flex items-center justify-center mr-4">
                    <i class="fas fa-user-slash text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Banned Users</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $data['stats']['banned_users'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
                <a href="<?= BASE_PATH ?>/admin/users/create" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-user-plus mr-1"></i>
                    Add New User
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Username
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Activity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($data['users'])): ?>
                            <?php foreach ($data['users'] as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['id'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="online-badge <?= ($user['is_online'] ?? 0) ? 'active' : 'offline' ?>"></span>
                                        <div class="text-sm font-medium text-gray-900 ml-2">
                                            <?= htmlspecialchars($user['username'] ?? '') ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars(ucfirst($user['role_name'] ?? 'user')) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = $user['status'] ?? 'pending';
                                    $statusClasses = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'banned' => 'bg-red-100 text-red-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                    $statusText = [
                                        'active' => 'Active',
                                        'banned' => 'Banned',
                                        'pending' => 'Pending'
                                    ];
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasses[$status] ?>">
                                        <?= $statusText[$status] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    if (!empty($user['updated_at'])) {
                                        $updated_at = new DateTime($user['updated_at']);
                                        echo $updated_at->format('Y-m-d H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <?php if (($user['id'] ?? '') !== ($_SESSION['user_id'] ?? '')): ?>
                                        <a href="<?= BASE_PATH ?>/admin/users/edit/<?= htmlspecialchars($user['id'] ?? '') ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button @click="openForceLogoutModal(<?= htmlspecialchars($user['id'] ?? '') ?>, '<?= htmlspecialchars($user['username'] ?? '') ?>')" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Force Logout">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                        <button @click="openDeleteModal(<?= htmlspecialchars($user['id'] ?? '') ?>, '<?= htmlspecialchars($user['username'] ?? '') ?>')" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= BASE_PATH ?>/admin/users/change_password" class="text-indigo-600 hover:text-indigo-900" title="Change Password">
                                            <i class="fas fa-key"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Force Logout Modal -->
    <div x-show="forceLogoutModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeForceLogoutModal()">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="closeForceLogoutModal()">
            <h3 class="text-lg font-semibold mb-4">Force Logout for <span x-text="forceLogoutModal.username"></span></h3>
            <form @submit.prevent="submitForceLogout">
                <div class="mb-4">
                    <label for="logout_message" class="block text-sm font-medium text-gray-700">Message (Optional)</label>
                    <input type="text" x-model="forceLogoutModal.message" id="logout_message" placeholder="e.g., Come to my office" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="closeForceLogoutModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirm Logout</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeDeleteModal()">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="closeDeleteModal()">
            <h3 class="text-lg font-semibold mb-2">Confirm Deletion</h3>
            <p class="text-gray-600 mb-4">Are you sure you want to delete user <span class="font-bold" x-text="deleteModal.username"></span>? This action cannot be undone.</p>
            <form @submit.prevent="submitDelete" method="POST" action="<?= BASE_PATH ?>/admin/users/destroy">
                <input type="hidden" name="id" x-model="deleteModal.userId">
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
    
<script>
function usersPage(flashMessage) {
    return {
        toast: { show: false, message: '', type: 'success' },
        forceLogoutModal: {
            open: false,
            userId: null,
            username: '',
            message: ''
        },
        deleteModal: {
            open: false,
            userId: null,
            username: ''
        },
        init() {
            if (flashMessage && flashMessage.message) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 4000);
        },
        openForceLogoutModal(userId, username) {
            this.forceLogoutModal.userId = userId;
            this.forceLogoutModal.username = username;
            this.forceLogoutModal.open = true;
        },
        closeForceLogoutModal() {
            this.forceLogoutModal.open = false;
            this.forceLogoutModal.userId = null;
            this.forceLogoutModal.username = '';
            this.forceLogoutModal.message = '';
        },
        submitForceLogout() {
            const body = new FormData();
            body.append('id', this.forceLogoutModal.userId);
            body.append('message', this.forceLogoutModal.message);

            fetch('<?= BASE_PATH ?>/admin/users/forceLogout', {
                method: 'POST',
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.showToast(`Logout initiated for ${this.forceLogoutModal.username}. They will be logged out on their next action.`);
                } else {
                    this.showToast(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(() => {
                this.showToast('A network error occurred.', 'error');
            })
            .finally(() => {
                this.closeForceLogoutModal();
            });
        },
        openDeleteModal(userId, username) {
            this.deleteModal.userId = userId;
            this.deleteModal.username = username;
            this.deleteModal.open = true;
        },
        closeDeleteModal() {
            this.deleteModal.open = false;
            this.deleteModal.userId = null;
            this.deleteModal.username = '';
        },
        submitDelete() {
            this.$event.target.submit();
        }
    }
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html> 