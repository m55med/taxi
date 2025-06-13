<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-semibold text-gray-700 flex items-center">
        <i class="fas fa-comments text-gray-400 mr-3"></i>
        المناقشات
    </h2>
    <?php if (in_array($currentUser['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])): ?>
        <button @click="openDiscussionForm = !openDiscussionForm" class="bg-purple-500 text-white px-3 py-1 rounded-md hover:bg-purple-600 text-sm">
            <i class="fas" :class="openDiscussionForm ? 'fa-times' : 'fa-plus'"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Add Discussion Form -->
<div x-show="openDiscussionForm" x-collapse x-cloak class="mb-4 border-t pt-4">
    <form action="<?= BASE_PATH ?>/tickets/addDiscussion/<?= $ticket['id'] ?>" method="POST">
        <div class="mb-3">
            <label for="reason_sidebar" class="block text-xs font-medium text-gray-700 mb-1">السبب</label>
            <input type="text" id="reason_sidebar" name="reason" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" >
        </div>
        <div class="mb-3">
            <label for="notes_sidebar" class="block text-xs font-medium text-gray-700 mb-1">الملاحظات</label>
            <textarea id="notes_sidebar" name="notes" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></textarea>
        </div>
        <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
            بدء المناقشة
        </button>
    </form>
</div>

<!-- Existing Discussions List -->
<ul class="space-y-3">
    <?php if (!empty($discussions)): ?>
        <?php foreach ($discussions as $index => $discussion): ?>
            <li x-data="{ open: false }" class="p-2 bg-gray-50 rounded-md border text-sm <?= $discussion['status'] === 'closed' ? 'opacity-60' : '' ?>">
                <div @click="open = !open" class="cursor-pointer flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-gray-800 flex items-center">
                            <?php if($discussion['status'] === 'closed'): ?>
                                <i class="fas fa-check-circle text-red-500 ml-2"></i>
                            <?php else: ?>
                                 <i class="fas fa-comment-dots text-purple-500 ml-2"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($discussion['reason']) ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            بواسطة: <?= htmlspecialchars($discussion['opener_username']) ?>
                        </p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                </div>
                <!-- Collapsible Content -->
                <div x-show="open" x-collapse class="mt-2 pt-2 border-t border-gray-200">
                    <!-- Discussion Body -->
                    <div class="text-xs text-gray-700 bg-white p-2 rounded-md mb-3">
                        <?= nl2br(htmlspecialchars($discussion['notes'])) ?>
                         <p class="text-gray-400 text-left mt-1" dir="ltr"><?= date('Y-m-d H:i', strtotime($discussion['created_at'])) ?></p>
                    </div>

                    <!-- Replies -->
                    <div class="space-y-2">
                         <?php if (!empty($discussion['objections'])): ?>
                            <?php foreach ($discussion['objections'] as $objection): ?>
                                <div class="bg-blue-50 p-2 rounded-lg border border-blue-100 text-xs">
                                    <p class="font-semibold text-blue-800"><?= htmlspecialchars($objection['replier_username']) ?></p>
                                    <p class="text-gray-700 mt-1"><?= nl2br(htmlspecialchars($objection['objection_text'])) ?></p>
                                    <p class="text-gray-400 text-left mt-1" dir="ltr"><?= date('Y-m-d H:i', strtotime($objection['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reply/Close Actions -->
                    <?php if($discussion['status'] === 'open'): ?>
                        <div class="mt-3 pt-3 border-t">
                            <!-- Add Reply Form (Simplified) -->
                             <form action="<?= BASE_PATH ?>/tickets/addObjection/<?= $ticket['id'] ?>/<?= $discussion['id'] ?>" method="POST" class="mb-2">
                                <input type="hidden" name="replied_to_user_id" value="<?= $discussion['opened_by'] ?>">
                                <textarea name="objection_text" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm text-xs" placeholder="اكتب ردك هنا..."></textarea>
                                <button type="submit" class="mt-2 w-full text-xs items-center px-3 py-1 border border-transparent rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    إرسال الرد
                                </button>
                            </form>
                             <!-- Close Discussion Button -->
                            <?php if (in_array($currentUser['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])): ?>
                                <form action="<?= BASE_PATH ?>/tickets/closeDiscussion/<?= $ticket['id'] ?>/<?= $discussion['id'] ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في إغلاق هذه المناقشة؟');">
                                    <button type="submit" class="w-full text-xs items-center px-3 py-1 border border-transparent rounded shadow-sm text-white bg-red-600 hover:bg-red-700">
                                        إغلاق المناقشة
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li class="text-gray-500 text-xs text-center py-2">لا توجد مناقشات لهذه التذكرة.</li>
    <?php endif; ?>
</ul> 