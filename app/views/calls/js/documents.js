/**
 * @file Manages the driver documents section with an improved, unified UI.
 * @description This script renders all required documents in a single list,
 * showing their status (e.g., submitted, missing) and allowing users to
 * add or remove them directly from the list.
 */

function initializeDocumentsModule() {
    // --- Helper Function Fallbacks ---
    const showNotification = window.showToast || ((message, type) => {
        console.log(`Toast (${type}): ${message}`);
        alert(message);
    });
    const showLoading = window.showLoading || (() => console.log('Loading...'));
    const hideLoading = window.hideLoading || (() => console.log('Loading finished.'));

    // --- DOM Element Selectors ---
    const sectionContainer = document.getElementById('documents-section-container');
    if (!sectionContainer) return;

    if (typeof driverId === 'undefined' || typeof allDocumentTypes === 'undefined' || typeof driverDocuments === 'undefined') {
        console.error('Documents script: Required data is not available.');
        sectionContainer.innerHTML = `<div class="text-center py-10"><p class="text-red-500">حدث خطأ فني أثناء تحميل بيانات المستندات.</p></div>`;
        return;
    }

    const listContainer = document.getElementById('documents-list');
    const saveButton = document.getElementById('save-documents-btn');
    const placeholder = document.getElementById('no-documents-placeholder');

    // --- State Management ---
    let state = {
        driverId: driverId,
        allTypes: allDocumentTypes || [],
        submitted: driverDocuments || [],
        isDirty: false
    };

    /**
     * Creates a card for a single document, adapting its appearance based on
     * whether it's been submitted or is currently missing.
     * @param {object} docData - The unified data for the document.
     * @returns {HTMLElement} The card element.
     */
    function createDocumentCard(docData) {
        const docId = docData.document_type_id;
        const card = document.createElement('div');
        card.className = 'document-card bg-gray-50 border rounded-lg p-4 transition-all duration-300';
        card.dataset.docId = docId;

        if (!docData.isSubmitted) {
            // --- RENDER CARD FOR A MISSING DOCUMENT ---
            card.classList.add('border-dashed', 'border-gray-300');
            card.innerHTML = `
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-semibold text-gray-600">${docData.name}</h4>
                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-200 text-gray-800">مفقود</span>
                    </div>
                    <button title="إضافة المستند" class="add-doc-btn bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-md text-sm transition-colors flex items-center">
                        <i class="fas fa-plus mr-1"></i>
                        <span>إضافة</span>
                    </button>
                </div>
            `;
        } else {
            // --- RENDER CARD FOR A SUBMITTED DOCUMENT ---
            card.classList.add('border-gray-200');
        const lastUpdate = docData.updated_at ? 
            new Date(docData.updated_at).toLocaleString('ar-SA', { dateStyle: 'short', timeStyle: 'short' }) : 'الآن';
        const updatedBy = docData.updated_by_name ? `بواسطة ${docData.updated_by_name}` : '';

            const statuses = { submitted: 'تم التسليم', approved: 'مقبول', rejected: 'مرفوض' };
        let statusOptions = '';
        for (const [key, value] of Object.entries(statuses)) {
            const isSelected = docData.status === key;
            statusOptions += `<option value="${key}" ${isSelected ? 'selected' : ''}>${value}</option>`;
        }

        const statusColorClass = {
            submitted: 'bg-yellow-200 text-yellow-800',
            approved: 'bg-green-200 text-green-800',
            rejected: 'bg-red-200 text-red-800',
        };
        const currentStatusColor = statusColorClass[docData.status] || 'bg-gray-200';

        card.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold text-gray-800">${docData.name}</h4>
                    <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-clock fa-fw mr-1"></i> آخر تحديث: ${lastUpdate} ${updatedBy}
                    </p>
                </div>
                <button title="إزالة المستند" class="remove-doc-btn text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times-circle fa-lg"></i>
                </button>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="text-xs font-medium text-gray-600">الحالة</label>
                    <select class="status-select w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm ${currentStatusColor}">
                        ${statusOptions}
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">ملاحظات</label>
                    <textarea class="note-textarea w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                        placeholder="أضف ملاحظة (اختياري)..." 
                        rows="1">${docData.note || ''}</textarea>
                </div>
            </div>
        `;
        }
        return card;
    }

    /**
     * The main render function. It builds a unified list of all required
     * documents and renders a card for each one based on its state.
     */
    function render() {
        listContainer.innerHTML = '';

        if (!state.allTypes || state.allTypes.length === 0) {
            placeholder.classList.remove('hidden');
            return;
        }

        placeholder.classList.add('hidden');

        const unifiedDocuments = state.allTypes.map(type => {
            const submittedDoc = state.submitted.find(doc => doc.document_type_id == type.id);
            if (submittedDoc) {
                return { ...submittedDoc, isSubmitted: true };
            } else {
                return {
                    document_type_id: type.id,
                    name: type.name,
                    status: 'missing',
                    isSubmitted: false
                };
            }
        }).sort((a, b) => (a.name || '').localeCompare(b.name || ''));

        unifiedDocuments.forEach(docData => {
            const docCard = createDocumentCard(docData);
            listContainer.appendChild(docCard);
        });

        saveButton.disabled = !state.isDirty;
    }

    /**
     * Handles adding a document to the 'submitted' state.
     * @param {number} docId - The ID of the document type to add.
     */
    function handleAdd(docId) {
        const docToAdd = state.allTypes.find(type => type.id == docId);
        const exists = state.submitted.find(doc => doc.document_type_id == docId);

        if (docToAdd && !exists) {
            state.submitted.push({
                document_type_id: docId,
                name: docToAdd.name,
                note: '',
                status: 'submitted', // Default status
            });
            state.isDirty = true;
            render();
        }
    }

    /**
     * Handles removing a document from the 'submitted' state.
     * @param {number} docId - The ID of the document type to remove.
     */
    function handleRemove(docId) {
        const docIndex = state.submitted.findIndex(doc => doc.document_type_id == docId);
        if (docIndex > -1) {
            state.submitted.splice(docIndex, 1);
            state.isDirty = true;
            render();
        }
    }

    /**
     * Saves all changes to the server.
     */
    async function handleSave() {
        const documentsPayload = state.submitted.map(submittedDoc => {
            const id = submittedDoc.document_type_id;
            const card = listContainer.querySelector(`.document-card[data-doc-id="${id}"]`);
            if (card) {
            return {
                id: id,
                    status: card.querySelector('.status-select').value,
                    note: card.querySelector('.note-textarea').value.trim()
            };
            }
            return null;
        }).filter(Boolean);
        
        showLoading();
        saveButton.disabled = true;

        try {
            const response = await fetch(`${BASE_PATH}/calls/updateDocuments`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    driver_id: state.driverId,
                    documents: documentsPayload
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Server error');
            }

            showNotification(data.message || 'تم الحفظ بنجاح', 'success');
            
            // The server returns an object, so we convert it to an array of values for our state.
            state.submitted = Object.values(data.documents || {});
            state.isDirty = false;
        
        } catch (error) {
            console.error('Error saving documents:', error);
            showNotification(error.message || 'فشل حفظ التغييرات', 'error');
        } finally {
            hideLoading();
            render(); // Re-render to show fresh state
        }
    }

    // --- Global Event Listeners ---
    saveButton.addEventListener('click', handleSave);

    sectionContainer.addEventListener('click', (e) => {
        const addBtn = e.target.closest('.add-doc-btn');
        if (addBtn) {
            e.preventDefault();
            const docId = addBtn.closest('.document-card').dataset.docId;
            handleAdd(docId);
            return;
        }

        const removeBtn = e.target.closest('.remove-doc-btn');
        if (removeBtn) {
            const docId = removeBtn.closest('.document-card').dataset.docId;
            handleRemove(docId);
            return;
        }
    });
    
    sectionContainer.addEventListener('input', (e) => {
        if (e.target.classList.contains('note-textarea') || e.target.classList.contains('status-select')) {
            state.isDirty = true;
            saveButton.disabled = false;
        }
    });

    // --- Initial Load ---
    render();
}

document.addEventListener('DOMContentLoaded', () => {
    // A short delay helps ensure all PHP-injected data is available.
    setTimeout(() => {
        if (typeof driverId !== 'undefined') {
            initializeDocumentsModule();
        } else {
            console.log('Documents module not initialized because no driver is loaded.');
        }
    }, 100);
});
