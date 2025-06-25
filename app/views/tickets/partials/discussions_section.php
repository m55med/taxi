<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-semibold text-gray-700 flex items-center">
        <i class="fas fa-comments text-gray-400 mr-3"></i>
        Discussions
    </h2>
    <?php if (in_array($data['currentUser']['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])): ?>
        <button @click="openDiscussionForm = !openDiscussionForm" class="bg-purple-500 text-white px-3 py-1 rounded-md hover:bg-purple-600 text-sm">
            <i class="fas" :class="openDiscussionForm ? 'fa-times' : 'fa-plus'"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Add Discussion Form -->
<div x-show="openDiscussionForm" x-collapse x-cloak class="mb-4 border-t pt-4">
    <form id="discussionForm" action="<?= BASE_PATH ?>/tickets/addDiscussion/<?= $data['ticket']['id'] ?>" method="POST">
        <div class="mb-3">
            <label for="reason_sidebar" class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
            <input type="text" id="reason_sidebar" name="reason" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Enter a brief reason for discussion">
        </div>
        <div class="mb-3">
            <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
            <input name="notes" type="hidden">
            <div id="discussionEditor" style="height: 150px;"></div>
        </div>
        <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
            Start Discussion
        </button>
    </form>
</div>

<!-- Existing Discussions List -->
<ul class="space-y-3">
    <?php if (!empty($data['discussions'])): ?>
        <?php foreach ($data['discussions'] as $index => $discussion): ?>
            <li x-data="{ open: false }" class="p-2 bg-gray-50 rounded-md border text-sm <?= $discussion['status'] === 'closed' ? 'opacity-60' : '' ?>">
                <div @click="open = !open" class="cursor-pointer flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-gray-800 flex items-center">
                            <?php if($discussion['status'] === 'closed'): ?>
                                <i class="fas fa-check-circle text-red-500 mr-2"></i>
                            <?php else: ?>
                                 <i class="fas fa-comment-dots text-purple-500 mr-2"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($discussion['reason']) ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            By: <?= htmlspecialchars($discussion['opener_username']) ?>
                        </p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                </div>
                <!-- Collapsible Content -->
                <div x-show="open" x-collapse class="mt-2 pt-2 border-t border-gray-200">
                    <!-- Discussion Body -->
                    <div class="text-xs text-gray-700 bg-white p-2 rounded-md mb-3 prose max-w-none">
                        <?= $discussion['notes'] ?>
                         <p class="text-gray-400 text-right mt-1" dir="ltr"><?= date('Y-m-d H:i', strtotime($discussion['created_at'])) ?></p>
                    </div>

                    <!-- Replies -->
                    <div class="space-y-2">
                         <?php if (!empty($discussion['objections'])): ?>
                            <?php foreach ($discussion['objections'] as $objection): ?>
                                <div class="bg-blue-50 p-2 rounded-lg border border-blue-100 text-xs">
                                    <p class="font-semibold text-blue-800"><?= htmlspecialchars($objection['replier_username']) ?></p>
                                    <p class="text-gray-700 mt-1"><?= nl2br(htmlspecialchars($objection['objection_text'])) ?></p>
                                    <p class="text-gray-400 text-right mt-1" dir="ltr"><?= date('Y-m-d H:i', strtotime($objection['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reply/Close Actions -->
                    <?php if($discussion['status'] === 'open'): ?>
                        <div class="mt-3 pt-3 border-t">
                            <!-- Add Reply Form (Simplified) -->
                             <form action="<?= BASE_PATH ?>/tickets/addObjection/<?= $data['ticket']['id'] ?>/<?= $discussion['id'] ?>" method="POST" class="mb-2">
                                <input type="hidden" name="replied_to_user_id" value="<?= $discussion['opened_by'] ?>">
                                <textarea name="objection_text" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm text-xs" placeholder="Write your reply here..."></textarea>
                                <button type="submit" class="mt-2 w-full text-xs items-center px-3 py-1 border border-transparent rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    Send Reply
                                </button>
                            </form>
                             <!-- Close Discussion Button -->
                            <?php if (in_array($data['currentUser']['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])): ?>
                                <form action="<?= BASE_PATH ?>/tickets/closeDiscussion/<?= $data['ticket']['id'] ?>/<?= $discussion['id'] ?>" method="POST" onsubmit="return confirm('Are you sure you want to close this discussion?');">
                                    <button type="submit" class="w-full text-xs items-center px-3 py-1 border border-transparent rounded shadow-sm text-white bg-red-600 hover:bg-red-700">
                                        Close Discussion
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li class="text-gray-500 text-xs text-center py-2">No discussions for this ticket.</li>
    <?php endif; ?>
</ul> 