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

    <?php include_once __DIR__ . '/partials/ticket_actions.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Content Column -->

        <div class="lg:col-span-2">

            <?php include_once __DIR__ . '/partials/history_section.php'; ?>

        </div>

        <!-- Sidebar Column -->

        <div class="lg:col-span-1 space-y-6">

            <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

            

        </div>

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

            openObjectionForm: {}, // e.g., { 'discussion_1': false, 'discussion_2': true }

        }

    }

</script>



<!-- Include the new ticket search JavaScript -->

<script src="<?= BASE_URL ?>/app/views/tickets/js/ticket_search.js"></script>



<?php include_once __DIR__ . '/../includes/footer.php'; ?>