<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-semibold text-gray-700 flex items-center">
        <i class="fas fa-clipboard-check text-gray-400 mr-3"></i>
        Ticket Reviews
    </h2>
    <?php if (in_array($data['currentUser']['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])): ?>
        <button @click="openReviewForm = !openReviewForm" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 text-sm">
            <i class="fas fa-plus mr-1"></i>
            <span x-show="!openReviewForm">Add Review</span>
            <span x-show="openReviewForm">Cancel</span>
        </button>
    <?php endif; ?>
</div>

<!-- Add Review Form -->
<div x-show="openReviewForm" x-collapse x-cloak class="mb-6 border-l-4 border-blue-300 pl-4 py-2">
    <form action="<?= BASE_PATH ?>/tickets/addReview/<?= $data['ticket']['id'] ?>" method="POST">
        <div class="mb-4">
            <label for="review_result" class="block text-sm font-medium text-gray-700 mb-1">Review Outcome</label>
            <select id="review_result" name="review_result" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="compliant">Compliant</option>
                <option value="non_compliant">Non-Compliant</option>
                <option value="needs_improvement">Needs Improvement</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-1">Review Notes</label>
            <textarea id="review_notes" name="review_notes" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Add detailed notes about the review..."></textarea>
        </div>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
            Save Review
        </button>
    </form>
</div>

<!-- Existing Reviews -->
<div class="space-y-4">
    <?php if (!empty($data['reviews'])): ?>
        <?php foreach ($data['reviews'] as $review): ?>
            <div class="p-4 rounded-lg bg-gray-50 border">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($review['reviewer_username']) ?></p>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php 
                                $result_text = '';
                                $result_color = '';
                                switch ($review['review_result']) {
                                    case 'compliant':
                                        $result_text = 'Compliant';
                                        $result_color = 'text-green-600';
                                        break;
                                    case 'non_compliant':
                                        $result_text = 'Non-Compliant';
                                        $result_color = 'text-red-600';
                                        break;
                                    case 'needs_improvement':
                                        $result_text = 'Needs Improvement';
                                        $result_color = 'text-yellow-600';
                                        break;
                                }
                            ?>
                            <span class="font-bold <?= $result_color ?>"><?= $result_text ?></span>
                        </p>
                    </div>
                    <span class="text-xs text-gray-400" dir="ltr"><?= date('Y-m-d H:i', strtotime($review['reviewed_at'])) ?></span>
                </div>
                <p class="mt-3 text-sm text-gray-700 bg-white p-3 rounded-md border"><?= nl2br(htmlspecialchars($review['review_notes'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-500 text-center py-4">No reviews for this ticket yet.</p>
    <?php endif; ?> 