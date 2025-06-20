/**
 * @file Manages the driver documents section with a more interactive UI.
 * @description This script handles rendering document cards, adding/removing documents,
 * and saving the state to the server. It's designed to be more user-friendly
 * than the previous checkbox-based system.
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- Helper Function Fallbacks ---
    // Define dummy functions if they don't exist to prevent errors.
    const showToast = window.showToast || ((message, type) => {
        console.log(`Toast (${type}): ${message}`);
        alert(message);
    });
    const showLoading = window.showLoading || (() => console.log('Loading...'));
    const hideLoading = window.hideLoading || (() => console.log('Loading finished.'));

    // --- DOM Element Selectors ---
    const sectionContainer = document.getElementById('documents-section-container');
    if (!sectionContainer) return;

    // Check if the required data from PHP is available
    if (typeof driverId === 'undefined' || typeof allDocumentTypes === 'undefined' || typeof driverDocuments === 'undefined') {
        console.error('Documents script: Required data (driverId, allDocumentTypes, driverDocuments) is not available on the page.');
        if (placeholder) {
            placeholder.classList.remove('hidden');
            placeholder.innerHTML = `<div class="text-red-500">حدث خطأ أثناء تحميل بيانات المستندات.</div>`;
        }
        return;
    }

    const listContainer = document.getElementById('documents-list');
    const saveButton = document.getElementById('save-documents-btn');
    const addContainer = document.getElementById('add-document-container');
    const placeholder = document.getElementById('no-documents-placeholder');

    // --- State Management ---
    // A central place to hold the current state of documents.
    let state = {
        driverId: driverId,
        allTypes: allDocumentTypes || [],
        // The list of documents currently marked as 'submitted' for the driver.
        // This will be populated by initializeState.
        submitted: {},
        // A flag to track if there are unsaved changes.
        isDirty: false
    };

    /**
     * Initializes the state from data passed by PHP.
     */
    function initializeState() {
        // The `driverDocuments` variable from PHP is already an object keyed by document_type_id,
        // which is exactly the format we need for our `state.submitted`. 
        // We can assign it directly. This is simpler and more robust.
        state.submitted = driverDocuments || {};
    }

    // --- Core Rendering Functions ---

    /**
     * The main render function. It orchestrates the display of the entire section
     * based on the current state.
     */
    function render() {
        // Clear all dynamic content
        listContainer.innerHTML = '';
        addContainer.innerHTML = '';
        
        const submittedIds = Object.keys(state.submitted).map(id => parseInt(id));
        const missingDocs = state.allTypes.filter(type => !submittedIds.includes(parseInt(type.id)));

        // Render submitted documents
        if (submittedIds.length > 0) {
            placeholder.classList.add('hidden');
            // Sort submitted documents alphabetically for consistent order
            const sortedSubmitted = Object.values(state.submitted).sort((a, b) => {
                // Add fallback to prevent crash if a name is missing
                const nameA = a.name || '';
                const nameB = b.name || '';
                return nameA.localeCompare(nameB);
            });
            sortedSubmitted.forEach(docData => {
                const docCard = createDocumentCard(docData);
                listContainer.appendChild(docCard);
            });
        } else {
            placeholder.classList.remove('hidden');
        }

        // Render 'Add Document' dropdown if there are documents to add
        if (missingDocs.length > 0) {
            const addDropdown = createAddDropdown(missingDocs);
            addContainer.appendChild(addDropdown);
        }
        
        // Enable/disable the save button based on the dirty state
        saveButton.disabled = !state.isDirty;
    }

    /**
     * Creates an HTML element for a single submitted document card.
     * @param {object} docData - The data for the document.
     * @returns {HTMLElement} The card element.
     */
    function createDocumentCard(docData) {
        // Ensure docData and its name property exist to prevent errors
        if (!docData || !docData.name) {
            console.warn('Attempted to create a card for an invalid document:', docData);
            return document.createDocumentFragment(); // Return an empty, non-disruptive element
        }

        const docId = docData.document_type_id || docData.id;
        const card = document.createElement('div');
        card.className = 'document-card bg-gray-50 border border-gray-200 rounded-lg p-4 transition-all duration-300';
        card.dataset.docId = docId;

        const lastUpdate = docData.updated_at ? 
            new Date(docData.updated_at).toLocaleString('ar-SA', { dateStyle: 'short', timeStyle: 'short' }) : 'الآن';
        const updatedBy = docData.updated_by_name ? `بواسطة ${docData.updated_by_name}` : '';

        card.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold text-gray-800">${docData.name}</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-clock fa-fw ml-1"></i> آخر تحديث: ${lastUpdate} ${updatedBy}
                    </p>
                </div>
                <button title="إزالة المستند" class="remove-doc-btn text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times-circle fa-lg"></i>
                </button>
            </div>
            <div class="mt-3">
                <textarea class="note-textarea w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                    placeholder="أضف ملاحظة (اختياري)..." 
                    rows="2">${docData.note || ''}</textarea>
            </div>
        `;
        return card;
    }

    /**
     * Creates the 'Add Document' dropdown button and its menu.
     * @param {Array<object>} missingDocs - List of documents that can be added.
     * @returns {HTMLElement} The dropdown container element.
     */
    function createAddDropdown(missingDocs) {
        const dropdownContainer = document.createElement('div');
        
        // Sort missing documents alphabetically and filter out any invalid ones
        missingDocs
            .filter(doc => doc && doc.name) // Ensure doc and doc.name exist
            .sort((a,b) => a.name.localeCompare(b.name));

        const menuItems = missingDocs.map(doc => `
            <a href="#" class="add-doc-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-doc-id="${doc.id}">
                ${doc.name}
            </a>
        `).join('');

        dropdownContainer.innerHTML = `
            <button id="add-doc-btn" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-plus ml-2"></i> إضافة مستند
            </button>
            <div id="add-doc-menu" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-10">
                <div class="py-1" role="menu" aria-orientation="vertical">
                    ${menuItems}
                </div>
            </div>
        `;
        return dropdownContainer;
    }

    // --- Event Handlers & State Changers ---

    /**
     * Handles removing a document from the submitted list.
     * @param {number} docId - The ID of the document to remove.
     */
    function handleRemove(docId) {
        if (state.submitted[docId]) {
            delete state.submitted[docId];
            state.isDirty = true;
            render();
        }
    }

    /**
     * Handles adding a new document to the submitted list.
     * @param {number} docId - The ID of the document to add.
     */
    function handleAdd(docId) {
        const docToAdd = state.allTypes.find(type => parseInt(type.id) === docId);
        if (docToAdd && !state.submitted[docId]) {
            // Add a temporary object to the state
            state.submitted[docId] = {
                document_type_id: docId,
                name: docToAdd.name,
                note: '',
                status: 'submitted',
                updated_at: null,
                updated_by_name: null
            };
            state.isDirty = true;
            // Close the dropdown and re-render
            const menu = document.getElementById('add-doc-menu');
            if(menu) menu.classList.add('hidden');
            render();
        }
    }

    /**
     * Handles saving all changes to the server.
     */
    async function handleSave() {
        // Collect data to send
        const submittedDocIds = Object.keys(state.submitted);
        const notes = {};
        submittedDocIds.forEach(id => {
            const card = listContainer.querySelector(`.document-card[data-doc-id="${id}"]`);
            if (card) {
                const noteText = card.querySelector('.note-textarea').value.trim();
                if (noteText) {
                    notes[id] = noteText;
                }
            }
        });
        
        showLoading();
        saveButton.disabled = true;

        try {
            const response = await fetch(`${BASE_PATH}/calls/documents/update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    driver_id: state.driverId,
                    documents: submittedDocIds,
                    notes: notes
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Server error');
            }

            showToast(data.message || 'تم الحفظ بنجاح', 'success');
            // Update state with fresh data from the server
            state.submitted = {};
            Object.values(data.documents).forEach(doc => {
                 if (doc.status === 'submitted') {
                    state.submitted[doc.document_type_id] = doc;
                }
            });
            state.isDirty = false;
        
        } catch (error) {
            console.error('Error saving documents:', error);
            showToast(error.message || 'فشل حفظ التغييرات', 'error');
        } finally {
            hideLoading();
            render(); // Re-render to show fresh state and correct save button status
        }
    }

    // --- Global Event Listeners ---

    // Listener for the save button
    saveButton.addEventListener('click', handleSave);

    // Use event delegation for dynamically created elements
    sectionContainer.addEventListener('click', (e) => {
        // Handle removing a document
        const removeBtn = e.target.closest('.remove-doc-btn');
        if (removeBtn) {
            const docId = parseInt(removeBtn.closest('.document-card').dataset.docId);
            handleRemove(docId);
            return;
        }

        // Handle adding a document from the dropdown
        const addItem = e.target.closest('.add-doc-item');
        if (addItem) {
            e.preventDefault();
            const docId = parseInt(addItem.dataset.docId);
            handleAdd(docId);
            return;
        }

        // Handle toggling the add-document dropdown
        const addBtn = e.target.closest('#add-doc-btn');
        const menu = document.getElementById('add-doc-menu');
        if (addBtn && menu) {
            menu.classList.toggle('hidden');
            return;
        }

        // Close dropdown if clicking outside
        if (menu && !menu.classList.contains('hidden') && !e.target.closest('#add-document-container')) {
            menu.classList.add('hidden');
        }
    });
    
    // Listen for changes in textareas to set the dirty state
    sectionContainer.addEventListener('input', (e) => {
        if(e.target.classList.contains('note-textarea')) {
            state.isDirty = true;
            saveButton.disabled = false;
        }
    });

    // --- Initial Load ---
    initializeState();
    render();
});
