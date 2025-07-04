<?php
// Create a unique identifier for this review section instance
// to avoid DOM ID conflicts when this partial is rendered multiple times on the same page.
$review_unique_id_suffix = 'new';
if (isset($add_review_url)) {
    // Attempt to create a suffix from the URL, e.g., 'ticket_detail_123'
    $path_parts = explode('/', rtrim($add_review_url, '/'));
    $id_part = end($path_parts);
    $type_part = prev($path_parts);
    if (is_numeric($id_part) && is_string($type_part)) {
        $review_unique_id_suffix = htmlspecialchars($type_part) . '_' . htmlspecialchars($id_part);
    }
} else {
    // Fallback for a generic new review
    $review_unique_id_suffix = 'review_' . rand();
}
?>
<!-- Reviews Section -->
<div x-data="{ openReviewForm: false }" class="mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-700">
            <i class="fas fa-star-half-alt text-gray-400 mr-3"></i>Reviews
        </h3>
        <?php if (isset($can_add_review) && $can_add_review) : ?>
            <button @click="openReviewForm = !openReviewForm" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm font-medium" :aria-expanded="openReviewForm" aria-controls="review-form-<?= $review_unique_id_suffix ?>">
                <i class="fas fa-plus mr-1"></i> Add Review
            </button>
        <?php endif; ?>
    </div>

    <!-- Review Form -->
    <div x-show="openReviewForm" x-transition class="bg-gray-50 p-4 rounded-lg border mb-4" id="review-form-<?= $review_unique_id_suffix ?>" x-data="{ rating: 50 }">
        <form action="<?= isset($add_review_url) ? $add_review_url : '' ?>" method="POST">
            <div class="mb-3">
                <label for="rating_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <div class="flex items-center space-x-3">
                    <input type="range" id="rating_<?= $review_unique_id_suffix ?>" name="rating" min="0" max="100" x-model="rating" class="w-full">
                    <span x-text="rating" class="font-semibold text-blue-700"></span>
                </div>
            </div>
            <div class="mb-3">
                <label for="review_notes_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="review_notes" id="review_notes_<?= $review_unique_id_suffix ?>" rows="3" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Add notes..."></textarea>
            </div>
            <div class="flex justify-end">
                <button type="button" @click="openReviewForm = false" class="text-gray-600 mr-4">Cancel</button>
                <button type="submit" class="bg-green-500 text-white font-bold py-2 px-4 rounded-md hover:bg-green-600">Submit Review</button>
            </div>
        </form>
    </div>

    <!-- Existing Reviews -->
    <?php if (!empty($reviews)) : ?>
        <ul class="space-y-4">
            <?php foreach ($reviews as $review) : ?>
                <?php
                    $bgColor = 'bg-yellow-50 border-yellow-400'; // Default for mid-range
                    if ($review['rating'] >= 80) {
                        $bgColor = 'bg-green-50 border-green-400';
                    } elseif ($review['rating'] < 50) {
                        $bgColor = 'bg-red-50 border-red-400';
                    }
                ?>
                <li class="p-4 rounded-lg border-l-4 <?= $bgColor ?>">
                    <div class="flex justify-between items-center">
                        <p class="font-bold">
                            Reviewed by: <?= htmlspecialchars($review['reviewer_name']) ?>
                            - <span class="font-bold text-lg"><?= htmlspecialchars($review['rating']) ?>/100</span>
                        </p>
                        <span class="text-xs text-gray-500"><?= date('Y-m-d H:i', strtotime($review['reviewed_at'])) ?></span>
                    </div>
                    <?php if (!empty($review['review_notes'])) : ?>
                        <p class="mt-2 text-gray-700 bg-white p-2 rounded border border-gray-200"><?= nl2br(htmlspecialchars($review['review_notes'])) ?></p>
                    <?php endif; ?>

                    <!-- Discussion Section for this Review -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                         <?php
                            // Ensure all required variables are passed to the partial
                            $discussion_partial_data = [
                                'discussions' => $review['discussions'] ?? [],
                                'add_url' => BASE_PATH . "/discussions/add/review/" . $review['id'],
                                'can_add_discussion' => in_array($currentUser['role'], ['quality_manager', 'Team_leader', 'admin', 'developer']),
                                'currentUser' => $currentUser // Pass the user context down
                            ];
                            render_partial('tickets/partials/discussions_section.php', $discussion_partial_data);
                        ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
            No reviews for this item yet.
        </div>
    <?php endif; ?>
</div> 