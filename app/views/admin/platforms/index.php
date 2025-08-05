<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['platform_message'])) {
    $flashMessage = [
        'message' => $_SESSION['platform_message'],
        'type' => $_SESSION['platform_message_type'] ?? 'success'
    ];
    unset($_SESSION['platform_message'], $_SESSION['platform_message_type']);
}

// Check for edit state
$platformToEdit = null;
if (isset($_GET['edit_id'])) {
    $editId = $_GET['edit_id'];
    foreach ($data['platforms'] as $platform) {
        if ($platform['id'] == $editId) {
            $platformToEdit = $platform;
            break;
        }
    }
}
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">

    <!-- Flash Message -->
    <?php if ($flashMessage): ?>
        <div class="p-4 mb-6 rounded-lg shadow-lg text-white font-semibold <?= $flashMessage['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' ?>" role="alert">
            <p><?= htmlspecialchars($flashMessage['message']) ?></p>
        </div>
    <?php endif; ?>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Platforms</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Add/Edit Form Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4"><?= $platformToEdit ? 'Edit Platform' : 'Add New Platform' ?></h2>
                <form action="<?= $platformToEdit ? URLROOT . '/admin/platforms/update' : URLROOT . '/admin/platforms/store' ?>" method="POST">
                    
                    <?php if ($platformToEdit): ?>
                        <input type="hidden" name="id" value="<?= $platformToEdit['id'] ?>">
                    <?php endif; ?>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Platform Name</label>
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($platformToEdit['name'] ?? '') ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex items-center mt-4 space-x-2">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center">
                            <i class="fas <?= $platformToEdit ? 'fa-save' : 'fa-plus' ?>"></i>
                            <span class="ml-2"><?= $platformToEdit ? 'Save Changes' : 'Add Platform' ?></span>
                        </button>
                        <?php if ($platformToEdit): ?>
                            <a href="<?= URLROOT ?>/admin/platforms" class="w-auto bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Platforms List -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Existing Platforms</h2>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['platforms'])): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No platforms found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['platforms'] as $index => $platform): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $index + 1 ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($platform['name']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                                <a href="?edit_id=<?= $platform['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="<?= URLROOT ?>/admin/platforms/delete/<?= $platform['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete the platform \'<?= htmlspecialchars(addslashes($platform['name'])) ?>\'? This action cannot be undone.');">
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
