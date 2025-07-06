function callFormAlpine() {
    return {
        formData: {
            driver_id: null,
            ticket_category_id: null,
            ticket_subcategory_id: null,
            ticket_code_id: null,
            call_status: null,
            notes: '',
            next_call_at: '',
        },
        subcategories: [],
        codes: [],
        subcategoriesLoading: false,
        codesLoading: false,
        isSubmitting: false,
        selectedStatus: null,
        
        init() {
            // Set driver_id from the hidden input
            const driverIdInput = document.querySelector('input[name="driver_id"]');
            if (driverIdInput) {
                this.formData.driver_id = driverIdInput.value;
            }

            this.$watch('formData.ticket_category_id', (categoryId) => {
                this.formData.ticket_subcategory_id = null;
                this.formData.ticket_code_id = null;
                this.subcategories = [];
                this.codes = [];

                if (categoryId) {
                    this.fetchSubcategories(categoryId);
                }
            });

            this.$watch('formData.ticket_subcategory_id', (subcategoryId) => {
                this.formData.ticket_code_id = null;
                this.codes = [];

                if (subcategoryId) {
                    this.fetchCodes(subcategoryId);
                }
            });
        },

        handleStatusClick(status) {
            this.selectedStatus = status;
            this.formData.call_status = status;
            
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
                this.formData.next_call_at = '';
                if(nextCallInput) nextCallInput.value = '';
            }
        },

        setDefaultNextCallTime(hours) {
            const nextCallInput = document.getElementById('nextCallTime');
            if (!nextCallInput) return;
            const now = new Date();
            now.setTime(now.getTime() + (hours * 60 * 60 * 1000));
            
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hour = String(now.getHours()).padStart(2, '0');
            const minute = String(now.getMinutes()).padStart(2, '0');
            
            const formattedDateTime = `${year}-${month}-${day}T${hour}:${minute}`;
            this.formData.next_call_at = formattedDateTime;
            nextCallInput.value = formattedDateTime;
        },

        async fetchSubcategories(categoryId) {
            this.subcategoriesLoading = true;
            try {
                const response = await fetch(`${URLROOT}/calls/subcategories/${categoryId}`);
                if (!response.ok) throw new Error('Server error');
                this.subcategories = await response.json();
                this.$dispatch('options-updated', { model: 'subcategories', data: this.subcategories });
            } catch (error) {
                console.error('Error fetching subcategories:', error);
                showToast('Failed to load subcategories.', 'error');
            } finally {
                this.subcategoriesLoading = false;
            }
        },

        async fetchCodes(subcategoryId) {
            this.codesLoading = true;
            try {
                const response = await fetch(`${URLROOT}/calls/codes/${subcategoryId}`);
                if (!response.ok) throw new Error('Server error');
                this.codes = await response.json();
                this.$dispatch('options-updated', { model: 'codes', data: this.codes });
            } catch (error) {
                console.error('Error fetching codes:', error);
                showToast('Failed to load codes.', 'error');
            } finally {
                this.codesLoading = false;
            }
        },

        async submitForm() {
            this.isSubmitting = true;

            // Get notes from textarea since it's not bound with x-model
            const notesTextarea = document.getElementById('callNotes');
            if (notesTextarea) this.formData.notes = notesTextarea.value;
            
            try {
                const formBody = new FormData();
                for (const key in this.formData) {
                    if (this.formData[key] !== null) {
                        formBody.append(key, this.formData[key]);
                    }
                }

                const response = await fetch(`${URLROOT}/calls/record`, {
                    method: 'POST',
                    body: formBody
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
                console.error('Error submitting call form:', error);
                showToast(error.message || 'A technical error occurred.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        }
    };
} 