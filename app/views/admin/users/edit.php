<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<body class="bg-gray-100">

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
                <a href="<?= URLROOT ?>/admin/users" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to List
                </a>
            </div>

            <?php if (isset($data['user'])): ?>
                <form action="<?= URLROOT ?>/admin/users/update/<?= htmlspecialchars($data['user']->id) ?>" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" readonly
                            value="<?= htmlspecialchars($data['user']->username) ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" required
                            value="<?= htmlspecialchars($data['user']->name) ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required
                            value="<?= htmlspecialchars($data['user']->email) ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password (leave blank if you don't want to change it)</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role_id" id="role" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <?php foreach ($data['roles'] as $role): ?>
                                <option value="<?= htmlspecialchars($role->id) ?>"
                                    <?= ($data['user']->role_id ?? '') == $role->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="active" <?= ($data['user']->status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="pending" <?= ($data['user']->status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="banned" <?= ($data['user']->status ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center text-gray-500">
                    User not found.
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?> 