<?php include_once APPROOT . '/views/includes/header.php'; ?>

<main class="container mx-auto p-4 sm:p-6 lg:p-8">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
            <?= htmlspecialchars($page_main_title); ?>
        </h1>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </a>
    </div>

    <!-- Flash Messages -->
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Item Details Column -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3">
                    Reviewing Item
                </h2>
                <div class="space-y-3">
                    <?php if ($reviewable_type === 'driver_call'): ?>
                        <p><strong>Type:</strong> Driver Call</p>
                        <p><strong>Call By:</strong> <?= htmlspecialchars($item['staff_name'] ?? 'N/A') ?></p>
                        <p><strong>Call Status:</strong> <?= htmlspecialchars($item['call_status'] ?? 'N/A') ?></p>
                        <p><strong>Call Notes:</strong> <?= htmlspecialchars($item['notes'] ?? 'None') ?></p>
                        <p><strong>Call Time:</strong> <?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></p>
                    <?php else: ?>
                        <p><strong>Type:</strong> <?= htmlspecialchars($reviewable_type) ?></p>
                        <p><strong>ID:</strong> <?= htmlspecialchars($reviewable_id) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Review Form Column -->
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="<?= URLROOT ?>/review/add/<?= $reviewable_type ?>/<?= $reviewable_id ?>" method="POST" x-data="{ rating: 50 }">
                    
                    <!-- New Rating Slider -->
                    <div class="mb-6">
                        <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex items-center space-x-4">
                            <input type="range" id="rating" name="rating" min="0" max="100" x-model="rating" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            <span x-text="rating" class="font-bold text-lg text-blue-600 w-12 text-center"></span>
                        </div>
                    </div>

                    <!-- Review Notes -->
                    <div class="mb-6">
                        <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">Review Notes</label>
                        <textarea name="review_notes" id="review_notes" rows="6" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Provide detailed feedback here..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md flex items-center">
                            <i class="fas fa-check mr-2"></i>
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 