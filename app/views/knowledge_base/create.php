<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Create New Article</h1>
    </div>

    <!-- Form Container -->
    <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        <form id="kb-form" action="<?= URLROOT ?>/knowledge_base/store" method="POST">
            <?php include_once '_form.php'; ?>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="<?= URLROOT ?>/knowledge_base" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    Save Article
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 