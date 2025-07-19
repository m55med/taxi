
<?php flash('kb_message'); ?>

<input type="hidden" name="id" value="<?= htmlspecialchars($article['id'] ?? '') ?>">

<div class="mb-6">
    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Article Title:</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($article['title'] ?? '') ?>" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
</div>

<div class="mb-6">
    <label for="ticket_code_id" class="block text-gray-700 text-sm font-bold mb-2">Link to Ticket Code (Optional):</label>
    <select id="ticket_code_id" name="ticket_code_id" class="shadow border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">-- None --</option>
        <?php foreach ($ticket_codes as $code) : ?>
            <option value="<?= $code['id'] ?>" <?= (isset($article['ticket_code_id']) && $article['ticket_code_id'] == $code['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($code['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-6">
    <label for="editor" class="block text-gray-700 text-sm font-bold mb-2">Content:</label>
    <input name="content" type="hidden">
    <div id="editor" class="bg-white rounded-lg" style="height: 300px;">
        <?= $article['content'] ?? '' ?>
    </div>
</div>

<!-- Modal for Image URL Input -->
<div id="image-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 1050;">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Add Image from URL</h3>
            <div class="mt-2 px-7 py-3">
                <input type="text" id="image-url-input" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none" placeholder="https://example.com/image.jpg" />
                <div id="image-preview-container" class="mt-4" style="display:none;">
                     <img id="image-preview" src="" alt="Image Preview" style="max-height: 200px; max-width: 100%; margin: 0 auto; border-radius: 5px;" />
                </div>
                <p id="image-error" class="text-red-500 text-sm mt-2" style="display:none;">Invalid URL or image cannot be loaded.</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="close-modal" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancel</button>
                <button id="insert-image-btn" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Insert Image</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Quill stylesheet -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />

<!-- Include the Quill library -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- Initialize Quill editor -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'direction': 'rtl' }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        'image': imageHandler
                    }
                }
            }
        });

        const modal = document.getElementById('image-modal');
        const closeModal = document.getElementById('close-modal');
        const insertImageBtn = document.getElementById('insert-image-btn');
        const imageUrlInput = document.getElementById('image-url-input');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');
        const imageError = document.getElementById('image-error');

        function imageHandler() {
            modal.style.display = 'flex';
            imageUrlInput.value = '';
            imagePreviewContainer.style.display = 'none';
            imageError.style.display = 'none';
        }
        
        closeModal.addEventListener('click', () => {
             modal.style.display = 'none';
        });

        imageUrlInput.addEventListener('input', () => {
            const url = imageUrlInput.value.trim();
            if (url) {
                const img = new Image();
                img.onload = function() {
                    imagePreview.src = url;
                    imagePreviewContainer.style.display = 'block';
                    imageError.style.display = 'none';
                };
                img.onerror = function() {
                    imagePreviewContainer.style.display = 'none';
                    imageError.style.display = 'block';
                };
                img.src = url;
            } else {
                imagePreviewContainer.style.display = 'none';
                imageError.style.display = 'none';
            }
        });

        insertImageBtn.addEventListener('click', () => {
            const url = imageUrlInput.value.trim();
            if (url && imagePreview.src === url && imageError.style.display === 'none') {
                const range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', url);
                modal.style.display = 'none';
            } else {
                 imageError.style.display = 'block';
            }
        });

        // Sync Quill content with hidden input before form submission
        const form = document.getElementById('kb-form');
        if(form) {
            form.addEventListener('submit', function() {
                const contentInput = document.querySelector('input[name=content]');
                contentInput.value = quill.root.innerHTML;
            });
        }
    });
</script> 