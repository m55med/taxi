<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['permission_message'])) {
    $flashMessage = [
        'message' => $_SESSION['permission_message'],
        'type' => $_SESSION['permission_message_type'] ?? 'success'
    ];
    unset($_SESSION['permission_message'], $_SESSION['permission_message_type']);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['page_main_title'] ?? 'Manage Permissions') ?></title>
    <style>
/* General Styles */
.permissions-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Role Selector */
.role-selector {
    margin-bottom: 20px;
}

/* Search Bar */
.search-bar {
    margin-bottom: 20px;
}

/* User Card */
.user-card {
    background: #fff;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: box-shadow 0.3s ease;
}
.user-card:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.user-card-header {
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
.user-card-header.is-locked {
    cursor: not-allowed;
    background-color: #f1f1f1;
}
.permissions-list {
    padding: 20px;
    display: none; /* Collapsed by default */
    background-color: #fafafa;
}
.permissions-list.is-expanded {
    display: block; /* Ensures visibility when expanded */
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 28px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #28a745; /* Green */
}
input:checked + .slider:before {
    transform: translateX(22px);
}

/* Disabled state for switch */
input:disabled + .slider {
    cursor: not-allowed;
    background-color: #e9ecef;
}

/* Permission Item */
.permission-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}
.permission-item:last-child {
    border-bottom: none;
}
.permission-description {
    font-size: 0.95rem;
}

/* Toast Notification for feedback */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #333;
    color: #fff;
    padding: 15px 25px;
    border-radius: 5px;
    z-index: 1050;
    opacity: 0;
    transition: opacity 0.5s, transform 0.5s;
    transform: translateY(-20px);
}
.toast-notification.show {
    opacity: 1;
    transform: translateY(0);
}
.toast-notification.success {
    background-color: #28a745;
}
.toast-notification.error {
    background-color: #dc3545;
}
    </style>
</head>
<body class="bg-gray-100">
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="permissions-container">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6"><?= htmlspecialchars($data['page_main_title']) ?></h1>

    <!-- Role Selector -->
        <div class="role-selector bg-white p-4 rounded-lg shadow-md max-w-lg mx-auto">
        <form action="" method="GET">
            <div class="form-group">
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Select a role to view users and edit permissions:</label>
                    <select name="role_id" id="role_id" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        <option value="">-- Select Role --</option>
                    <?php foreach ($data['roles'] as $role) : ?>
                        <option value="<?= $role['id'] ?>" <?= ($data['selectedRoleId'] == $role['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
            </div>

    <?php if ($data['selectedRoleId']) : ?>
        <!-- Default Role Permissions -->
        <div class="user-card" id="role-permissions-card">
            <div class="user-card-header">
                <strong>Default Permissions for <?= htmlspecialchars(array_values(array_filter($data['roles'], fn($role) => $role['id'] == $data['selectedRoleId']))[0]['name'] ?? 'Role') ?></strong>
                <span>Click to view/hide permissions</span>
            </div>
            <div class="permissions-list" data-role-id="<?= $data['selectedRoleId'] ?>">
                <div class="permission-item master-toggle-item p-2 bg-gray-100 rounded mb-3">
                    <span class="font-bold text-blue-600">Toggle All</span>
                    <label class="switch">
                        <input type="checkbox" class="master-toggle">
                        <span class="slider"></span>
                    </label>
                </div>

                <?php foreach ($data['permissions'] as $groupName => $permissions) : ?>
                    <div class="permission-group mb-4 border border-gray-200 p-3 rounded">
                        <div class="permission-item group-header-item">
                            <h5 class="font-bold text-lg"><?= htmlspecialchars($groupName) ?></h5>
                            <label class="switch">
                                <input type="checkbox" class="group-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="permission-group-items pt-2">
                            <?php foreach ($permissions as $permission) : ?>
                                <div class="permission-item">
                                    <span class="permission-description"><?= htmlspecialchars($permission['description']) ?></span>
                                    <label class="switch">
                                        <input type="checkbox"
                                               class="role-permission-toggle"
                                               data-role-id="<?= $data['selectedRoleId'] ?>"
                                               data-permission-id="<?= $permission['id'] ?>"
                                               <?= in_array($permission['id'], $data['rolePermissions']) ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Search Bar -->
            <div class="search-bar max-w-lg mx-auto mt-4">
                <input type="text" id="userSearch" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" placeholder="Search for a user by name or email...">
        </div>
        
        <!-- Users and Permissions -->
            <div id="usersContainer" class="mt-6 space-y-4">
            <?php if (empty($data['users'])) : ?>
                    <p class="text-center text-gray-500">No users found in this role.</p>
            <?php else : ?>
                <?php foreach ($data['users'] as $user) :
                    // Gracefully handle missing role_name
                    $roleName = $user['role_name'] ?? '';
                    $isLocked = in_array(strtolower($roleName), ['admin', 'developer']);
                ?>
                    <div class="user-card" data-user-name="<?= strtolower(htmlspecialchars($user['username'])) ?>" data-user-email="<?= strtolower(htmlspecialchars($user['email'])) ?>">
                        <div class="user-card-header <?= $isLocked ? 'is-locked' : '' ?>">
                            <strong><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</strong>
                                <span><?= $isLocked ? '🔒 Fixed Permissions' : 'Click to view/hide permissions' ?></span>
                        </div>
                        <div class="permissions-list" data-user-id="<?= $user['id'] ?>">
                            <!-- Master Toggle Switch -->
                            <div class="permission-item master-toggle-item p-2 bg-gray-100 rounded mb-3">
                                    <span class="font-bold text-blue-600">Toggle All</span>
                                <label class="switch">
                                        <input type="checkbox" class="master-toggle" <?= $isLocked ? 'disabled' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <?php foreach ($data['permissions'] as $groupName => $permissions) : ?>
                                <div class="permission-group mb-4 border border-gray-200 p-3 rounded">
                                    <div class="permission-item group-header-item">
                                            <h5 class="font-bold text-lg"><?= htmlspecialchars($groupName) ?></h5>
                                        <label class="switch">
                                                <input type="checkbox" class="group-toggle" <?= $isLocked ? 'disabled' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-group-items pt-2">
                                        <?php foreach ($permissions as $permission) : ?>
                                            <div class="permission-item">
                                                <span class="permission-description"><?= htmlspecialchars($permission['description']) ?></span>
                                                <label class="switch">
                                            <input type="checkbox"
                                                        class="permission-toggle"
                                                            data-user-id="<?= $user['id'] ?>"
                                                        data-permission-id="<?= $permission['id'] ?>"
                                                        <?= in_array($permission['permission_key'], $data['userPermissions'][$user['id']] ?? []) ? 'checked' : '' ?>
                                                        <?= $isLocked ? 'disabled' : '' ?>>
                                                    <span class="slider"></span>
                                        </label>
                                            </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Toast Notification Container -->
<div id="toastNotification" class="toast-notification"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Helper Functions ---

    /**
     * Shows a toast notification.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error'.
     */
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toastNotification');
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast-notification show ' + type;
        setTimeout(() => {
            toast.className = toast.className.replace('show', '');
        }, 3000);
    }

    /**
     * Handles the API call for a single permission change.
     * @param {HTMLInputElement} toggle - The checkbox element that was changed.
     * @returns {Promise} A promise that resolves or rejects based on the fetch call.
     */
    function handlePermissionChange(toggle) {
        const isRoleToggle = toggle.classList.contains('role-permission-toggle');
        const isGranted = toggle.checked;
        const permissionId = toggle.dataset.permissionId;
        const ownerId = isRoleToggle ? toggle.dataset.roleId : toggle.dataset.userId;

        const formData = new FormData();
        formData.append('permission_id', permissionId);
        formData.append('grant', isGranted ? '1' : '0');

        let url;
        if (isRoleToggle) {
            url = `<?= URLROOT ?>/admin/permissions/toggleRolePermission`;
            formData.append('role_id', ownerId);
        } else {
            url = `<?= URLROOT ?>/admin/permissions/toggle`;
            formData.append('user_id', ownerId);
        }

        return fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    return Promise.reject(data);
                }
                // The backend now signals on its own for user toggles
                return Promise.resolve(data);
            })
            .catch(err => {
                // The toggle state is reverted in the caller on failure
                return Promise.reject(err);
            });
    }

    /**
     * Handles batch updates for a collection of toggles.
     * @param {NodeList} toggles - The checkboxes to update.
     * @param {boolean} isGranted - The state to set.
     * @param {string|int} ownerId - The role_id or user_id.
     * @param {boolean} isRoleUpdate - True if this is a role update.
     * @returns {Promise}
     */
    function handleBatchUpdate(toggles, isGranted, ownerId, isRoleUpdate) {
        const permissionIds = Array.from(toggles).map(t => t.dataset.permissionId).filter(id => id);
        if (permissionIds.length === 0) return Promise.resolve({success: true, message: 'No permissions to update.'});

        const url = isRoleUpdate
            ? `<?= URLROOT ?>/admin/permissions/batchUpdateRolePermissions`
            : `<?= URLROOT ?>/admin/permissions/batchUpdateUserPermissions`;
        
        const body = {
            permission_ids: permissionIds,
            grant: isGranted,
        };
        
        if (isRoleUpdate) {
            body.role_id = ownerId;
        } else {
            body.user_id = ownerId;
        }

        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return Promise.reject(data);
            return Promise.resolve(data);
        });
    }

    /**
     * Updates the state of a group toggle based on its item toggles.
     * @param {Element} groupEl - The .permission-group element.
     */
    function updateGroupToggleState(groupEl) {
        const groupToggle = groupEl.querySelector('.group-toggle');
        if (!groupToggle) return;
        const itemToggles = groupEl.querySelectorAll('.permission-toggle, .role-permission-toggle');
        const allChecked = itemToggles.length > 0 && Array.from(itemToggles).every(t => t.checked);
        groupToggle.checked = allChecked;
    }

    /**
     * Updates the state of a master toggle based on all item toggles in its list.
     * @param {Element} listEl - The .permissions-list element.
     */
    function updateMasterToggleState(listEl) {
        const masterToggle = listEl.querySelector('.master-toggle');
        if (!masterToggle) return;
        const allToggles = listEl.querySelectorAll('.permission-toggle, .role-permission-toggle');
        const allChecked = allToggles.length > 0 && Array.from(allToggles).every(t => t.checked);
        masterToggle.checked = allChecked;
    }

    // --- Main Logic ---

    // Expand/Collapse card content
    document.querySelectorAll('.user-card-header').forEach(header => {
        if (!header.classList.contains('is-locked')) {
            header.addEventListener('click', function() {
                this.nextElementSibling.classList.toggle('is-expanded');
            });
        }
    });

    // User search filtering
    const userSearch = document.getElementById('userSearch');
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('#usersContainer .user-card').forEach(card => {
                const name = card.dataset.userName || '';
                const email = card.dataset.userEmail || '';
                card.style.display = (name.includes(searchTerm) || email.includes(searchTerm)) ? '' : 'none';
            });
        });
    }

    // Initialize all permission lists
    document.querySelectorAll('.permissions-list').forEach(list => {
        const allItemsInList = list.querySelectorAll('.permission-toggle, .role-permission-toggle');
        const groupsInList = list.querySelectorAll('.permission-group');
        const masterToggle = list.querySelector('.master-toggle');
        const isRoleUpdate = list.dataset.roleId !== undefined;
        const ownerId = isRoleUpdate ? list.dataset.roleId : list.dataset.userId;

        // Event listener for individual toggles
        allItemsInList.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const originalState = !this.checked;
                handlePermissionChange(this)
                    .then(() => {
                        const parentGroup = this.closest('.permission-group');
                        if (parentGroup) updateGroupToggleState(parentGroup);
                        updateMasterToggleState(list);
                    })
                    .catch(() => {
                        this.checked = originalState; // Revert on failure
                        showToast('Update failed. Please try again.', 'error');
                        const parentGroup = this.closest('.permission-group');
                        if (parentGroup) updateGroupToggleState(parentGroup);
                        updateMasterToggleState(list);
                    });
            });
        });

        // Event listener for group toggles
        groupsInList.forEach(group => {
            const groupToggle = group.querySelector('.group-toggle');
            if (groupToggle) {
                groupToggle.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const itemsInGroup = group.querySelectorAll('.permission-toggle, .role-permission-toggle');
                    
                    itemsInGroup.forEach(item => item.checked = isChecked);

                    handleBatchUpdate(itemsInGroup, isChecked, ownerId, isRoleUpdate)
                        .then(data => showToast(data.message || 'Group permissions updated.', 'success'))
                        .catch(() => {
                            showToast('Group update failed.', 'error');
                            itemsInGroup.forEach(item => item.checked = !isChecked);
                        })
                        .finally(() => updateMasterToggleState(list));
                });
            }
        });

        // Event listener for master toggle
        if (masterToggle) {
            masterToggle.addEventListener('change', function() {
                const isChecked = this.checked;
                
                allItemsInList.forEach(item => item.checked = isChecked);
                groupsInList.forEach(group => {
                    const groupToggle = group.querySelector('.group-toggle');
                    if(groupToggle) groupToggle.checked = isChecked;
                });

                handleBatchUpdate(allItemsInList, isChecked, ownerId, isRoleUpdate)
                    .then(data => showToast(data.message || 'All permissions updated.', 'success'))
                    .catch(() => {
                        showToast('Update for all permissions failed.', 'error');
                        allItemsInList.forEach(item => item.checked = !isChecked);
                        groupsInList.forEach(group => {
                            const groupToggle = group.querySelector('.group-toggle');
                            if(groupToggle) groupToggle.checked = !isChecked;
                        });
                    });
            });
        }

        // Set initial state on page load
        groupsInList.forEach(updateGroupToggleState);
        updateMasterToggleState(list);
    });
});
</script>
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>