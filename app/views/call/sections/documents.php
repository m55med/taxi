<?php
defined('BASE_PATH') or define('BASE_PATH', '');
// Initialize variables if not set
$document_types = $document_types ?? [];
$required_documents = $required_documents ?? [];
?>

<!-- Notification Element -->
<div id="notification" class="fixed top-4 left-1/2 transform -translate-x-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg z-50 hidden animated">
    <div class="p-4 flex items-center">
        <i id="notificationIcon" class="fas fa-check-circle text-green-500 text-xl"></i>
        <span id="notificationMessage" class="mr-3 text-gray-700"></span>
    </div>
</div>

<!-- Main Content -->
<?php if (isset($driver)): ?>
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">
            <i class="fas fa-file-alt ml-2"></i>
            المستندات المطلوبة
        </h2>
        <button type="button" id="saveDocuments" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
            <i class="fas fa-save ml-1"></i>
            حفظ التغييرات
        </button>
    </div>

    <form id="documentsForm" data-driver-id="<?= $driver['id'] ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($document_types as $doc): ?>
                <?php
                    $isRequired = $doc['is_required'] == 1;
                    $existingDoc = $required_documents[$doc['id']] ?? null;
                    $isMissing = $existingDoc && $existingDoc['status'] === 'missing';
                    $note = $existingDoc ? $existingDoc['note'] : '';
                ?>
                <div class="border rounded-lg p-4 document-item <?= $isRequired ? 'bg-gray-50' : '' ?>">
                    <div class="flex items-start mb-2">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   id="doc_<?= $doc['id'] ?>" 
                                   name="documents[]" 
                                   value="<?= $doc['id'] ?>"
                                   class="document-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   <?= $isMissing ? 'checked' : '' ?>>
                        </div>
                        <div class="mr-3 flex-grow">
                            <label for="doc_<?= $doc['id'] ?>" class="text-lg font-semibold text-gray-800">
                                <?= htmlspecialchars($doc['name']) ?>
                                <?php if ($isRequired): ?>
                                    <span class="text-red-500 text-sm">(مطلوب)</span>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">(اختياري)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>

                    <div class="mr-7">
                        <textarea 
                            name="notes[<?= $doc['id'] ?>]" 
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" 
                            placeholder="<?= $isRequired ? 'ملاحظة (مطلوبة)...' : 'ملاحظة (اختيارية)...' ?>"
                            rows="2"><?= htmlspecialchars($note) ?></textarea>
                    </div>

                    <?php if ($existingDoc && $existingDoc['updated_at']): ?>
                        <div class="mt-2 text-sm text-gray-500 mr-7 document-info">
                            <i class="fas fa-clock ml-1"></i>
                            آخر تحديث: <?= date('Y-m-d H:i', strtotime($existingDoc['updated_at'])) ?>
                            <?php if ($existingDoc['updated_by_name']): ?>
                                <span class="mr-2">
                                    <i class="fas fa-user ml-1"></i>
                                    بواسطة: <?= htmlspecialchars($existingDoc['updated_by_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>
<?php endif; ?> 