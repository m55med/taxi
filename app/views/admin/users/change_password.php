<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex justify-between items-start">
        <div>
            <a href="<?= URLROOT ?>/admin/users" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Users</span>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
            <p class="text-gray-600 mt-1">Change password for: <strong><?= htmlspecialchars($data['user']->name) ?> (<?= htmlspecialchars($data['user']->username) ?>)</strong></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="<?= URLROOT ?>/admin/users/change_password/<?= $data['user']->id ?>" method="POST" class="space-y-6">
            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-2">New Password *</label>
                <input type="password" id="password" name="password" required minlength="6"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                       placeholder="Enter new password (minimum 6 characters)">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-bold text-gray-700 mb-2">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                       placeholder="Confirm new password">
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <a href="<?= URLROOT ?>/admin/users" class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors font-bold text-sm text-center">
                    Cancel
                </a>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-bold text-sm">
                    Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;

    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
