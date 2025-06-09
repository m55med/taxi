const TransferModule = {
    init() {
        this.modal = document.getElementById('transferModal');
        this.form = document.getElementById('transferForm');

        if (this.modal) {
            this.initializeModal();
        }

        if (this.form) {
            this.initializeForm();
        }
    },

    initializeModal() {
        // Show modal function
        window.showTransferModal = () => {
            this.modal.classList.remove('hidden');
        };

        // Hide modal function
        window.hideTransferModal = () => {
            this.modal.classList.add('hidden');
            // Reset form when modal is closed
            if (this.form) {
                this.form.reset();
            }
        };

        // Close modal when clicking outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                window.hideTransferModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                window.hideTransferModal();
            }
        });
    },

    async initializeForm() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(this.form);
            const submitButton = this.form.querySelector('button[type="submit"]');
            
            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري التحويل...';
            
            try {
                const response = await fetch(`${BASE_PATH}/call/assignments/assign`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                let data;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const errorText = await response.text();
                    console.error('Invalid server response:', errorText);
                    throw new Error('استجابة غير صالحة من الخادم. راجع الـ console لمزيد من التفاصيل.');
                }

                if (!response.ok) {
                    if (response.status === 401 && data.redirect) {
                        showNotification(data.message || 'انتهت صلاحية الجلسة', 'error');
                        setTimeout(() => window.location.href = data.redirect, 1500);
                        return;
                    }
                    throw new Error(data.message || `خطأ غير متوقع: ${response.statusText}`);
                }
                
                if (data.success) {
                    showNotification(data.message || 'تم تحويل السائق بنجاح', 'success');
                    window.hideTransferModal();
                    // Redirect after a short delay
                    setTimeout(() => window.location.href = `${BASE_PATH}/call`, 500);
                } else {
                    throw new Error(data.message || 'فشل في تحويل السائق');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message || 'حدث خطأ أثناء تحويل السائق', 'error');
            } finally {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = 'تأكيد التحويل';
            }
        });
    }
};

// Initialize module when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => TransferModule.init());
} else {
    TransferModule.init();
} 