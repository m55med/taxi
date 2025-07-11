<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
    <div class="max-w-4xl w-full mx-auto">
        
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800"><?= $data['page_main_title'] ?? 'Driver Bulk Upload' ?></h1>
            <p class="text-gray-500 mt-2">Efficiently upload and process driver data from a file.</p>
        </div>

        <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="<?= URLROOT ?>/upload/process" method="post" enctype="multipart/form-data" class="space-y-12">

                <!-- Step 1: File Upload -->
                <div>
                    <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b-2 border-gray-200 pb-3 mb-6">
                        <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">1</span>
                        <span>Upload Your File</span>
                    </h2>
                    <div id="file-drop-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors">
                        <input type="file" name="file" id="file" class="hidden" required>
                        <div id="file-info" class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2"><span class="font-semibold text-blue-600">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-500 mt-1">XLSX, XLS, or CSV</p>
                            <p class="text-xs text-gray-500 mt-1">Required columns: `fullname`, `phone`, `email`</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Set Common Attributes -->
                <div>
                    <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b-2 border-gray-200 pb-3 mb-6">
                        <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">2</span>
                        <span>Configure Driver Settings</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="country_id" class="form-label">Country</label>
                            <div class="relative">
                                <select name="country_id" id="country_id" class="form-select appearance-none" required>
                                    <option value="">Select Country</option>
                                    <?php foreach ($data['countries'] as $country): ?>
                                        <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="app_status" class="form-label">Application Status</label>
                            <div class="relative">
                                <select name="app_status" id="app_status" class="form-select appearance-none" required>
                                    <option value="inactive" selected>Inactive</option>
                                    <option value="active">Active</option>
                                    <option value="banned">Banned</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="data_source" class="form-label">Data Source</label>
                             <div class="relative">
                                <select name="data_source" id="data_source" class="form-select appearance-none">
                                    <option value="excel" selected>From Excel File</option>
                                    <option value="form">From Manual Form</option>
                                    <option value="referral">From Referral</option>
                                    <option value="telegram">From Telegram</option>
                                    <option value="staff">Added by Staff</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="notes" class="form-label">Common Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="form-textarea" placeholder="Add a note that will apply to all drivers in this upload..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Required Documents -->
                <div>
                     <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b-2 border-gray-200 pb-3 mb-6">
                        <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">3</span>
                        <span>Assign Required Documents</span>
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                         <?php foreach ($data['document_types'] as $doc_type): ?>
                            <div class="relative">
                                <input type="checkbox" name="required_doc_ids[]" value="<?= $doc_type['id'] ?>" id="doc_<?= $doc_type['id'] ?>" class="hidden peer">
                                <label for="doc_<?= $doc_type['id'] ?>" class="flex items-center justify-center text-center p-4 border rounded-lg cursor-pointer transition-all duration-300 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg peer-checked:scale-105 hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-800"><?= htmlspecialchars($doc_type['name']) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submission -->
                <div class="flex justify-end pt-6 border-t">
                    <button type="submit" class="w-full md:w-auto px-8 py-3 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform transform hover:scale-105">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>
                        Upload and Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropArea = document.getElementById('file-drop-area');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('file-info');

    if (dropArea) {
        dropArea.addEventListener('click', () => fileInput.click());

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('border-blue-500', 'bg-blue-50');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                updateFileInfo();
            }
        });

        fileInput.addEventListener('change', updateFileInfo);

        function updateFileInfo() {
            if (fileInput.files.length > 0) {
                fileInfo.innerHTML = `<p class="font-semibold text-green-600">File selected: ${fileInput.files[0].name}</p><p class="text-xs text-gray-500">${(fileInput.files[0].size / 1024).toFixed(2)} KB</p>`;
            } else {
                fileInfo.innerHTML = `<svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg><p class="mt-2"><span class="font-semibold text-blue-600">Click to upload</span> or drag and drop</p><p class="text-xs text-gray-500 mt-1">XLSX, XLS, or CSV</p>`;
            }
        }
    }
});
</script>

<style>
.form-label { @apply block text-sm font-medium text-gray-600 mb-2; }
.form-select, .form-textarea { @apply block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow; }
.form-checkbox { @apply h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer; }
</style>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 