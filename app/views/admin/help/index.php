<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
    </div>

    <?php flash('help_videos_success'); ?>
    <?php flash('help_videos_error'); ?>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <p class="text-gray-600 mb-6">
            Here you can map specific pages in the application to their corresponding YouTube help videos.
            The system automatically finds all main pages (index routes). Simply paste the 11-character YouTube Video ID into the field next to the page path.
        </p>

        <form action="<?= URLROOT ?>/admin/help-videos/save" method="POST">
            <div class="space-y-4">

                <?php if (empty($data['routes'])) : ?>
                    <p class="text-center text-gray-500 py-4">No index routes could be found automatically.</p>
                <?php else : ?>
                    <?php foreach ($data['routes'] as $route) : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            <div>
                                <label for="route-<?= htmlspecialchars($route) ?>" class="font-mono text-sm text-gray-800 bg-gray-100 p-2 rounded-md block">
                                    <?= htmlspecialchars($route) ?>
                                </label>
                            </div>
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fab fa-youtube text-red-500"></i>
                                    </div>
                                    <input type="text" 
                                           id="route-<?= htmlspecialchars($route) ?>" 
                                           name="mappings[<?= urlencode($route) ?>]" 
                                           value="<?= htmlspecialchars($data['mappings'][$route] ?? '') ?>" 
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           placeholder="YouTube Video ID">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300 ease-in-out">
                    <i class="fas fa-save mr-2"></i> Save All Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
