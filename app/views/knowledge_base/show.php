<?php require_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" />

<style>
    /* Read-only styles to mimic Quill editor look */
    .ql-editor {
        font-family: 'Cairo', sans-serif;
        font-size: 1.125rem; /* text-lg */
        line-height: 1.75;
        padding: 0;
    }
    .ql-editor h1 { font-size: 2.25rem; font-weight: bold; margin-bottom: 1rem; }
    .ql-editor h2 { font-size: 1.875rem; font-weight: bold; margin-bottom: 1rem; }
    .ql-editor h3 { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; }
    .ql-editor p, .ql-editor ul, .ql-editor ol { margin-bottom: 1rem; }
    .ql-editor a { color: #3b82f6; text-decoration: underline; }
    .ql-editor pre.ql-syntax {
        background-color: #1f2937; /* gray-800 */
        color: #f3f4f6; /* gray-100 */
        padding: 1rem;
        border-radius: 0.5rem; /* rounded-lg */
        margin-bottom: 1rem;
        white-space: pre-wrap;
        font-family: monospace;
    }
    .ql-editor blockquote {
        border-right: 4px solid #e5e7eb; /* gray-300 */
        padding-right: 1rem;
        margin-right: 1rem;
        font-style: italic;
        color: #4b5563; /* gray-600 */
    }
</style>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div class="flex-1 mb-4 sm:mb-0">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800"><?= htmlspecialchars($data['article']['title']) ?></h1>
            <?php if ($data['article']['ticket_code_name']) : ?>
                <div class="mt-2">
                    <span class="text-sm font-semibold text-gray-500">مرتبط بالكود:</span>
                    <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-700 rounded-full"><?= htmlspecialchars($data['article']['ticket_code_name']) ?></span>
                </div>
            <?php endif; ?>
        </div>
        <a href="<?= URLROOT ?>/knowledge_base" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-right ml-2"></i>
            العودة إلى القائمة
        </a>
    </div>

    <!-- Article Content -->
    <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        <div class="ql-snow">
             <div class="ql-editor">
                <?= $data['article']['content'] /* Content is already sanitized from a trusted source (admin) */ ?>
             </div>
        </div>
    </div>
    
    <!-- Meta Info -->
    <div class="text-xs text-gray-500 mt-4 text-left" dir="ltr">
        Last updated by <?= htmlspecialchars($data['article']['updated_by_name'] ?? 'N/A') ?> on <?= date('F j, Y, g:i a', strtotime($data['article']['updated_at'])) ?>
    </div>

</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 