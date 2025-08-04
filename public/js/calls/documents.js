console.log("✅ documents.js loaded");

/**
 * @file Manages the driver documents section with an interactive card-based UI.
 * @description Allows users to add/remove documents via checkboxes and edit
 * details (status, notes) for submitted documents.
 */

const documentsModule = {
    // Helper function to display notifications
    showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) {
            alert(message); // Fallback
            return;
        }

        const toast = document.createElement('div');
        const bgColor = { success: 'bg-green-500', error: 'bg-red-500' }[type] || 'bg-blue-500';
        const icon = { success: '<i class="fas fa-check-circle mr-3"></i>', error: '<i class="fas fa-times-circle mr-3"></i>' }[type] || '<i class="fas fa-info-circle mr-3"></i>';

        toast.className = `flex items-center text-white p-4 rounded-lg shadow-lg`;
        toast.classList.add(bgColor);
        toast.innerHTML = `${icon}<span>${message}</span>`;

        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    },

    // Initialization function
    init() {
        this.sectionContainer = document.getElementById('documents-section-container');
        if (!this.sectionContainer) return;

        this.driverId = this.sectionContainer.dataset.driverId;
        this.saveButton = document.getElementById('save-documents-btn');
        this.listContainer = document.getElementById('documents-list');

        // Modal elements
        this.deleteModal = document.getElementById('delete-confirm-modal');
        this.docNameSpan = document.getElementById('modal-doc-name');
        this.confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        this.cancelDeleteBtn = document.getElementById('cancel-delete-btn');

        if (!this.driverId || !this.saveButton || !this.listContainer || !this.deleteModal) {
            console.error('Documents module: Required elements are missing.');
            return;
        }

        // Delegated event listeners
        this.listContainer.addEventListener('input', (e) => this.handleInput(e));
        this.listContainer.addEventListener('click', (e) => this.handleClick(e));

        this.saveButton.addEventListener('click', () => this.handleSave());
        this.cancelDeleteBtn.addEventListener('click', () => this.closeDeleteModal());

        console.log('Documents module initialized.');
    },

    handleInput(e) {
        if (e.target.classList.contains('document-checkbox')) {
            this.toggleDetails(e.target);
        }
        this.saveButton.disabled = false;
    },

    handleClick(e) {
        const deleteButton = e.target.closest('.delete-document-btn');
        if (deleteButton) {
            const docId = deleteButton.dataset.docId;
            const docName = deleteButton.dataset.docName;
            this.openDeleteModal(docId, docName);
        }
    },

    openDeleteModal(docId, docName) {
        this.docNameSpan.textContent = docName;
        this.confirmDeleteBtn.dataset.docId = docId;
        this.deleteModal.classList.remove('hidden');

        // Use a one-time event listener for the confirmation
        this.confirmDeleteBtn.onclick = () => this.handleDelete(docId);
    },

    closeDeleteModal() {
        this.deleteModal.classList.add('hidden');
        this.confirmDeleteBtn.dataset.docId = '';
        this.confirmDeleteBtn.onclick = null; // Clean up listener
    },

    handleDelete(docId) {
        const checkbox = this.listContainer.querySelector(`.document-checkbox[value="${docId}"]`);
        if (checkbox) {
            checkbox.checked = false;
            this.toggleDetails(checkbox);
            this.saveButton.disabled = false;
        }
        this.closeDeleteModal();
    },

    // Toggles the visibility of the details section and updates card styles
    toggleDetails(checkbox) {
        const itemContainer = checkbox.closest('.document-item');
        const detailsSection = itemContainer.querySelector('.document-details');

        if (checkbox.checked) {
            detailsSection.classList.remove('hidden');
            itemContainer.classList.add('border-indigo-200', 'bg-indigo-50');
            itemContainer.classList.remove('border-gray-200', 'bg-white');
        } else {
            detailsSection.classList.add('hidden');
            itemContainer.classList.remove('border-indigo-200', 'bg-indigo-50');
            itemContainer.classList.add('border-gray-200', 'bg-white');
        }
    },

    // Function to handle saving the documents
    async handleSave() {
        this.saveButton.disabled = true;

        const checkedItems = this.listContainer.querySelectorAll('.document-checkbox:checked');

        const documentsPayload = Array.from(checkedItems).map(checkbox => {
            const itemContainer = checkbox.closest('.document-item');
            const statusSelect = itemContainer.querySelector('.status-select');
            const noteTextarea = itemContainer.querySelector('.note-textarea');

            return {
                id: parseInt(checkbox.value, 10),
                status: statusSelect ? statusSelect.value : 'submitted',
                note: noteTextarea ? noteTextarea.value.trim() : ''
            };
        });

        try {
            const response = await fetch(`${URLROOT}/calls/updateDocuments`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    driver_id: this.driverId,
                    documents: documentsPayload
                })
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Server error');

            this.showToast(data.message || 'تم حفظ التغييرات بنجاح.', 'success');

        } catch (error) {
            this.showToast(error.message || 'فشل حفظ التغييرات.', 'error');
            this.saveButton.disabled = false; // Re-enable button on error
        }
    }
};

// Initialize the module once the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    documentsModule.init();
});
