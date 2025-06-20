<?php
defined('BASE_PATH') or define('BASE_PATH', '');
// Initialize variables for safety
$document_types = $document_types ?? [];
$required_documents = $required_documents ?? [];
$driver = $driver ?? null;
?>

<?php if ($driver): ?>
<div class="bg-white rounded-lg shadow p-6" id="documents-section-container">
    <!-- Header: Title and Action Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-file-alt ml-3 text-indigo-500"></i>
            <span>مستندات السائق</span>
        </h2>
        <div class="flex items-center gap-2">
            <!-- Add Document Dropdown -->
            <div id="add-document-container" class="relative inline-block text-left">
                <!-- Dropdown will be rendered here by JS -->
            </div>
            <button id="save-documents-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                <i class="fas fa-save ml-2"></i>
                <span>حفظ التغييرات</span>
            </button>
        </div>
    </div>

    <!-- Container for document cards -->
    <div id="documents-list" class="space-y-4">
        <!-- Document cards will be rendered here by JS -->
    </div>
    
    <!-- Placeholder for when no documents are added -->
    <div id="no-documents-placeholder" class="text-center py-10 border-2 border-dashed border-gray-300 rounded-lg hidden">
        <div class="text-gray-400">
            <svg class="mx-auto h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-800">لا توجد مستندات</h3>
            <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة أول مستند للسائق.</p>
        </div>
    </div>
</div>

<!-- Pass initial data to JavaScript -->
<script>
    // Make sure to only output valid JSON
    const allDocumentTypes = <?= json_encode(array_values($document_types), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const driverDocuments = <?= json_encode($required_documents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const driverId = <?= json_encode($driver['id'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php endif; ?>