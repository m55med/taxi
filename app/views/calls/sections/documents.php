<?php
defined('BASE_PATH') or define('BASE_PATH', '');
// Initialize variables for safety
$document_types = $data['document_types'] ?? [];
$required_documents = $data['required_documents'] ?? [];
$driver = $data['driver'] ?? null;
?>

<?php if ($driver): ?>
<div id="documents-section-container" data-driver-id="<?= htmlspecialchars($driver['id'] ?? '') ?>">
    <!-- Header: Title and Action Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-file-alt mr-3 text-indigo-500"></i>
            <span>Driver Documents</span>
        </h2>
        <div class="flex items-center gap-2">
            <button id="save-documents-btn" type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-save mr-2"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </div>

    <!-- Container for document items -->
    <div id="documents-list" class="space-y-4">
        <?php if (empty($document_types)): ?>
            <p class="text-gray-500 text-center py-4">No document types have been configured in the system.</p>
        <?php else: ?>
            <?php
            // Create a lookup map for faster access to submitted document details.
            $submitted_docs_map = [];
            foreach ($required_documents as $doc) {
                $submitted_docs_map[$doc['document_type_id']] = $doc;
            }
            ?>
            <?php foreach ($document_types as $doc_type): ?>
                <?php
                $doc_id = $doc_type['id'];
                $submitted_doc = $submitted_docs_map[$doc_id] ?? null;
                $isChecked = $submitted_doc !== null;
                ?>
                <div class="document-item border rounded-lg overflow-hidden transition-all duration-300 <?= $isChecked ? 'border-indigo-200 bg-indigo-50' : 'border-gray-200 bg-white' ?>" data-doc-id="<?= $doc_id ?>">
                    <!-- Header with Checkbox and Name -->
                    <label class="flex items-center p-4 cursor-pointer bg-gray-50 border-b hover:bg-gray-100 transition-colors">
                        <input type="checkbox"
                               class="document-checkbox form-checkbox h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                               value="<?= $doc_id ?>"
                               <?= $isChecked ? 'checked' : '' ?>>
                        <span class="ml-4 text-gray-800 font-semibold"><?= htmlspecialchars($doc_type['name']) ?></span>
                    </label>
                    
                    <!-- Details Section (Status, Note, etc.) -->
                    <div class="document-details p-4 space-y-4 <?= !$isChecked ? 'hidden' : '' ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-600 block mb-1">Status</label>
                                <select class="status-select w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <?php
                                    $statuses = ['submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'];
                                    $current_status = $submitted_doc['status'] ?? 'submitted';
                                    if (!array_key_exists($current_status, $statuses)) {
                                        $current_status = 'submitted';
                                    }
                                    foreach ($statuses as $key => $value) {
                                        $selected = ($key === $current_status) ? 'selected' : '';
                                        echo "<option value='{$key}' {$selected}>{$value}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600 block mb-1">Notes</label>
                                <textarea class="note-textarea w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                          placeholder="Add a note (optional)..."
                                          rows="1"><?= htmlspecialchars($submitted_doc['note'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <?php if ($submitted_doc && !empty($submitted_doc['updated_at'])): ?>
                            <div class="text-xs text-gray-500 text-right pt-2 border-t mt-4">
                                <i class="fas fa-clock fa-fw"></i>
                                Last updated: <?= (new DateTime($submitted_doc['updated_at']))->format('Y-m-d H:i') ?>
                                <?php if (!empty($submitted_doc['updated_by_name'])): ?>
                                    by <?= htmlspecialchars($submitted_doc['updated_by_name']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>