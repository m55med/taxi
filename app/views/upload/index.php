<?php include_once APPROOT . '/views/includes/header.php'; ?>

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<!-- Choices.js SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

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
                    <h2
                        class="flex items-center text-xl font-semibold text-gray-700 border-b-2 border-gray-200 pb-3 mb-6">
                        <span
                            class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">1</span>
                        <span>Upload Your File</span>
                    </h2>
                    <div id="file-drop-area"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors">
                        <input type="file" name="file" id="file" class="hidden" required>
                        <div id="file-info" class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48" aria-hidden="true">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2"><span class="font-semibold text-blue-600">Click to upload</span> or drag and
                                drop</p>
                            <p class="text-xs text-gray-500 mt-1">XLSX, XLS, or CSV</p>
                            <p class="text-xs text-gray-500 mt-1">Required columns: `fullname`, `phone`, `email`</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Set Common Attributes -->
                <div>
                    <h2
                        class="flex items-center text-xl font-semibold text-gray-700 border-b-2 border-gray-200 pb-3 mb-6">
                        <span
                            class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">2</span>
                        <span>Configure Driver Settings</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Standard Select for Country -->
                        <div class="mb-4">
                            <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>
                            <select id="country_id" name="country_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                required>
                                <?php foreach ($data['countries'] as $country): ?>
                                    <option value="<?= $country->id ?>"><?= htmlspecialchars($country->name ?? '') ?>
                                    </option>

                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="car_type_id" class="block text-sm font-medium text-gray-700">Default Car
                                Type</label>
                            <select id="car_type_id" name="car_type_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                required>
                                <?php foreach ($data['car_types'] as $carType): ?>
                                    <option value="<?= $carType->id ?>"><?= htmlspecialchars($carType->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="app_status" class="block text-sm font-medium text-gray-700">Application
                                Status</label>
                            <select id="app_status" name="app_status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                required>
                                <option value="inactive" selected>Inactive</option>
                                <option value="active">Active</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>

                        <!-- Standard Select for Data Source -->
                        <div class="md:col-span-2">
                            <label for="data_source" class="form-label">Data Source</label>
                            <select name="data_source" id="data_source" class="form-select">
                                <option value="excel" selected>From Excel File</option>
                                <option value="form">From Manual Form</option>
                                <option value="referral">From Referral</option>
                                <option value="telegram">From Telegram</option>
                                <option value="staff">Added by Staff</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Common Notes
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Optional: Add general notes that apply to all drivers
                                in this file.</p>
                            <div class="relative">
                                <textarea name="notes" id="notes" rows="4"
                                    class="w-full resize-y rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                    placeholder="e.g. Added from recruitment campaign #4 or contacted via WhatsApp..."></textarea>
                                <div class="absolute bottom-2 right-3 text-xs text-gray-400" id="note-char-count">0/300
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Step 3: Assign Required Documents -->
                <div>
                    <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b-2 border-gray-200 pb-3 mb-6">
                        <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">3</span>
                        <span>Assign Required Documents</span>
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">Select which documents will be marked as required for all uploaded drivers.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php foreach ($data['document_types'] as $docType): ?>
                            <div>
                                <input type="checkbox" name="required_doc_ids[]" id="doc_<?= $docType->id ?>" value="<?= $docType->id ?>" class="hidden peer" checked>
                                <label for="doc_<?= $docType->id ?>" class="block text-center text-sm p-3 rounded-lg border-2 border-gray-300 cursor-pointer transition-all duration-200 ease-in-out peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-800 peer-checked:shadow-sm">
                                    <?= htmlspecialchars($docType->name) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>


                <!-- Submission -->
                <div class="flex justify-end pt-6 border-t">
                    <button type="submit"
                        class="w-full md:w-auto px-8 py-3 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform transform hover:scale-105">
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

    document.addEventListener('DOMContentLoaded', function () {
        new Choices('#country_id', {
            removeItemButton: true,
            searchPlaceholderValue: "Search countries..."
        });
        new Choices('#car_type_id', {
            removeItemButton: true,
            searchPlaceholderValue: "Search car types..."
        });
        new Choices('#app_status', {
            removeItemButton: true,
            searchEnabled: false,
            itemSelectText: ''
        });
        new Choices('#data_source', {
            removeItemButton: true,
            searchEnabled: false,
            itemSelectText: ''
        });
        new Choices('#required_doc_ids', {
            removeItemButton: true,
            searchPlaceholderValue: "Search documents..."
        });
    });
</script>

<style>
    .form-label {
        @apply block text-sm font-medium text-gray-600 mb-2;
    }

    .form-textarea {
        @apply block w-full bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow px-4 py-3;
    }

    .form-checkbox {
        @apply h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer;
    }
</style>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>