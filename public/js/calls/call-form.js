console.log("✅ call-form.js loaded");

const CallFormManager = {
    init() {
        this.form = document.getElementById('callForm');
        if (!this.form) return;

        this.driverId = this.form.querySelector('[name="driver_id"]').value;
        this.submitButton = this.form.querySelector('button[type="submit"]');

        this.bindEventListeners();
        this.initializeSelects();
    },

    bindEventListeners() {
        // Handle call status button clicks
        const statusButtons = this.form.querySelectorAll('.call-status-btn');
        statusButtons.forEach(btn => {
            btn.addEventListener('click', () => this.handleStatusClick(btn));
        });

        // Handle form submission
        this.form.addEventListener('submit', (e) => this.submitForm(e));
    },

    handleStatusClick(clickedButton) {
        const status = clickedButton.dataset.status;

        // Update UI
        this.form.querySelectorAll('.call-status-btn').forEach(btn => {
            btn.classList.remove('selected-status', 'border-green-500', 'border-red-500', 'border-yellow-500', 'border-blue-500', 'border-orange-500', 'border-purple-500');
            const selectedBorder = clickedButton.dataset.border; // Get border from data attribute
            if (btn === clickedButton) {
                btn.classList.add('selected-status', selectedBorder);
            }
        });

        // Update hidden input
        this.form.querySelector('#selectedCallStatus').value = status;

        // Show/hide next call time section
        const nextCallSection = document.getElementById('nextCallSection');
        const nextCallInput = document.getElementById('nextCallTime');

        if (['no_answer', 'busy', 'not_available', 'rescheduled'].includes(status)) {
            nextCallSection.classList.remove('hidden');
            if (status !== 'rescheduled') {
                const hoursMap = { 'no_answer': 1, 'busy': 0.5, 'not_available': 3 };
                this.setDefaultNextCallTime(hoursMap[status]);
            }
        } else {
            nextCallSection.classList.add('hidden');
            nextCallInput.value = '';
        }

        this.validateForm();
    },

    setDefaultNextCallTime(hours) {
        const nextCallInput = document.getElementById('nextCallTime');
        const now = new Date();
        now.setTime(now.getTime() + (hours * 60 * 60 * 1000));

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hour = String(now.getHours()).padStart(2, '0');
        const minute = String(now.getMinutes()).padStart(2, '0');

        nextCallInput.value = `${year}-${month}-${day}T${hour}:${minute}`;
    },

    initializeSelects() {
        const categorySelect = this.form.querySelector('#ticket_category_id');
        const subcategorySelect = this.form.querySelector('#ticket_subcategory_id');
        const codeSelect = this.form.querySelector('#ticket_code_id');

        categorySelect.addEventListener('change', () => {
            this.fetchSubcategories(categorySelect.value);
            // Reset subsequent selects
            this.updateSelectOptions(subcategorySelect, [], 'Select a subcategory...');
            this.updateSelectOptions(codeSelect, [], 'Select a code...');
        });

        subcategorySelect.addEventListener('change', () => {
            this.fetchCodes(subcategorySelect.value);
            // Reset code select
            this.updateSelectOptions(codeSelect, [], 'Select a code...');
        });
    },

    async fetchSubcategories(categoryId) {
        if (!categoryId) return;
        try {
            const response = await fetch(`${URLROOT}/calls/subcategories/${categoryId}`);
            if (!response.ok) throw new Error('Server error');
            const data = await response.json();
            this.updateSelectOptions(this.form.querySelector('#ticket_subcategory_id'), data, 'Select a subcategory...');
        } catch (error) {
            console.error('Error fetching subcategories:', error);
            showToast('Failed to load subcategories.', 'error');
        }
    },

    async fetchCodes(subcategoryId) {
        if (!subcategoryId) return;
        try {
            const response = await fetch(`${URLROOT}/calls/codes/${subcategoryId}`);
            if (!response.ok) throw new Error('Server error');
            const data = await response.json();
            this.updateSelectOptions(this.form.querySelector('#ticket_code_id'), data, 'Select a code...');
        } catch (error) {
            console.error('Error fetching codes:', error);
            showToast('Failed to load codes.', 'error');
        }
    },

    updateSelectOptions(selectElement, options, placeholder) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.id;
            opt.textContent = option.name;
            selectElement.appendChild(opt);
        });
        selectElement.disabled = options.length === 0;
    },

    validateForm() {
        const callStatus = this.form.querySelector('#selectedCallStatus').value;
        this.submitButton.disabled = !callStatus;
    },

    async submitForm(e) {
        e.preventDefault();
        this.submitButton.disabled = true;
        this.submitButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Saving...`;

        const formData = new FormData(this.form);

        try {
            const response = await fetch(`${URLROOT}/calls/record`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Server error');
            }

            if (result.success) {
                showToast('Call logged successfully. Fetching next driver...', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(result.message || 'An unexpected error occurred.');
            }
        } catch (error) {
            showToast(error.message, 'error');
            this.submitButton.disabled = false;
            this.submitButton.innerHTML = `<i class="fas fa-save mr-2"></i> Save Call`;
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    CallFormManager.init();
});
