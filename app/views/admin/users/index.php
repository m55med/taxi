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



<body class="bg-gray-100">
    <div x-data="usersPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">

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
                        <!-- Debug: Show actual count -->
                        <?php 
                        $onlineCount = 0;
                        if (!empty($data['users'])) {
                            foreach ($data['users'] as $user) {
                                if (!empty($user->is_online)) {
                                    $onlineCount++;
                                }
                            }
                        }
                        ?>
                        <p class="text-xs text-gray-400 mt-1">Debug: Actual in table: <?= $onlineCount ?></p>
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
                    <a href="<?= URLROOT ?>/admin/users/create" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-user-plus mr-1"></i>
                        Add New User
                    </a>
                </div>

                <!-- Filters Section -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-800">
                            <i class="fas fa-filter mr-2"></i>Filters
                            <span x-show="getActiveFiltersCount() > 0" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800" x-text="getActiveFiltersCount() + ' active'"></span>
                        </h3>
                        <button @click="resetFilters()" :class="getActiveFiltersCount() > 0 ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-500 hover:bg-gray-600'" class="text-white px-3 py-1 rounded-md text-sm transition-colors duration-200">
                            <i class="fas fa-undo mr-1"></i>Reset Filters
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID</label>
                            <input type="text" x-model="filters.id" @input="applyFilters()" placeholder="Search by ID..." :class="filters.id ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" x-model="filters.username" @input="applyFilters()" placeholder="Search by username..." :class="filters.username ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" x-model="filters.name" @input="applyFilters()" placeholder="Search by name..." :class="filters.name ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="text" x-model="filters.email" @input="applyFilters()" placeholder="Search by email..." :class="filters.email ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select x-model="filters.role" @change="applyFilters()" :class="filters.role ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="Agent">Agent</option>
                                <option value="user">User</option>
                                <option value="employee">Employee</option>
                                <option value="Marketer">Marketer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select x-model="filters.status" @change="applyFilters()" :class="filters.status ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="banned">Banned</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" x-model="filters.dateFrom" @change="applyFilters()" :class="filters.dateFrom ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" x-model="filters.dateTo" @change="applyFilters()" :class="filters.dateTo ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Online Status</label>
                            <select x-model="filters.isOnline" @change="applyFilters()" :class="filters.isOnline ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'" class="w-full rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-colors duration-200">
                                <option value="">All Users</option>
                                <option value="online">Online Only</option>
                                <option value="offline">Offline Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-600">
                        <span x-text="getFilteredRowsCount()"></span> users displayed
                    </div>
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
                                    Name
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
                                <?php 
                                // Remove duplicates by ID (final safety check in view)
                                $displayedUsers = [];
                                $displayedIds = [];
                                foreach ($data['users'] as $user) {
                                    $userId = is_object($user) ? $user->id : (is_array($user) ? $user['id'] : null);
                                    if ($userId && !in_array($userId, $displayedIds)) {
                                        $displayedUsers[] = $user;
                                        $displayedIds[] = $userId;
                                    }
                                }
                                ?>
                                <?php foreach ($displayedUsers as $user): ?>
                                <?php 
                                    // Ensure is_online is properly converted to 1 or 0
                                    $isOnline = isset($user->is_online) && ($user->is_online === true || $user->is_online === 1 || $user->is_online === '1');
                                ?>
                                <tr data-is-online="<?= $isOnline ? '1' : '0' ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($user->id ?? '') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full align-middle <?= ($user->is_online ?? 0) ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                                            <div class="text-sm font-medium text-gray-900 ml-2">
                                                <?= htmlspecialchars($user->username ?? '') ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user->name ?? '') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user->email ?? '') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars(ucfirst($user->role_name ?? 'user')) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status = $user->status ?? 'pending';
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
                                        if (!empty($user->updated_at)) {
                                            $updated_at = new DateTime($user->updated_at);
                                            echo $updated_at->format('Y-m-d H:i');
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <?php if (($user->id ?? '') !== ($_SESSION['user_id'] ?? '')): ?>
                                            <a href="<?= URLROOT ?>/admin/users/edit/<?= htmlspecialchars($user->id ?? '') ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= URLROOT ?>/admin/users/change_password/<?= htmlspecialchars($user->id ?? '') ?>" class="text-green-600 hover:text-green-900 mr-3" title="Change Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <button @click="openForceLogoutModal(<?= htmlspecialchars($user->id ?? '') ?>, '<?= htmlspecialchars($user->username ?? '') ?>')" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Force Logout">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </button>
                                            <button @click="openDeleteModal(<?= htmlspecialchars($user->id ?? '') ?>, '<?= htmlspecialchars($user->username ?? '') ?>')" class="text-red-600 hover:text-red-900" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= URLROOT ?>/admin/users/change_password/<?= htmlspecialchars($user->id ?? '') ?>" class="text-indigo-600 hover:text-indigo-900" title="Change Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="no-users-row">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No users found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr id="no-filtered-results" style="display: none;">
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-600">No users match your filters</p>
                                        <p class="text-sm text-gray-500 mt-2">Try adjusting your search criteria or <button @click="resetFilters()" class="text-indigo-600 hover:text-indigo-800 underline">reset all filters</button></p>
                                    </div>
                                </td>
                            </tr>
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
                <form @submit.prevent="submitDelete" method="POST" action="<?= URLROOT ?>/admin/users/destroy">
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
        filters: {
            id: '',
            username: '',
            name: '',
            email: '',
            role: '',
            status: '',
            dateFrom: '',
            dateTo: '',
            isOnline: ''
        },
        init() {
            if (flashMessage && flashMessage.message) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        },
        applyFilters() {
            const tableBody = document.querySelector('tbody');
            const rows = tableBody.querySelectorAll('tr');
            let visibleRowsCount = 0;
            let hasActiveFilters = this.hasActiveFilters();
            
            rows.forEach(row => {
                // Skip the special rows (no-users and no-filtered-results)
                if (row.id === 'no-users-row' || row.id === 'no-filtered-results') return;
                if (row.cells.length < 8) return; // Skip any other special rows
                
                // Check if user is online using data attribute (more reliable)
                const isOnlineAttr = row.getAttribute('data-is-online');
                // Convert to boolean - '1' means online, anything else means offline
                const isOnline = String(isOnlineAttr) === '1';
                
                // Extract username properly (skip the green dot indicator)
                const usernameCell = row.cells[1];
                const usernameDiv = usernameCell.querySelector('.text-sm.font-medium');
                const username = usernameDiv ? usernameDiv.textContent.trim() : row.cells[1].textContent.trim();
                
                const rowData = {
                    id: row.cells[0].textContent.trim(),
                    username: username,
                    name: row.cells[2].textContent.trim(), 
                    email: row.cells[3].textContent.trim(),
                    role: row.cells[4].textContent.trim().toLowerCase(),
                    status: row.cells[5].textContent.trim().toLowerCase(),
                    lastActivity: row.cells[6].textContent.trim(),
                    isOnline: isOnline
                };
                
                let shouldShow = true;
                
                // Filter by ID
                if (this.filters.id && !rowData.id.includes(this.filters.id)) {
                    shouldShow = false;
                }
                
                // Filter by username
                if (this.filters.username && !rowData.username.toLowerCase().includes(this.filters.username.toLowerCase())) {
                    shouldShow = false;
                }
                
                // Filter by name  
                if (this.filters.name && !rowData.name.toLowerCase().includes(this.filters.name.toLowerCase())) {
                    shouldShow = false;
                }
                
                // Filter by email
                if (this.filters.email && !rowData.email.toLowerCase().includes(this.filters.email.toLowerCase())) {
                    shouldShow = false;
                }
                
                // Filter by role
                if (this.filters.role && !rowData.role.includes(this.filters.role.toLowerCase())) {
                    shouldShow = false;
                }
                
                // Filter by status
                if (this.filters.status && !rowData.status.includes(this.filters.status.toLowerCase())) {
                    shouldShow = false;
                }
                
                // Filter by date range
                if (this.filters.dateFrom || this.filters.dateTo) {
                    const lastActivityDate = this.parseDate(rowData.lastActivity);
                    if (lastActivityDate) {
                        if (this.filters.dateFrom) {
                            const fromDate = new Date(this.filters.dateFrom);
                            if (lastActivityDate < fromDate) {
                                shouldShow = false;
                            }
                        }
                        if (this.filters.dateTo) {
                            const toDate = new Date(this.filters.dateTo);
                            toDate.setHours(23, 59, 59); // Include the whole day
                            if (lastActivityDate > toDate) {
                                shouldShow = false;
                            }
                        }
                    } else if (this.filters.dateFrom || this.filters.dateTo) {
                        // If we have date filters but no valid date in the row, hide it
                        shouldShow = false;
                    }
                }
                
                // Filter by online status - must be checked after all other filters
                if (this.filters.isOnline && this.filters.isOnline !== '') {
                    if (this.filters.isOnline === 'online') {
                        // Only show if user is online
                        if (!rowData.isOnline) {
                            shouldShow = false;
                        }
                    } else if (this.filters.isOnline === 'offline') {
                        // Only show if user is offline
                        if (rowData.isOnline) {
                            shouldShow = false;
                        }
                    }
                }
                
                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleRowsCount++;
            });
            
            // Show/hide the "no filtered results" message
            const noFilteredResults = document.getElementById('no-filtered-results');
            const noUsersRow = document.getElementById('no-users-row');
            
            if (hasActiveFilters && visibleRowsCount === 0) {
                if (noFilteredResults) noFilteredResults.style.display = '';
                if (noUsersRow) noUsersRow.style.display = 'none';
            } else {
                if (noFilteredResults) noFilteredResults.style.display = 'none';
                // Show original "no users" message only if no filters are active and no data rows exist
                if (noUsersRow && !hasActiveFilters) {
                    const dataRows = Array.from(rows).filter(row => 
                        row.id !== 'no-users-row' && 
                        row.id !== 'no-filtered-results' && 
                        row.cells.length >= 8
                    );
                    noUsersRow.style.display = dataRows.length === 0 ? '' : 'none';
                } else if (noUsersRow) {
                    noUsersRow.style.display = 'none';
                }
            }
        },
        hasActiveFilters() {
            return Object.values(this.filters).some(value => value !== '');
        },
        getActiveFiltersCount() {
            return Object.values(this.filters).filter(value => value !== '').length;
        },
        resetFilters() {
            this.filters = {
                id: '',
                username: '',
                name: '',
                email: '',
                role: '',
                status: '',
                dateFrom: '',
                dateTo: '',
                isOnline: ''
            };
            this.applyFilters();
        },
        getFilteredRowsCount() {
            const tableBody = document.querySelector('tbody');
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                // Skip special rows and only count data rows
                if (row.id === 'no-users-row' || row.id === 'no-filtered-results') return;
                if (row.style.display !== 'none' && row.cells.length >= 8) {
                    visibleCount++;
                }
            });
            
            return visibleCount;
        },
        parseDate(dateString) {
            if (!dateString || dateString === 'N/A') return null;
            
            // Try to parse different date formats
            const date = new Date(dateString);
            return isNaN(date.getTime()) ? null : date;
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

            fetch('<?= URLROOT ?>/admin/users/forceLogout', {
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
</div>
</body>

</html> 