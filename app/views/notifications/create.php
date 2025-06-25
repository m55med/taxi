<?php include_once __DIR__ . '/../includes/header.php'; ?>

<body class="bg-gray-100">
<div class="container mx-auto p-4 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <a href="<?= BASE_PATH ?>/notifications/history" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
            <i class="fas fa-history mr-2"></i> View History
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 sm:p-8">
        <form id="notificationForm" action="<?= BASE_PATH ?>/notifications/store" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required>
            </div>

            <div class="mb-6">
                <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Message:</label>
                <input name="message" type="hidden">
                <div id="editor" style="height: 300px;">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 space-x-reverse">
                <a href="<?= BASE_PATH ?>/notifications" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                    Send Notification to All Users
                </button>
            </div>
        </form>
    </div>

    <!-- Image URL Modal -->
    <div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 1000;">
        <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg shadow-lg">
            <button id="closeModal" class="absolute top-0 right-0 mt-4 mr-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-bold mb-4">Add Image from URL</h3>
            <p class="text-sm text-gray-600 mb-4">Please provide a direct link to an image (e.g., ending in .jpg, .png, .gif).</p>
            <div>
                <input type="text" id="imageUrlInput" placeholder="https://example.com/image.png" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200">
                <p id="imageUrlError" class="text-red-500 text-xs italic mt-2" style="display: none;"></p>
            </div>
            <div class="flex justify-end space-x-4 mt-6 space-x-reverse">
                <button id="cancelImage" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                <button id="addImage" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Image</button>
            </div>
        </div>
    </div>

    <!-- Link URL Modal -->
    <div id="linkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 1000;">
        <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg shadow-lg">
            <button id="closeLinkModal" class="absolute top-0 right-0 mt-4 mr-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-bold mb-4">Add or Edit Link</h3>
            <div class="mb-4">
                <label for="linkUrlInput" class="block text-gray-700 text-sm font-bold mb-2">URL:</label>
                <input type="text" id="linkUrlInput" placeholder="https://example.com" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200">
                <p id="linkUrlError" class="text-red-500 text-xs italic mt-2" style="display: none;"></p>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="linkNewTabInput" class="form-checkbox h-5 w-5 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Open in a new tab</span>
                </label>
            </div>
            <div class="flex justify-end space-x-4 mt-6 space-x-reverse">
                <button id="cancelLink" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                <button id="addLink" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Link</button>
            </div>
        </div>
    </div>
</div>

<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                ['link', 'image'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

    const imageModal = document.getElementById('imageModal');
    const addImageBtn = document.getElementById('addImage');
    const cancelImageBtn = document.getElementById('cancelImage');
    const closeModalBtn = document.getElementById('closeModal');
    const imageUrlInput = document.getElementById('imageUrlInput');
    const imageUrlError = document.getElementById('imageUrlError');
    let savedRange;

    function customImageHandler() {
        savedRange = quill.getSelection(true);
        imageModal.style.display = 'flex';
        imageUrlInput.value = '';
        imageUrlError.style.display = 'none';
    }

    quill.getModule('toolbar').addHandler('image', customImageHandler);

    function closeModal() {
        imageModal.style.display = 'none';
    }

    cancelImageBtn.addEventListener('click', closeModal);
    closeModalBtn.addEventListener('click', closeModal);

    addImageBtn.addEventListener('click', () => {
        const url = imageUrlInput.value;
        if (url && /\.(jpe?g|png|gif|webp)$/i.test(url)) {
            quill.insertEmbed(savedRange.index, 'image', url, Quill.sources.USER);
            closeModal();
        } else {
            imageUrlError.textContent = 'Invalid URL. Please provide a direct link to an image file.';
            imageUrlError.style.display = 'block';
        }
    });

    // --- Link Modal Logic ---
    const linkModal = document.getElementById('linkModal');
    const addLinkBtn = document.getElementById('addLink');
    const cancelLinkBtn = document.getElementById('cancelLink');
    const closeLinkModalBtn = document.getElementById('closeLinkModal');
    const linkUrlInput = document.getElementById('linkUrlInput');
    const linkNewTabInput = document.getElementById('linkNewTabInput');
    const linkUrlError = document.getElementById('linkUrlError');
    let linkSavedRange;

    function customLinkHandler() {
        linkSavedRange = quill.getSelection(true);
        if (linkSavedRange) {
            const existingLink = quill.getFormat(linkSavedRange).link;
            if (existingLink) {
                linkUrlInput.value = existingLink.href || existingLink;
            } else {
                linkUrlInput.value = '';
            }
        }
        linkNewTabInput.checked = true;
        linkUrlError.style.display = 'none';
        linkModal.style.display = 'flex';
    }

    quill.getModule('toolbar').addHandler('link', customLinkHandler);

    function closeLinkModal() {
        linkModal.style.display = 'none';
    }

    cancelLinkBtn.addEventListener('click', closeLinkModal);
    closeLinkModalBtn.addEventListener('click', closeLinkModal);

    addLinkBtn.addEventListener('click', () => {
        const url = linkUrlInput.value;
        if (url && (url.startsWith('http') || url.startsWith('/'))) {
            const text = quill.getText(linkSavedRange.index, linkSavedRange.length);
            const target = linkNewTabInput.checked ? ' target="_blank"' : '';

            quill.deleteText(linkSavedRange.index, linkSavedRange.length);
            
            const linkHtml = `<a href="${url}"${target}>${text || url}</a>`;
            quill.clipboard.dangerouslyPasteHTML(linkSavedRange.index, linkHtml, 'user');
            
            quill.setSelection(linkSavedRange.index + (text || url).length, 0);
            closeLinkModal();
        } else {
            linkUrlError.textContent = 'Please enter a valid URL (e.g., https://example.com).';
            linkUrlError.style.display = 'block';
        }
    });

    var form = document.getElementById('notificationForm');
    form.onsubmit = function() {
        var messageInput = document.querySelector('input[name=message]');
        messageInput.value = quill.root.innerHTML;
        return true;
    };
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 