// Call Form Module
const CallFormModule = {
    init() {
        this.callForm = document.getElementById('callForm');
        if (!this.callForm) return;

        this.statusButtons = this.callForm.querySelectorAll('.call-status-btn');
        this.selectedCallStatus = this.callForm.querySelector('#selectedCallStatus');
        this.notesTextarea = this.callForm.querySelector('textarea[name="notes"]');
        this.nextCallSection = this.callForm.querySelector('#nextCallSection');
        this.nextCallInput = this.callForm.querySelector('input[name="next_call_at"]');
        
        this.initializeButtons();
        this.initializeFormValidation();
    },

    initializeButtons() {
        this.statusButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleButtonClick(button);
            });
        });
    },

    handleButtonClick(button) {
        // Clear previous selection
        this.statusButtons.forEach(btn => {
            btn.classList.remove('selected-status', 'border-green-500', 'border-red-500', 'border-yellow-500', 'border-blue-500', 'border-orange-500', 'border-purple-500');
            btn.classList.add('border-gray-200');
        });

        // Apply new selection
        button.classList.add('selected-status');
        const status = button.dataset.status;
        this.selectedCallStatus.value = status;
        
        const colorMap = {
            'answered': 'green', 'no_answer': 'red', 'busy': 'yellow',
            'not_available': 'blue', 'wrong_number': 'orange', 'rescheduled': 'purple'
        };
        const color = colorMap[status] || 'gray';
        button.classList.remove('border-gray-200');
        button.classList.add(`border-${color}-500`);

        // Handle visibility of next call section
        if (['no_answer', 'busy', 'not_available', 'rescheduled'].includes(status)) {
            this.nextCallSection.classList.remove('hidden');
            if (status !== 'rescheduled') {
                const hoursMap = { 'no_answer': 1, 'busy': 0.5, 'not_available': 3 };
                this.setDefaultNextCallTime(hoursMap[status]);
            }
        } else {
            this.nextCallSection.classList.add('hidden');
            this.nextCallInput.value = '';
        }
    },

    setDefaultNextCallTime(hours) {
        if (!this.nextCallInput) return;
        const now = new Date();
        now.setTime(now.getTime() + (hours * 60 * 60 * 1000));
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hour = String(now.getHours()).padStart(2, '0');
        const minute = String(now.getMinutes()).padStart(2, '0');
        this.nextCallInput.value = `${year}-${month}-${day}T${hour}:${minute}`;
    },

    initializeFormValidation() {
        this.callForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = this.callForm.querySelector('button[type="submit"]');

            try {
                if (!this.selectedCallStatus.value) {
                    showToast('Please select a call outcome.', 'error');
                    return;
                }

                if (!this.nextCallSection.classList.contains('hidden') && !this.nextCallInput.value) {
                    showToast('Please specify the next call time.', 'error');
                    this.nextCallInput.focus();
                    return;
                }

                submitButton.disabled = true;
                const formData = new FormData(this.callForm);
                
                const response = await fetch(`${URLROOT}/calls/record`, {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                if (!responseText) {
                    alert("The server returned an empty response. Please check the PHP error logs.");
                    throw new Error("Empty server response.");
                }

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (err) {
                    console.error("Failed to parse JSON:", responseText);
                    alert("Failed to parse server response as JSON. Full response in console.");
                    throw new Error("Invalid JSON response from server.");
                }

                if (!response.ok) {
                    throw new Error(result.message || `Server error: ${response.statusText}`);
                }

                if (result.success) {
                    showToast('Call logged successfully. Fetching next driver...', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(result.message || 'An unexpected error occurred while logging the call.');
                }

            } catch (error) {
                console.error('Error submitting call form:', error);
                showToast(error.message || 'A technical error occurred.', 'error');
            } finally {
                if(submitButton) submitButton.disabled = false;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => CallFormModule.init());
