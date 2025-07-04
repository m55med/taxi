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
                    Item to Discuss
                </h2>
                <div class="space-y-3">
                    <?php if ($discussable_type === 'review'): ?>
                        <p><strong>Type:</strong> Review</p>
                        <p><strong>Review Result:</strong> <span class="font-semibold <?= 
                            $item['review_result'] === 'accepted' ? 'text-green-600' : 
                            ($item['review_result'] === 'rejected' ? 'text-red-600' : 'text-yellow-600') 
                        ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($item['review_result']))) ?></span></p>
                        <p><strong>Reviewer:</strong> <?= htmlspecialchars($item['reviewer_name'] ?? 'N/A') ?></p>
                        <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($item['review_notes'] ?? 'None')) ?></p>
                        <p><strong>Reviewed At:</strong> <?= date('Y-m-d H:i', strtotime($item['reviewed_at'])) ?></p>
                    <?php else: ?>
                        <p><strong>Type:</strong> <?= htmlspecialchars($discussable_type) ?></p>
                        <p><strong>ID:</strong> <?= htmlspecialchars($discussable_id) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Discussion Form Column -->
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="<?= URLROOT ?>/discussions/add/<?= $discussable_type ?>/<?= $discussable_id ?>" method="POST">
                    
                    <!-- Reason -->
                    <div class="mb-6">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Discussion</label>
                        <input type="text" name="reason" id="reason" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="e.g., Disagree with review result">
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Detailed Notes</label>
                        <textarea name="notes" id="notes" rows="8" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Explain your point of view in detail..." required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Open Discussion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 