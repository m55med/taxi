<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Establishment</h1>
            <p class="text-gray-600">Update establishment information</p>
        </div>

        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?= URLROOT ?>/dashboard" class="hover:text-gray-700">Dashboard</a></li>
                <li><i class="fas fa-chevron-right"></i></li>
                <li><a href="<?= URLROOT ?>/referral/establishments" class="hover:text-gray-700">Establishments</a></li>
                <li><i class="fas fa-chevron-right"></i></li>
                <li class="text-gray-900">Edit</li>
            </ol>
        </nav>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form action="<?= URLROOT ?>/referral/establishments/edit/<?= $data['establishment']->id ?>" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Establishment Information -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Establishment Information</h3>
                    </div>

                    <div>
                        <label for="establishment_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Establishment Name
                        </label>
                        <input type="text" 
                               id="establishment_name" 
                               name="establishment_name" 
                               value="<?= htmlspecialchars($data['establishment']->establishment_name ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="legal_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Legal Name
                        </label>
                        <input type="text" 
                               id="legal_name" 
                               name="legal_name" 
                               value="<?= htmlspecialchars($data['establishment']->legal_name ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="taxpayer_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Taxpayer Number
                        </label>
                        <input type="text" 
                               id="taxpayer_number" 
                               name="taxpayer_number" 
                               value="<?= htmlspecialchars($data['establishment']->taxpayer_number ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="marketer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Marketer
                        </label>
                        <select id="marketer_id" 
                                name="marketer_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No marketer assigned</option>
                            <?php foreach ($data['marketers'] as $marketer): ?>
                                <option value="<?= $marketer->id ?>" 
                                        <?= $data['establishment']->marketer_id == $marketer->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($marketer->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Address Information -->
                    <div class="md:col-span-2 mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Address Information</h3>
                    </div>

                    <div>
                        <label for="street" class="block text-sm font-medium text-gray-700 mb-2">
                            Street
                        </label>
                        <input type="text" 
                               id="street" 
                               name="street" 
                               value="<?= htmlspecialchars($data['establishment']->street ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="house_number" class="block text-sm font-medium text-gray-700 mb-2">
                            House Number
                        </label>
                        <input type="text" 
                               id="house_number" 
                               name="house_number" 
                               value="<?= htmlspecialchars($data['establishment']->house_number ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="postal_zip" class="block text-sm font-medium text-gray-700 mb-2">
                            Postal/ZIP Code
                        </label>
                        <input type="text" 
                               id="postal_zip" 
                               name="postal_zip" 
                               value="<?= htmlspecialchars($data['establishment']->postal_zip ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Contact Information -->
                    <div class="md:col-span-2 mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Establishment Contact</h3>
                    </div>

                    <div>
                        <label for="establishment_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Establishment Email
                        </label>
                        <input type="email" 
                               id="establishment_email" 
                               name="establishment_email" 
                               value="<?= htmlspecialchars($data['establishment']->establishment_email ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="establishment_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Establishment Phone
                        </label>
                        <input type="text" 
                               id="establishment_phone" 
                               name="establishment_phone" 
                               value="<?= htmlspecialchars($data['establishment']->establishment_phone ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Owner Information -->
                    <div class="md:col-span-2 mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Owner Information</h3>
                    </div>

                    <div>
                        <label for="owner_full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Owner Full Name
                        </label>
                        <input type="text" 
                               id="owner_full_name" 
                               name="owner_full_name" 
                               value="<?= htmlspecialchars($data['establishment']->owner_full_name ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="owner_position" class="block text-sm font-medium text-gray-700 mb-2">
                            Owner Position
                        </label>
                        <input type="text" 
                               id="owner_position" 
                               name="owner_position" 
                               value="<?= htmlspecialchars($data['establishment']->owner_position ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Owner Email
                        </label>
                        <input type="email" 
                               id="owner_email" 
                               name="owner_email" 
                               value="<?= htmlspecialchars($data['establishment']->owner_email ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="owner_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Owner Phone
                        </label>
                        <input type="text" 
                               id="owner_phone" 
                               name="owner_phone" 
                               value="<?= htmlspecialchars($data['establishment']->owner_phone ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Additional Information -->
                    <div class="md:col-span-2 mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h3>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Enter establishment description..."><?= htmlspecialchars($data['establishment']->description ?? '') ?></textarea>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                        <label for="establishment_logo" class="block text-sm font-medium text-gray-700 mb-2">
                            Establishment Logo
                        </label>
                        
                        <!-- Current Logo Display -->
                        <?php if (!empty($data['establishment']->establishment_logo)): ?>
                            <div class="mb-3">
                                <div class="text-xs text-gray-500 mb-1">Current Logo:</div>
                                <img src="<?= URLROOT ?>/establishment/image/<?= htmlspecialchars($data['establishment']->establishment_logo) ?>" 
                                     alt="Current Logo" 
                                     class="w-20 h-20 object-cover border border-gray-300 rounded-lg">
                            </div>
                        <?php endif; ?>

                        <!-- File Upload Input -->
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="establishment_logo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload a logo</span>
                                        <input id="establishment_logo" name="establishment_logo" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'logo-preview')">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, WebP up to 5MB</p>
                            </div>
                        </div>

                        <!-- Logo Preview -->
                        <div id="logo-preview" class="mt-3 hidden">
                            <div class="text-xs text-gray-500 mb-1">New Logo Preview:</div>
                            <img class="w-20 h-20 object-cover border border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <!-- Header Image Upload -->
                    <div>
                        <label for="establishment_header_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Header Image
                        </label>
                        
                        <!-- Current Header Image Display -->
                        <?php if (!empty($data['establishment']->establishment_header_image)): ?>
                            <div class="mb-3">
                                <div class="text-xs text-gray-500 mb-1">Current Header:</div>
                                <img src="<?= URLROOT ?>/establishment/image/<?= htmlspecialchars($data['establishment']->establishment_header_image) ?>" 
                                     alt="Current Header" 
                                     class="w-full h-24 object-cover border border-gray-300 rounded-lg">
                            </div>
                        <?php endif; ?>

                        <!-- File Upload Input -->
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="establishment_header_image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload header image</span>
                                        <input id="establishment_header_image" name="establishment_header_image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'header-preview')">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, WebP up to 5MB</p>
                            </div>
                        </div>

                        <!-- Header Preview -->
                        <div id="header-preview" class="mt-3 hidden">
                            <div class="text-xs text-gray-500 mb-1">New Header Preview:</div>
                            <img class="w-full h-24 object-cover border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    <a href="<?= URLROOT ?>/referral/establishments" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Establishments
                    </a>
                    
                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Preview uploaded image before form submission
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}

/**
 * Handle drag and drop functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get all file input elements
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(function(input) {
        const dropArea = input.closest('.border-dashed');
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        dropArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                input.files = files;
                
                // Trigger preview
                const previewId = input.getAttribute('onchange').match(/'([^']+)'/)[1];
                previewImage(input, previewId);
            }
        }, false);
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            dropArea.classList.add('border-indigo-500', 'bg-indigo-50');
        }
        
        function unhighlight(e) {
            dropArea.classList.remove('border-indigo-500', 'bg-indigo-50');
        }
    });
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
