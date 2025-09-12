<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Edit Folder</h1>
                <a href="<?= URLROOT ?>/knowledge_base"
                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Knowledge Base
                </a>
            </div>

            <form action="<?= URLROOT ?>/knowledge_base/folders/update/<?= $folder['id'] ?>" method="POST" class="space-y-6">
                <!-- Folder Name -->
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                        Folder Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="<?= htmlspecialchars($folder['name'] ?? '') ?>"
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Enter folder name"
                           required>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                        Description
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="3"
                              class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Describe what this folder contains..."><?= htmlspecialchars($folder['description'] ?? '') ?></textarea>
                </div>

                <!-- Color Picker -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        Folder Color
                    </label>
                    <div class="grid grid-cols-6 gap-3">
                        <?php
                        $colors = [
                            '#3B82F6' => 'Blue',
                            '#EF4444' => 'Red',
                            '#10B981' => 'Green',
                            '#F59E0B' => 'Yellow',
                            '#8B5CF6' => 'Purple',
                            '#EC4899' => 'Pink',
                            '#6B7280' => 'Gray',
                            '#059669' => 'Emerald',
                            '#DC2626' => 'Red-600',
                            '#2563EB' => 'Blue-600',
                            '#7C3AED' => 'Violet',
                            '#EA580C' => 'Orange'
                        ];

                        $selectedColor = $folder['color'] ?? '#3B82F6';
                        foreach ($colors as $color => $name): ?>
                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="color"
                                       value="<?= $color ?>"
                                       <?= $selectedColor === $color ? 'checked' : '' ?>
                                       class="sr-only peer">
                                <div class="w-full aspect-square rounded-lg border-2 peer-checked:border-gray-900 peer-checked:ring-2 peer-checked:ring-offset-2 transition-all"
                                     style="background-color: <?= $color ?>; border-color: <?= $color === $selectedColor ? '#1F2937' : 'transparent' ?>;"></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Icon Selector -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        Folder Icon
                    </label>
                    <div class="grid grid-cols-6 gap-3">
                        <?php
                        $icons = [
                            'fas fa-folder' => 'Folder',
                            'fas fa-tools' => 'Tools',
                            'fas fa-question-circle' => 'Questions',
                            'fas fa-book' => 'Book',
                            'fas fa-rocket' => 'Rocket',
                            'fas fa-cog' => 'Settings',
                            'fas fa-users' => 'Users',
                            'fas fa-chart-bar' => 'Charts',
                            'fas fa-file-alt' => 'Document',
                            'fas fa-lightbulb' => 'Ideas',
                            'fas fa-shield-alt' => 'Security',
                            'fas fa-code' => 'Code'
                        ];

                        $selectedIcon = $folder['icon'] ?? 'fas fa-folder';
                        foreach ($icons as $icon => $name): ?>
                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="icon"
                                       value="<?= $icon ?>"
                                       <?= $selectedIcon === $icon ? 'checked' : '' ?>
                                       class="sr-only peer">
                                <div class="w-full aspect-square rounded-lg border-2 bg-gray-50 hover:bg-gray-100 flex items-center justify-center peer-checked:bg-indigo-100 peer-checked:border-indigo-500 transition-all">
                                    <i class="<?= $icon ?> text-2xl text-gray-600 peer-checked:text-indigo-600"></i>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="<?= URLROOT ?>/knowledge_base/folders/delete/<?= $folder['id'] ?>"
                       class="px-4 py-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>Delete Folder
                    </a>

                    <div class="flex items-center space-x-4">
                        <a href="<?= URLROOT ?>/knowledge_base"
                           class="px-6 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>Update Folder
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update color preview in real-time
document.querySelectorAll('input[name="color"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove previous selection styling
        document.querySelectorAll('input[name="color"]').forEach(r => {
            const div = r.parentElement.querySelector('div');
            div.style.borderColor = 'transparent';
        });

        // Add selection styling to current
        if (this.checked) {
            const div = this.parentElement.querySelector('div');
            div.style.borderColor = '#1F2937';
        }
    });
});

// Update icon preview in real-time
document.querySelectorAll('input[name="icon"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove previous selection styling
        document.querySelectorAll('input[name="icon"]').forEach(r => {
            const div = r.parentElement.querySelector('div');
            div.classList.remove('bg-indigo-100', 'border-indigo-500');
            const icon = div.querySelector('i');
            icon.classList.remove('text-indigo-600');
        });

        // Add selection styling to current
        if (this.checked) {
            const div = this.parentElement.querySelector('div');
            div.classList.add('bg-indigo-100', 'border-indigo-500');
            const icon = div.querySelector('i');
            icon.classList.add('text-indigo-600');
        }
    });
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
