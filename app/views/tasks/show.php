<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="max-w-5xl mx-auto" x-data="taskViewer()" @keydown.left.window="prevFile()" @keydown.right.window="nextFile()">
                <div class="mb-6 flex justify-between items-start">
                    <div>
                        <a href="<?= URLROOT ?>/tasks" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Tasks</span>
                        </a>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($data['task']['title']) ?></h1>
                            <?php if ($data['role'] === 'admin' || $data['role'] === 'developer' || $data['task']['created_by'] == $data['current_user_id']): ?>
                            <a href="<?= URLROOT ?>/tasks/edit/<?= $data['task']['id'] ?>" class="text-gray-400 hover:text-blue-600 transition-colors mr-2" title="Edit Task">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($data['role'] === 'admin' || $data['role'] === 'developer'): ?>
                            <button onclick="showDeleteModal()" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete Task">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs text-gray-500">Created by <span class="font-medium text-gray-700"><?= htmlspecialchars($data['task']['creator_name']) ?></span></span>
                            <span class="text-xs text-gray-500">•</span>
                            <span class="text-xs text-gray-500"><?= date('M d, Y H:i', strtotime($data['task']['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end gap-3">
                        <?php
                        $statusClasses = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'in_progress' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        $statusClass = $statusClasses[$data['task']['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-4 py-1 rounded-full text-sm font-bold shadow-sm <?= $statusClass ?>">
                            <?= ucwords(str_replace('_', ' ', $data['task']['status'])) ?>
                        </span>
                        
                        <!-- Update Status Form with Confirmation -->
                        <?php if ($data['task']['status'] === 'completed' && !in_array($data['role'], ['admin', 'developer'])): ?>
                        <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                            <i class="fas fa-lock"></i>
                            <span>You don't have permission to reopen completed tasks</span>
                        </div>
                        <?php else: ?>
                        <form id="statusForm" action="<?= URLROOT ?>/tasks/update_status" method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="task_id" value="<?= $data['task']['id'] ?>">
                            <select name="status" x-model="taskStatus" class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <button type="button" @click="taskStatus === 'completed' ? showConfirmModal = true : $el.form.submit()" class="text-xs bg-gray-900 text-white px-3 py-1 rounded-lg hover:bg-gray-800 transition-colors">Update</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2">Description</h2>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($data['task']['description']) ?></p>
                        </section>

                        <!-- Attachments with Previews -->
                        <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2">Attachments</h2>
                            <?php if (empty($data['task']['attachments'])): ?>
                                <p class="text-gray-400 text-sm italic">No files attached to this task.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <?php foreach ($data['task']['attachments'] as $index => $file): ?>
                                        <div class="cursor-pointer flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-all group relative">
                                            <div @click="openPreview(<?= $index ?>)" class="flex items-center gap-3 min-w-0 flex-1">
                                                <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-blue-500 group-hover:bg-white transition-all">
                                                    <?php if (strpos($file['file_type'], 'image') !== false): ?>
                                                        <i class="fas fa-image"></i>
                                                    <?php elseif (strpos($file['file_type'], 'pdf') !== false): ?>
                                                        <i class="fas fa-file-pdf"></i>
                                                    <?php elseif (strpos($file['file_type'], 'video') !== false): ?>
                                                        <i class="fas fa-file-video"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-file"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($file['file_name']) ?></span>
                                                    <span class="text-[10px] text-gray-400 uppercase"><?= explode('/', $file['file_type'])[1] ?? 'FILE' ?></span>
                                                </div>
                                            </div>
                                            <?php if ($data['role'] === 'admin' || $data['role'] === 'developer' || $data['task']['created_by'] == $data['current_user_id']): ?>
                                            <button @click="deleteAttachment(<?= $file['id'] ?>, '<?= htmlspecialchars($file['file_name']) ?>')"
                                                    class="opacity-0 group-hover:opacity-100 w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-all ml-2"
                                                    title="Delete attachment">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>

                        <!-- Progress & Comments -->
                        <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2">Progress & Updates</h2>
                            
                            <!-- Comment Loop -->
                            <div class="space-y-6 mb-8 mt-4">
                                <?php if (empty($data['task']['comments'])): ?>
                                    <p class="text-gray-400 text-sm italic text-center py-4">No updates yet.</p>
                                <?php else: ?>
                                    <?php foreach ($data['task']['comments'] as $comment): ?>
                                        <div class="flex gap-4 <?= $comment['is_completion_notice'] ? 'bg-green-50/50 p-4 rounded-xl border border-green-100' : '' ?>">
                                            <div class="w-10 h-10 rounded-full flex-shrink-0 bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-sm uppercase">
                                                <?= substr($comment['username'], 0, 1) ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm font-bold text-gray-900"><?= htmlspecialchars($comment['name']) ?></span>
                                                    <span class="text-[10px] text-gray-400 font-medium"><?= date('M d, H:i', strtotime($comment['created_at'])) ?></span>
                                                    <?php if ($comment['is_completion_notice']): ?>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-bold uppercase">Marked as Done</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Comment Input -->
                            <form action="<?= URLROOT ?>/tasks/add_comment" method="POST" class="mt-6 pt-6 border-t border-gray-50">
                                <input type="hidden" name="task_id" value="<?= $data['task']['id'] ?>">
                                <div class="space-y-3">
                                    <textarea name="comment" required rows="2" placeholder="Write an update, progress report, or comment..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all text-sm"></textarea>
                                    <div class="flex justify-between items-center">
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" name="is_completion_notice" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="text-xs font-medium text-gray-500 group-hover:text-green-600 transition-colors">I've finished my part of this task</span>
                                        </label>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all transform hover:-translate-y-0.5">
                                            Post Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="space-y-6">
                        <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2">Task Details</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Deadline</label>
                                    <p class="text-sm font-semibold text-gray-900 flex items-center gap-2 mt-1">
                                        <i class="far fa-calendar-alt text-blue-500"></i>
                                        <?= date('M d, Y', strtotime($data['task']['deadline'])) ?>
                                        <span class="text-gray-400">•</span>
                                        <?= date('H:i', strtotime($data['task']['deadline'])) ?>
                                    </p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Source</label>
                                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= htmlspecialchars($data['task']['source']) ?></p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Project</label>
                                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= htmlspecialchars($data['task']['project'] ?? 'General') ?></p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Frequency</label>
                                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= ucfirst($data['task']['frequency']) ?></p>
                                </div>
                                <?php if (!empty($data['task']['indicator'])): ?>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Indicator</label>
                                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= htmlspecialchars($data['task']['indicator']) ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($data['task']['goal'])): ?>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Goal</label>
                                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= htmlspecialchars($data['task']['goal']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2">Assignees</h2>
                            <div class="space-y-3">
                                <?php foreach ($data['task']['assignees'] as $assignee): ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs uppercase">
                                        <?= substr($assignee['username'], 0, 1) ?>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($assignee['name']) ?></span>
                                        <span class="text-[10px] text-gray-400">@<?= htmlspecialchars($assignee['username']) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </div>

    <!-- Previews Modal -->
    <div x-show="previewFile" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80" x-cloak @keydown.escape.window="previewFile = null" @keydown.left.window="prevFile()" @keydown.right.window="nextFile()">
        <div class="relative w-full max-w-4xl max-h-[90vh] bg-white rounded-2xl overflow-hidden shadow-2xl" @click.away="previewFile = null">
            <!-- Navigation Arrows (only show if more than 1 file) -->
            <template x-if="availableFiles.length > 1">
                <button @click="prevFile()" class="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button @click="nextFile()" class="absolute right-4 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </template>

            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-bold text-gray-900 truncate pr-8" x-text="previewFile ? previewFile.name : ''"></h3>
                <div class="flex items-center gap-2">
                    <!-- File counter -->
                    <template x-if="availableFiles.length > 1">
                        <span class="text-sm text-gray-500" x-text="(currentFileIndex + 1) + ' / ' + availableFiles.length"></span>
                    </template>
                    <button @click="previewFile = null" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-4 bg-gray-50 flex items-center justify-center overflow-auto" style="height: calc(90vh - 65px);">
                <template x-if="previewFile && previewFile.type.includes('image')">
                    <img :src="previewFile.url" class="max-w-full max-h-full object-contain">
                </template>
                <template x-if="previewFile && previewFile.type.includes('video')">
                    <video :src="previewFile.url" controls class="max-w-full max-h-full"></video>
                </template>
                <template x-if="previewFile && previewFile.type.includes('pdf')">
                    <iframe :src="previewFile.url" class="w-full h-full border-none"></iframe>
                </template>
                <template x-if="previewFile && !previewFile.type.includes('image') && !previewFile.type.includes('video') && !previewFile.type.includes('pdf')">
                    <div class="text-center py-12">
                        <i class="fas fa-file text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 mb-4">Preview not available for this file type.</p>
                        <a :href="previewFile.url" target="_blank" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">Download File</a>
                    </div>
                </template>
            </div>

            <!-- Bottom Navigation (only show if more than 1 file) -->
            <template x-if="availableFiles.length > 1">
                <div class="flex items-center justify-between p-4 bg-gray-50 border-t">
                    <button @click="prevFile()" class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left"></i>
                        <span>Previous</span>
                    </button>

                    <!-- Thumbnail navigation -->
                    <div class="flex gap-2">
                        <template x-for="(file, index) in availableFiles" :key="index">
                            <button @click="openPreview(index)" :class="index === currentFileIndex ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                                <span x-text="index + 1"></span>
                            </button>
                        </template>
                    </div>

                    <button @click="nextFile()" class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                        <span>Next</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak @keydown.escape.window="showConfirmModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.away="showConfirmModal = false">
            <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mb-4 text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Complete Task?</h3>
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">Are you sure you want to mark this task as fully completed? Make sure all assignees have finished their work.</p>
            <div class="flex gap-3">
                <button @click="showConfirmModal = false" class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors font-bold text-sm">Cancel</button>
                <button @click="document.getElementById('statusForm').submit()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-bold text-sm">Yes, Complete it</button>
            </div>
        </div>
    </div>

    <!-- Delete Task Modal -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak @keydown.escape.window="showDeleteModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.away="showDeleteModal = false">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Task?</h3>
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">Are you sure you want to delete this task? This action cannot be undone and will remove all attachments and comments.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors font-bold text-sm">Cancel</button>
                <form action="<?= URLROOT ?>/tasks/delete/<?= $data['task']['id'] ?>" method="POST" class="flex-1">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-bold text-sm">Delete Task</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Assignees Modal -->
    <?php if (in_array($data['role'], ['Team_leader', 'Quality'])): ?>
    <div x-show="showAssigneeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak @keydown.escape.window="showAssigneeModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-2xl w-full shadow-2xl max-h-[90vh] overflow-y-auto" @click.away="showAssigneeModal = false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Edit Task Assignees</h3>
                <button @click="showAssigneeModal = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="<?= URLROOT ?>/tasks/update_assignees/<?= $data['task']['id'] ?>" method="POST" class="space-y-6">
                <div x-data="{ search: '' }">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Assign To * (Search & Select)</label>

                    <!-- Search Input -->
                    <div class="mb-3">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" x-model="search" placeholder="Search by name or role..." class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-gray-200 focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Assignees List -->
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-xl p-3 space-y-2 bg-gray-50/50">
                        <?php
                        $currentAssignees = array_column($data['task']['assignees'], 'id');
                        foreach ($data['users'] as $user):
                        ?>
                        <label class="flex items-center gap-3 p-2 hover:bg-white hover:shadow-sm rounded-lg cursor-pointer transition-all border border-transparent hover:border-gray-100"
                               x-show="search === '' || '<?= strtolower(htmlspecialchars($user->name)) ?>'.includes(search.toLowerCase()) || '<?= strtolower(htmlspecialchars($user->role_name)) ?>'.includes(search.toLowerCase())">
                            <input type="checkbox" name="assignee_ids[]" value="<?= $user->id ?>"
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   <?= in_array($user->id, $currentAssignees) ? 'checked' : '' ?>>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user->name) ?></span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-tight"><?= htmlspecialchars($user->role_name) ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="button" @click="showAssigneeModal = false" class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors font-bold text-sm">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-bold text-sm">Update Assignees</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function taskViewer() {
    return {
        previewFile: null,
        showConfirmModal: false,
        showDeleteModal: false,
        taskStatus: '<?= $data['task']['status'] ?>',
        showAssigneeModal: <?= isset($_GET['action']) && $_GET['action'] === 'edit_assignees' ? 'true' : 'false' ?>,
        searchAssignee: '',
        availableFiles: [
            <?php foreach ($data['task']['attachments'] as $index => $file): ?>
            <?php $filename = basename($file['file_path']); ?>
            {
                id: <?= $file['id'] ?>,
                url: '<?= URLROOT ?>/serve/<?= $filename ?>',
                name: '<?= htmlspecialchars(addslashes($file['file_name'])) ?>',
                type: '<?= $file['file_type'] ?>',
                index: <?= $index ?>
            }<?= $index < count($data['task']['attachments']) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ],
        currentFileIndex: 0,
        openPreview(fileIndex) {
            this.currentFileIndex = fileIndex;
            const file = this.availableFiles[fileIndex];
            this.previewFile = {
                url: file.url,
                name: file.name,
                type: file.type,
                index: file.index
            };
        },
        nextFile() {
            if (this.availableFiles.length > 1) {
                this.currentFileIndex = (this.currentFileIndex + 1) % this.availableFiles.length;
                this.updatePreviewFile();
            }
        },
        prevFile() {
            if (this.availableFiles.length > 1) {
                this.currentFileIndex = (this.currentFileIndex - 1 + this.availableFiles.length) % this.availableFiles.length;
                this.updatePreviewFile();
            }
        },
        updatePreviewFile() {
            const file = this.availableFiles[this.currentFileIndex];
            this.previewFile = {
                url: file.url,
                name: file.name,
                type: file.type,
                index: file.index
            };
        },
        deleteAttachment(attachmentId, filename) {
            if (!confirm(`Are you sure you want to delete "${filename}"? This action cannot be undone.`)) {
                return;
            }

            fetch('<?= URLROOT ?>/tasks/delete_attachment/' + attachmentId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove from availableFiles array
                    this.availableFiles = this.availableFiles.filter(file => file.id !== attachmentId);
                    // Close preview if it was the deleted file
                    if (this.previewFile && this.availableFiles.length === 0) {
                        this.previewFile = null;
                    }
                    showNotification('Attachment deleted successfully', 'success');
                } else {
                    showNotification(data.message || 'Failed to delete attachment', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while deleting the attachment', 'error');
            });
        }
    }
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
