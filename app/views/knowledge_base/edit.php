<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']); ?></h1>
    </div>

    <!-- Form Container -->
    <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        <form action="<?= URLROOT ?>/knowledge_base/update/<?= $data['article']['id'] ?>" method="POST">
            <?php include_once '_form.php'; ?>
        </form>
    </div>

</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 