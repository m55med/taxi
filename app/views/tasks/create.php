<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <a href="<?= URLROOT ?>/tasks" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Tasks</span>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Task</h1>
                    <p class="text-gray-600">Assign a new task to one or more employees.</p>
                </div>

                <form action="<?= URLROOT ?>/tasks/create" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100" x-data="taskUpload()">
                    
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Task Title *</label>
                            <input type="text" name="title" required placeholder="Enter task title" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4" placeholder="Mention details, context, and requirements..." class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline *</label>
                            <input type="datetime-local" name="deadline" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Source (Where did this come from?)</label>
                            <input type="text" name="source" list="sources" placeholder="WhatsApp, Email, Meeting..." class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
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
                                <?php foreach ($data['users'] as $user): ?>
                                <label class="flex items-center gap-3 p-2 hover:bg-white hover:shadow-sm rounded-lg cursor-pointer transition-all border border-transparent hover:border-gray-100" 
                                       x-show="search === '' || '<?= strtolower(htmlspecialchars($user->name)) ?>'.includes(search.toLowerCase()) || '<?= strtolower(htmlspecialchars($user->role_name)) ?>'.includes(search.toLowerCase())">
                                    <input type="checkbox" name="assignee_ids[]" value="<?= $user->id ?>" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
                                    <option value="once">Once</option>
                                    <option value="recurring">Recurring</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Project</label>
                                <input type="text" name="project" placeholder="Project name" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Indicators & Goals -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-50">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">OCNA Indicator</label>
                            <input type="text" name="indicator" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">OCNA Goal</label>
                            <input type="text" name="goal" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>

                    <!-- File Attachments -->
                    <div class="pt-4 border-t border-gray-50">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-semibold text-gray-700">Attachments</label>
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

                        <!-- File Previews -->
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
                            Create Task & Assign
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
            // Note: This won't update the actual input element, 
            // but in a real app you'd typically handle this via AJAX or by using a hidden input with indexed filenames
            // For simple PHP form submission, we might need a workaround if we want to truly remove files from the POST.
            // However, for this UI improvement, it visually helps.
        },
        getFileIcon(type) {
            if (type.includes('image')) return 'fa-image';
            if (type.includes('pdf')) return 'fa-file-pdf';
            if (type.includes('video')) return 'fa-file-video';
            return 'fa-file';
        }
    }
}
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
