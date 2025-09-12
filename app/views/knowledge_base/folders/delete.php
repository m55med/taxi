<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Delete Folder</h1>
                <a href="<?= URLROOT ?>/knowledge_base"
                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Knowledge Base
                </a>
            </div>

            <!-- Warning Message -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-semibold text-red-800">Warning: This action cannot be undone!</h3>
                        <p class="text-red-700 mt-1">Deleting this folder will permanently remove it from the system.</p>
                    </div>
                </div>
            </div>

            <!-- Folder Information -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Folder Details</h2>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                             style="background: linear-gradient(135deg, <?= $folder['color'] ?>20, <?= $folder['color'] ?>30); border: 2px solid <?= $folder['color'] ?>40;">
                            <i class="<?= $folder['icon'] ?> text-xl" style="color: <?= $folder['color'] ?>"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($folder['name']) ?></h3>
                            <p class="text-gray-600"><?= htmlspecialchars($folder['description'] ?? 'No description') ?></p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Articles in folder:</span>
                                <span class="text-gray-900"><?= $articles_count ?> articles</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-900"><?= date('M d, Y', strtotime($folder['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impact Information -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-yellow-800 mb-3">What happens when you delete this folder?</h3>
                <ul class="space-y-2 text-yellow-700">
                    <li class="flex items-start">
                        <i class="fas fa-arrow-right text-yellow-600 mt-0.5 mr-2"></i>
                        <span>All articles (<?= $articles_count ?> articles) will be moved to the "عام" (General) folder</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-arrow-right text-yellow-600 mt-0.5 mr-2"></i>
                        <span>The folder will be permanently removed from the system</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-arrow-right text-yellow-600 mt-0.5 mr-2"></i>
                        <span>This action cannot be reversed</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-arrow-right text-yellow-600 mt-0.5 mr-2"></i>
                        <span>Article links and references will remain intact</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= URLROOT ?>/knowledge_base/folders/edit/<?= $folder['id'] ?>"
                   class="px-6 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit Instead
                </a>

                <a href="<?= URLROOT ?>/knowledge_base"
                   class="px-6 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition-colors duration-200">
                    Cancel
                </a>

                <form action="<?= URLROOT ?>/knowledge_base/folders/destroy/<?= $folder['id'] ?>" method="POST" class="inline-block">
                    <button type="submit"
                            class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200"
                            onclick="return confirm('Are you absolutely sure you want to delete this folder? This action cannot be undone.')">
                        <i class="fas fa-trash mr-2"></i>Delete Folder
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced confirmation dialog
function confirmDelete() {
    return confirm('Are you absolutely sure you want to delete this folder?\n\n• All articles will be moved to "General" folder\n• This action cannot be undone\n\nType "DELETE" to confirm:');
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
