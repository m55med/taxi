<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأدوار</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>

<?php require APPROOT . '/app/views/includes/nav.php'; ?>

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
        bottom: 20px;
        right: 20px;
        background-color: #333;
        color: #fff;
        padding: 15px 25px;
        border-radius: 5px;
        z-index: 1050;
        opacity: 0;
        transition: opacity 0.5s, transform 0.5s;
        transform: translateY(20px);
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

<div class="permissions-container">
    <h1 class="text-center mb-4"><?= $data['page_main_title'] ?></h1>

    <!-- Role Selector -->
    <div class="role-selector">
        <form action="" method="GET">
            <div class="form-group">
                <label for="role_id">اختر دورًا لعرض المستخدمين وتعديل صلاحياتهم:</label>
                <select name="role_id" id="role_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- اختر دور --</option>
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
        <div class="search-bar">
            <input type="text" id="userSearch" class="form-control" placeholder="ابحث عن مستخدم بالاسم أو البريد الإلكتروني...">
        </div>
        
        <!-- Users and Permissions -->
        <div id="usersContainer">
            <?php if (empty($data['users'])) : ?>
                <p class="text-center text-muted">لا يوجد مستخدمون في هذا الدور.</p>
            <?php else : ?>
                <?php foreach ($data['users'] as $user) :
                    $isLocked = in_array(strtolower($user['role_name']), ['admin', 'developer']);
                ?>
                    <div class="user-card" data-user-name="<?= strtolower(htmlspecialchars($user['username'])) ?>" data-user-email="<?= strtolower(htmlspecialchars($user['email'])) ?>">
                        <div class="user-card-header <?= $isLocked ? 'is-locked' : '' ?>">
                            <strong><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</strong>
                            <span><?= $isLocked ? '🔒 صلاحيات ثابتة' : 'اضغط لعرض/إخفاء الصلاحيات' ?></span>
                        </div>
                        <div class="permissions-list" data-user-id="<?= $user['id'] ?>">
                            <!-- Master Toggle Switch -->
                            <div class="permission-item master-toggle-item p-2 bg-gray-100 rounded mb-3">
                                <span class="font-bold text-blue-600">تحديد الكل / إلغاء تحديد الكل</span>
                                <label class="switch">
                                    <input type="checkbox" class="master-toggle">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <?php foreach ($data['permissions'] as $groupName => $permissions) : ?>
                                <div class="permission-group mb-4 border border-gray-200 p-3 rounded">
                                    <div class="permission-item group-header-item">
                                        <h5 class="font-bold text-lg cursor-pointer" onclick="this.closest('.permission-group').querySelector('.permission-group-items').classList.toggle('hidden')">
                                            <?= htmlspecialchars($groupName) ?>
                                        </h5>
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
                                                        class="permission-toggle"
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
    console.log("DOM fully loaded and parsed");

    const headers = document.querySelectorAll('.user-card-header');
    console.log(`Found ${headers.length} user-card-header elements.`);

    headers.forEach((header, index) => {
        console.log(`Attaching click listener to header #${index + 1}`);
        header.addEventListener('click', function() {
            console.log(`Header #${index + 1} was clicked.`);
            
            if (this.classList.contains('is-locked')) {
                console.log("Action blocked: Card is locked.");
                return;
            }

            const content = this.nextElementSibling;
            if (content) {
                if (content.style.display === "block") {
                    console.log("Closing permissions list.");
                    content.style.display = "none";
                } else {
                    console.log("Opening permissions list.");
                    content.style.display = "block";
                }
            } else {
                console.error("Could not find the permissions list for this header.");
            }
        });
    });

    // User Search
    const userSearchInput = document.getElementById('userSearch');
    if (userSearchInput) {
        console.log("Attaching keyup listener to search input.");
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
    } else {
        console.log("Search input not found.");
    }

    // Initialize parent toggle states for each user card on load
    document.querySelectorAll('.user-card').forEach(userCard => {
        updateParentToggles(userCard);
    });

    // --- Master Toggle Logic ---
    document.querySelectorAll('.master-toggle').forEach(masterToggle => {
        masterToggle.addEventListener('change', function() {
            const userCard = this.closest('.user-card');
            const isChecked = this.checked;
            this.indeterminate = false; // A manual click resolves indeterminate state

            // Update all child group toggles
            userCard.querySelectorAll('.group-toggle').forEach(groupToggle => {
                groupToggle.checked = isChecked;
                groupToggle.indeterminate = false;
            });

            // Update all individual permission toggles and send requests
            userCard.querySelectorAll('.permission-toggle:not(:disabled)').forEach(toggle => {
                if (toggle.checked !== isChecked) {
                    toggle.checked = isChecked;
                    updatePermission(userCard.querySelector('.permissions-list').dataset.userId, toggle.dataset.permissionId, isChecked);
                }
            });
        });
    });

    // --- Group Toggle Logic ---
    document.querySelectorAll('.group-toggle').forEach(groupToggle => {
        groupToggle.addEventListener('change', function() {
            const group = this.closest('.permission-group');
            const userCard = this.closest('.user-card');
            const isChecked = this.checked;
            this.indeterminate = false; // A manual click resolves indeterminate state

            // Update permissions within this group
            group.querySelectorAll('.permission-toggle:not(:disabled)').forEach(toggle => {
                if (toggle.checked !== isChecked) {
                    toggle.checked = isChecked;
                    updatePermission(userCard.querySelector('.permissions-list').dataset.userId, toggle.dataset.permissionId, isChecked);
                }
            });

            // Re-evaluate the master toggle's state
            updateParentToggles(userCard);
        });
    });

    // --- Individual Permission Toggle Logic ---
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            if (this.disabled) return;
            const userCard = this.closest('.user-card');
            updatePermission(userCard.querySelector('.permissions-list').dataset.userId, this.dataset.permissionId, this.checked);
            updateParentToggles(userCard); // Update parent states after a change
        });
    });

    // --- State Update Function for Parent Toggles ---
    function updateParentToggles(userCard) {
        if (!userCard) return;

        const masterToggle = userCard.querySelector('.master-toggle');
        if (!masterToggle) return;

        const allPermissions = userCard.querySelectorAll('.permission-toggle:not(:disabled)');
        const totalChecked = userCard.querySelectorAll('.permission-toggle:checked:not(:disabled)').length;

        // Update master toggle state
        if (totalChecked === 0) {
            masterToggle.checked = false;
            masterToggle.indeterminate = false;
        } else if (totalChecked === allPermissions.length) {
            masterToggle.checked = true;
            masterToggle.indeterminate = false;
        } else {
            masterToggle.checked = false; // Or true, doesn't matter when indeterminate
            masterToggle.indeterminate = true;
        }
        
        // Update each group toggle state
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

    // --- Centralized function for AJAX update ---
    function updatePermission(userId, permissionId, isChecked) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('permission_id', permissionId);
        formData.append('checked', isChecked ? '1' : '0');
        
        fetch('<?= URLROOT ?>/admin/permissions/save', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'An unexpected error occurred', 'error');
                // Revert the toggle on failure - find the specific toggle and revert it
                const failedToggle = document.querySelector(`.permission-toggle[data-user-id="${userId}"][data-permission-id="${permissionId}"]`);
                if (failedToggle) {
                    failedToggle.checked = !isChecked;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to connect to the server.', 'error');
            const failedToggle = document.querySelector(`.permission-toggle[data-user-id="${userId}"][data-permission-id="${permissionId}"]`);
            if (failedToggle) {
                failedToggle.checked = !isChecked;
            }
        });
    }

    // --- Toast Notification ---
    window.showToast = function(message, type = 'success') {
        const toast = document.getElementById('toastNotification');
        if (!toast) return;
        
        toast.textContent = message;
        toast.className = 'toast-notification show';
        toast.classList.add(type); // 'success' or 'error'

        setTimeout(() => {
            toast.className = toast.className.replace('show', '');
        }, 3000);
    }
});
</script>

<?php require APPROOT . '/app/views/inc/footer.php'; ?>