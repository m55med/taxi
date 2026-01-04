<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="<?= URLROOT ?>/tasks/show/<?= $data['task']['id'] ?>" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Task Details</span>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Task</h1>
        <p class="text-gray-600">Modify task details and assignments.</p>
    </div>

    <form action="<?= URLROOT ?>/tasks/edit/<?= $data['task']['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100" x-data="taskUpload()">
        
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Task Title *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($data['task']['title']) ?>" placeholder="Enter task title" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>
            
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="4" placeholder="Mention details, context, and requirements..." class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"><?= htmlspecialchars($data['task']['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline *</label>
                <input type="datetime-local" name="deadline" required value="<?= date('Y-m-d\TH:i', strtotime($data['task']['deadline'])) ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Source</label>
                <input type="text" name="source" list="sources" value="<?= htmlspecialchars($data['task']['source']) ?>" placeholder="WhatsApp, Email, Meeting..." class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                <datalist id="sources">
                    <option value="WhatsApp">
                    <option value="Email">
                    <option value="Telegram">
                    <option value="Meeting">
                    <option value="Office Visit">
                    <option value="Phone Call">
                </datalist>
            </div>
        </div>

        <!-- Assignment & Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-50">
            <div x-data="{ search: '' }">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Assign To * (Search & Select)</label>
                <div class="mb-2">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="search" placeholder="Search by name or role..." class="w-full pl-9 pr-4 py-1.5 text-sm rounded-lg border border-gray-200 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-xl p-3 space-y-2 bg-gray-50/50">
                    <?php 
                    $currentAssigneeIds = array_column($data['task']['assignees'], 'id');
                    foreach ($data['users'] as $user): 
                    ?>
                    <label class="flex items-center gap-3 p-2 hover:bg-white hover:shadow-sm rounded-lg cursor-pointer transition-all border border-transparent hover:border-gray-100" 
                           x-show="search === '' || '<?= strtolower(htmlspecialchars($user->name)) ?>'.includes(search.toLowerCase()) || '<?= strtolower(htmlspecialchars($user->role_name)) ?>'.includes(search.toLowerCase())">
                        <input type="checkbox" name="assignee_ids[]" value="<?= $user->id ?>" <?= in_array($user->id, $currentAssigneeIds) ? 'checked' : '' ?> class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user->name) ?></span>
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-tight"><?= htmlspecialchars($user->role_name) ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Frequency</label>
                    <select name="frequency" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                        <option value="once" <?= $data['task']['frequency'] === 'once' ? 'selected' : '' ?>>Once</option>
                        <option value="recurring" <?= $data['task']['frequency'] === 'recurring' ? 'selected' : '' ?>>Recurring</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project</label>
                    <input type="text" name="project" value="<?= htmlspecialchars($data['task']['project'] ?? '') ?>" placeholder="Project name" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                </div>
            </div>
        </div>

        <!-- Indicators & Goals -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-50">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">OCNA Indicator</label>
                <input type="text" name="indicator" value="<?= htmlspecialchars($data['task']['indicator'] ?? '') ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">OCNA Goal</label>
                <input type="text" name="goal" value="<?= htmlspecialchars($data['task']['goal'] ?? '') ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>
        </div>

        <!-- File Attachments -->
        <div class="pt-4 border-t border-gray-50">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-semibold text-gray-700">Add New Attachments</label>
                <span class="text-[10px] font-bold text-gray-400 uppercase">Supported: JPG, PNG, PDF, MP4</span>
            </div>
            
            <div class="relative group">
                <input type="file" name="attachments[]" multiple @change="handleFiles" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <div class="border-2 border-dashed border-gray-200 group-hover:border-blue-400 rounded-xl p-8 flex flex-col items-center justify-center transition-all bg-gray-50 group-hover:bg-blue-50">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 group-hover:text-blue-500 mb-2 transition-all"></i>
                    <p class="text-sm text-gray-600 group-hover:text-blue-600">Click or drag files to upload</p>
                    <p class="text-xs text-gray-400 mt-1">Multi-upload supported</p>
                </div>
            </div>

            <!-- Existing Files -->
            <?php if (!empty($data['task']['attachments'])): ?>
            <div class="mt-6">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Existing Attachments</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php foreach ($data['task']['attachments'] as $file): ?>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/50 group hover:bg-red-50 hover:border-red-200 transition-all" id="attachment-<?= $file['id'] ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <i class="fas fa-file text-gray-400"></i>
                            <span class="text-xs font-medium text-gray-700 truncate"><?= htmlspecialchars($file['file_name']) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-400 uppercase"><?= explode('/', $file['file_type'])[1] ?? 'FILE' ?></span>
                            <button type="button" onclick="deleteAttachment(<?= $file['id'] ?>, '<?= htmlspecialchars($file['file_name']) ?>')"
                                    class="opacity-0 group-hover:opacity-100 w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-all"
                                    title="Delete attachment">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- New File Previews -->
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4" x-show="files.length > 0">
                <template x-for="(file, index) in files" :key="index">
                    <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 bg-white p-2">
                        <div class="w-full h-full flex flex-col items-center justify-center gap-1">
                            <i class="fas text-2xl text-gray-400" :class="getFileIcon(file.type)"></i>
                            <span class="text-[10px] font-medium text-gray-500 truncate w-full text-center" x-text="file.name"></span>
                        </div>
                        <button type="button" @click="removeFile(index)" class="absolute top-1 right-1 w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-colors z-20">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
function taskUpload() {
    return {
        files: [],
        handleFiles(e) {
            const newFiles = Array.from(e.target.files);
            this.files = [...this.files, ...newFiles];
        },
        removeFile(index) {
            this.files.splice(index, 1);
        },
        getFileIcon(type) {
            if (type.includes('image')) return 'fa-image';
            if (type.includes('pdf')) return 'fa-file-pdf';
            if (type.includes('video')) return 'fa-file-video';
            return 'fa-file';
        }
    }
}

function deleteAttachment(attachmentId, filename) {
    if (!confirm(`Are you sure you want to delete "${filename}"? This action cannot be undone.`)) {
        return;
    }

    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
    button.disabled = true;

    fetch('<?= URLROOT ?>/tasks/delete_attachment/' + attachmentId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            _token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the attachment from DOM
            document.getElementById('attachment-' + attachmentId).remove();
            // Show success message
            showNotification('Attachment deleted successfully', 'success');
        } else {
            showNotification(data.message || 'Failed to delete attachment', 'error');
            button.innerHTML = originalHtml;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the attachment', 'error');
        button.innerHTML = originalHtml;
        button.disabled = false;
    });
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
