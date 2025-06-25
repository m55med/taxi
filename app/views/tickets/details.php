<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" x-data="ticketDetails()">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']); ?> #<?= htmlspecialchars($data['ticket']['ticket_number']) ?></h1>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </a>
    </div>

    <?php include_once __DIR__ . '/partials/page_actions.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <?php include_once __DIR__ . '/partials/ticket_info.php'; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <?php include_once __DIR__ . '/partials/related_info.php'; ?>
            
            <!-- Reviews Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <?php include_once __DIR__ . '/partials/reviews_section.php'; ?>
            </div>
    
            <!-- Discussions Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <?php include_once __DIR__ . '/partials/discussions_section.php'; ?>
            </div>
        </div>
    </div>

    <!-- Ticket History Section -->
    <div class="mt-6">
        <?php include_once __DIR__ . '/partials/history_section.php'; ?>
    </div>

    <!-- Image URL Modal for Discussions -->
    <div id="discussionImageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 1000;">
        <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg shadow-lg">
            <button id="closeDiscussionImageModal" class="absolute top-0 right-0 mt-4 mr-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-bold mb-4">Add Image from URL</h3>
            <p class="text-sm text-gray-600 mb-4">Please provide a direct link to an image (e.g., ending in .jpg, .png, .gif).</p>
            <div>
                <input type="text" id="discussionImageUrlInput" placeholder="https://example.com/image.png" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200">
                <p id="discussionImageUrlError" class="text-red-500 text-xs italic mt-2" style="display: none;"></p>
            </div>
            <div class="flex justify-end space-x-4 mt-6">
                <button id="cancelDiscussionImage" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                <button id="addDiscussionImage" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Image</button>
            </div>
        </div>
    </div>
</div>

<script>
    function ticketDetails() {
        return {
            openReviewForm: false,
            openDiscussionForm: false,
            openObjectionForm: {}, // e.g., { 'discussion_1': false, 'discussion_2': true }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('discussionEditor')) {
            var quill = new Quill('#discussionEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        ['link', 'image']
                    ]
                }
            });

            // --- New Image Modal Logic for Discussions ---
            const imageModal = document.getElementById('discussionImageModal');
            const addImageBtn = document.getElementById('addDiscussionImage');
            const cancelImageBtn = document.getElementById('cancelDiscussionImage');
            const closeModalBtn = document.getElementById('closeDiscussionImageModal');
            const imageUrlInput = document.getElementById('discussionImageUrlInput');
            const imageUrlError = document.getElementById('discussionImageUrlError');
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
            // --- End of New Logic ---

            var form = document.getElementById('discussionForm');
            if(form) {
                form.onsubmit = function() {
                    var messageInput = document.querySelector('input[name=notes]');
                    messageInput.value = quill.root.innerHTML;
                    return true;
                };
            }
        }
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 