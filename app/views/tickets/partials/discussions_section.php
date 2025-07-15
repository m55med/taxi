<?php
// Create a unique identifier for this specific discussion section instance
// to avoid DOM ID conflicts when this partial is rendered multiple times on the same page.
$unique_id_suffix = 'new';
if (isset($add_url)) {
    // Attempt to create a suffix from the URL, e.g., 'review_123'
    $path_parts = explode('/', rtrim($add_url, '/'));
    $id_part = end($path_parts);
    $type_part = prev($path_parts);
    if (is_numeric($id_part) && is_string($type_part)) {
        $unique_id_suffix = htmlspecialchars($type_part) . '_' . htmlspecialchars($id_part);
    }
} else {
    // Fallback for a generic new discussion
    $unique_id_suffix = 'ticket_discussion_' . rand();
}
?>
<!-- Discussions Section -->
<div x-data="{ openDiscussionForm: false }" class="mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-700">
            <i class="fas fa-comments text-gray-400 mr-3"></i>Discussions
        </h3>
        <?php if (isset($can_add_discussion) && $can_add_discussion && isset($add_url)) : ?>
            <button @click="openDiscussionForm = !openDiscussionForm" class="bg-purple-500 text-white px-3 py-2 rounded-full hover:bg-purple-600 flex items-center justify-center w-8 h-8" :aria-expanded="openDiscussionForm" aria-controls="discussion-form-<?= $unique_id_suffix ?>">
                <i class="fas fa-plus"></i>
            </button>
        <?php endif; ?>
    </div>

    <!-- Discussion Form -->
    <div x-show="openDiscussionForm" x-transition class="bg-gray-50 p-4 rounded-lg border mb-4 discussion-form-container" id="discussion-form-<?= $unique_id_suffix ?>">
        <form id="discussionForm_<?= $unique_id_suffix ?>" action="<?= isset($add_url) ? $add_url : '' ?>" method="POST">
            <div class="mb-3">
                <label for="reason_<?= $unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Reason for Opening</label>
                <input type="text" name="reason" id="reason_<?= $unique_id_suffix ?>" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required placeholder="e.g., Complaint about call quality">
            </div>
            <div class="mb-3">
                <label for="notes_<?= $unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                <textarea name="notes" id="notes_<?= $unique_id_suffix ?>" rows="4" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required placeholder="Provide details here..."></textarea>
            </div>
            <div class="flex justify-end">
                <button type="button" @click="openDiscussionForm = false" class="text-gray-600 mr-4">Cancel</button>
                <button type="submit" class="bg-green-500 text-white font-bold py-2 px-4 rounded-md hover:bg-green-600">Open Discussion</button>
            </div>
        </form>
    </div>

    <!-- Existing Discussions -->
    <?php if (!empty($discussions)) : ?>
        <ul class="space-y-4">
            <?php foreach ($discussions as $discussion) : ?>
                <li class="p-4 rounded-lg border <?= $discussion['status'] === 'open' ? 'bg-orange-50 border-orange-200' : 'bg-gray-50 border-gray-200' ?>">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-bold"><?= htmlspecialchars($discussion['reason']) ?></span>
                            <span class="text-xs text-gray-500 ml-2">by <?= htmlspecialchars($discussion['opener_name']) ?></span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs px-2 py-1 rounded-full <?= $discussion['status'] === 'open' ? 'bg-orange-200 text-orange-800' : 'bg-gray-200 text-gray-800' ?>"><?= ucfirst($discussion['status']) ?></span>
                            <a href="/discussions#discussion-<?= $discussion['id'] ?>" title="View on discussions page" class="text-gray-400 hover:text-blue-500 transition-colors duration-150">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="prose prose-sm max-w-none mt-2 text-gray-700 p-2 bg-white rounded border border-gray-200"><?= $discussion['notes'] ?></div>
                    <div class="text-xs text-gray-500 mt-2 text-right">
                        <span>Opened at: <?= date('Y-m-d H:i', strtotime($discussion['created_at'])) ?></span>
                        <?php if ($discussion['status'] === 'closed') : ?>
                            <span class="ml-2">| Closed at: <?= date('Y-m-d H:i', strtotime($discussion['updated_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
            No discussions found.
        </div>
    <?php endif; ?>
</div> 