<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="p-4 lg:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-key mr-3 text-indigo-500"></i>
            Token Management
        </h1>
        <p class="text-gray-600 mt-1">Manage and monitor user authentication tokens</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center">
                <div class="bg-blue-100 text-blue-500 p-4 rounded-full mr-4">
                    <i class="fas fa-ticket-alt fa-2x"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Tokens</p>
                    <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']['total'] ?? '0') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center">
                <div class="bg-green-100 text-green-500 p-4 rounded-full mr-4">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Active Tokens</p>
                    <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']['active'] ?? '0') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center">
                <div class="bg-red-100 text-red-500 p-4 rounded-full mr-4">
                    <i class="fas fa-times-circle fa-2x"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Expired Tokens</p>
                    <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']['expired'] ?? '0') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center">
                <div class="bg-yellow-100 text-yellow-500 p-4 rounded-full mr-4">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Used Today</p>
                    <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']['used_today'] ?? '0') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
        <form method="GET" action="<?= URLROOT ?>/token-management" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- User Filter -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <option value="">All Users</option>
                    <?php foreach ($data['users_list'] as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($data['filters']['user_id'] == $user['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['name'] . ' (' . $user['username'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Team Filter -->
            <div>
                <label for="team_id" class="block text-sm font-medium text-gray-700 mb-1">Team</label>
                <select name="team_id" id="team_id" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <option value="">All Teams</option>
                    <?php foreach ($data['teams_list'] as $team): ?>
                        <option value="<?= $team['id'] ?>" <?= ($data['filters']['team_id'] == $team['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($team['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <option value="">All Status</option>
                    <option value="active" <?= ($data['filters']['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="expired" <?= ($data['filters']['status'] == 'expired') ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" id="date_from" class="w-full border-gray-300 rounded-lg shadow-sm"
                       value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" id="date_to" class="w-full border-gray-300 rounded-lg shadow-sm"
                       value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
                <a href="<?= URLROOT ?>/token-management" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Reset
                </a>
                <?php
                // فلترة GET parameters - استبعاد url و format
                $exportParams = $_GET;
                unset($exportParams['url']);
                unset($exportParams['format']);
                $exportQuery = !empty($exportParams) ? '&' . http_build_query($exportParams) : '';
                ?>
                <a href="<?= URLROOT ?>/token-management/export?format=csv<?= $exportQuery ?>"
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>
                <a href="<?= URLROOT ?>/token-management/export?format=json<?= $exportQuery ?>"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-file-code mr-2"></i> Export JSON
                </a>
            </div>
        </form>
    </div>

    <!-- Tokens Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="border-b-2 border-gray-200">
                    <tr>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">ID</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Token</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">User</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Team</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Created At</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Last Activity</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Expires After</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Status</th>
                        <th class="text-left py-4 px-6 uppercase font-semibold text-sm text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($data['tokens'])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-10 text-gray-500">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <p>No tokens found matching the selected criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['tokens'] as $token): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-4 px-6 font-mono text-sm">
                                    <?= htmlspecialchars($token['id']) ?>
                                </td>
                                <td class="py-4 px-6 font-mono text-sm">
                                    <span class="bg-gray-100 px-2 py-1 rounded text-xs" style="word-break: break-all;">
                                        <?= htmlspecialchars(substr($token['token'], 0, 20) . '...') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-gray-900">
                                        <?= htmlspecialchars($token['user_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($token['user_username']) ?> (ID: <?= $token['user_id'] ?>)
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <?= htmlspecialchars($token['team_name'] ?? 'No Team') ?>
                                </td>
                                <td class="py-4 px-6 font-mono text-sm">
                                    <?= htmlspecialchars($token['created_at']) ?>
                                </td>
                                <td class="py-4 px-6 font-mono text-sm">
                                    <?= htmlspecialchars($token['last_activity']) ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                        <?= htmlspecialchars($token['expires_after_minutes']) ?> min
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <?php if ($token['status'] === 'active'): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <i class="fas fa-times-circle mr-1"></i>Expired
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <button onclick="revokeToken(<?= $token['id'] ?>)"
                                                class="text-yellow-600 hover:text-yellow-900 p-1"
                                                title="Revoke Token">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <button onclick="deleteToken(<?= $token['id'] ?>)"
                                                class="text-red-600 hover:text-red-900 p-1"
                                                title="Delete Token">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// دالة إلغاء التوكن
function revokeToken(tokenId) {
    if (confirm('Are you sure you want to revoke this token? The user will need to log in again to get a new token.')) {
        fetch(`<?= URLROOT ?>/token-management/revoke/${tokenId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while revoking the token.', 'error');
        });
    }
}

// دالة حذف التوكن نهائياً
function deleteToken(tokenId) {
    if (confirm('Are you sure you want to permanently delete this token? This action cannot be undone.')) {
        fetch(`<?= URLROOT ?>/token-management/delete/${tokenId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the token.', 'error');
        });
    }
}

// دالة عرض الإشعارات
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-md text-white text-sm font-medium z-50 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // إزالة الإشعار بعد 3 ثواني
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s ease-out';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }, 3000);
}
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
