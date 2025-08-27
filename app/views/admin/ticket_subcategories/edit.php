<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Subcategory</h1>

    <div class="max-w-md mx-auto bg-white rounded-lg shadow-sm p-6">
        <form action="<?= BASE_URL ?>/admin/ticket_subcategories/update/<?= $data['subcategory']['id'] ?>" method="POST">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Subcategory Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($data['subcategory']['name']) ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" id="category_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <?php foreach ($data['ticket_categories'] as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($category['id'] == $data['subcategory']['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mt-6 flex justify-end gap-4">
                <a href="<?= BASE_URL ?>/admin/ticket_subcategories" class="px-6 py-2 rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium">Cancel</a>
                <button type="submit" class="px-6 py-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700 font-medium">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
