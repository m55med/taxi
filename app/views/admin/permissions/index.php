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
    const headers = document.querySelectorAll('.user-card-header');

    headers.forEach((header) => {
        header.addEventListener('click', function() {
            if (this.classList.contains('is-locked')) {
                return;
            }
            const content = this.nextElementSibling;
            if (content) {
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            }
        });
    });

    const userSearchInput = document.getElementById('userSearch');
    if (userSearchInput) {
        userSearchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const userCards = document.querySelectorAll('#usersContainer .user-card');
            
            userCards.forEach(card => {
                const name = card.dataset.userName || '';
                const email = card.dataset.userEmail || '';
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    document.querySelectorAll('.user-card').forEach(userCard => {
        updateParentToggles(userCard);
    });

    document.querySelectorAll('.master-toggle').forEach(masterToggle => {
        masterToggle.addEventListener('change', function() {
            const userCard = this.closest('.user-card');
            const isChecked = this.checked;
            this.indeterminate = false;

            userCard.querySelectorAll('.group-toggle').forEach(groupToggle => {
                groupToggle.checked = isChecked;
                groupToggle.indeterminate = false;
            });

            userCard.querySelectorAll('.permission-toggle:not(:disabled)').forEach(toggle => {
                if (toggle.checked !== isChecked) {
                    toggle.checked = isChecked;
                    updatePermission(toggle.dataset.userId, toggle.dataset.permissionId, isChecked);
                }
            });
        });
    });

    document.querySelectorAll('.group-toggle').forEach(groupToggle => {
        groupToggle.addEventListener('change', function() {
            const group = this.closest('.permission-group');
            const userCard = this.closest('.user-card');
            const isChecked = this.checked;
            this.indeterminate = false;

            group.querySelectorAll('.permission-toggle:not(:disabled)').forEach(toggle => {
                if (toggle.checked !== isChecked) {
                    toggle.checked = isChecked;
                    updatePermission(toggle.dataset.userId, toggle.dataset.permissionId, isChecked);
                }
            });

            updateParentToggles(userCard);
        });
    });

    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            if (this.disabled) return;
            const userCard = this.closest('.user-card');
            updatePermission(this.dataset.userId, this.dataset.permissionId, this.checked);
            updateParentToggles(userCard);
        });
    });

    function updateParentToggles(userCard) {
        if (!userCard) return;

        const masterToggle = userCard.querySelector('.master-toggle');
        if (!masterToggle) return;

        const allPermissions = userCard.querySelectorAll('.permission-toggle:not(:disabled)');
        const totalChecked = userCard.querySelectorAll('.permission-toggle:checked:not(:disabled)').length;

        if (totalChecked === 0) {
            masterToggle.checked = false;
            masterToggle.indeterminate = false;
        } else if (totalChecked === allPermissions.length) {
            masterToggle.checked = true;
            masterToggle.indeterminate = false;
        } else {
            masterToggle.checked = false; 
            masterToggle.indeterminate = true;
        }
        
        userCard.querySelectorAll('.permission-group').forEach(group => {
            const groupToggle = group.querySelector('.group-toggle');
            const groupPermissions = group.querySelectorAll('.permission-toggle:not(:disabled)');
            const groupCheckedCount = group.querySelectorAll('.permission-toggle:checked:not(:disabled)').length;

            if (groupCheckedCount === 0) {
                groupToggle.checked = false;
                groupToggle.indeterminate = false;
            } else if (groupCheckedCount === groupPermissions.length) {
                groupToggle.checked = true;
                groupToggle.indeterminate = false;
            } else {
                groupToggle.checked = false;
                groupToggle.indeterminate = true;
            }
        });
    }

    function updatePermission(userId, permissionId, isGranted) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('permission_id', permissionId);
        formData.append('grant', isGranted ? '1' : '0');
        
        fetch('<?= BASE_PATH ?>/admin/permissions/toggle', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'An unexpected error occurred', 'error');
                revertToggle(userId, permissionId, isGranted);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to connect to the server.', 'error');
            revertToggle(userId, permissionId, isGranted);
        });
    }

    function revertToggle(userId, permissionId, originalState) {
        const failedToggle = document.querySelector(`.permission-toggle[data-user-id="${userId}"][data-permission-id="${permissionId}"]`);
        if (failedToggle) {
            failedToggle.checked = !originalState;
            updateParentToggles(failedToggle.closest('.user-card'));
        }
    }

    window.showToast = function(message, type = 'success') {
        const toast = document.getElementById('toastNotification');
        if (!toast) return;
        
        toast.textContent = message;
        toast.className = 'toast-notification show';
        toast.classList.add(type);

        setTimeout(() => {
            toast.className = toast.className.replace('show', '');
        }, 3000);
    }
});
</script>
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>