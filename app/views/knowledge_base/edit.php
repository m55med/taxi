<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']); ?></h1>
    </div>

    <!-- Form Container -->
    <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        <form id="kb-form" action="<?= URLROOT ?>/knowledge_base/update/<?= $data['article']['id'] ?>" method="POST">
            <?php view('knowledge_base/_form', ['ticket_codes' => $data['ticket_codes'], 'article' => $data['article']]); ?>
            
            <div class="mt-8 flex justify-between items-center">
                <?php if ($data['can_delete']) : ?>
                    <form action="<?= URLROOT ?>/knowledge_base/destroy" method="POST" class="inline-block" onsubmit="return confirm('هل أنت متأكد من حذف هذا المقال؟');">
                        <input type="hidden" name="id" value="<?= $data['article']['id'] ?>">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Delete Article
                        </button>
                    </form>
                <?php else : ?>
                    <div></div> <!-- Empty div for spacing -->
                <?php endif; ?>
                
                <div class="flex space-x-4">
                    <a href="<?= URLROOT ?>/knowledge_base" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                        Cancel
                    </a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                        Update Article
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 