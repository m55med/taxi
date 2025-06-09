// Call Form Module
const CallFormModule = {
    init() {
        this.callForm = document.getElementById('callForm');
        this.statusButtons = document.querySelectorAll('.call-status-btn');
        this.selectedCallStatus = document.getElementById('selectedCallStatus');
        this.notesTextarea = document.querySelector('textarea[name="notes"]');
        this.nextCallSection = document.getElementById('nextCallSection');
        
        if (this.callForm) {
            this.initializeButtons();
            this.initializeFormValidation();
        }
    },

    initializeButtons() {
        // حالة الأزرار
        this.currentSelectedButton = null;

        // إضافة مستمعي الأحداث للأزرار
        this.statusButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleButtonClick(button);
            });
        });

        // الحفاظ على حالة الأزرار عند التفاعل مع النموذج
        const formFields = this.callForm.querySelectorAll('input, textarea, select');
        formFields.forEach(field => {
            field.addEventListener('focus', () => this.updateButtonStates(this.currentSelectedButton));
            field.addEventListener('blur', () => this.updateButtonStates(this.currentSelectedButton));
        });
    },

    handleButtonClick(button) {
        // تحديث الزر المحدد
        this.currentSelectedButton = (this.currentSelectedButton === button) ? null : button;
        this.updateButtonStates(this.currentSelectedButton);
    },

    updateButtonStates(selectedButton) {
        // إعادة تعيين جميع الأزرار
        this.statusButtons.forEach(button => {
            const icon = button.querySelector('div');
            const text = button.querySelector('span');
            
            // إعادة تعيين الأنماط
            icon.classList.remove(
                'border-green-500', 'text-green-700',
                'border-red-500', 'text-red-700',
                'border-yellow-500', 'text-yellow-700',
                'border-orange-500', 'text-orange-700',
                'border-gray-500', 'text-gray-700',
                'shadow-lg'
            );
            icon.classList.add('border-gray-300', 'text-gray-700');
            
            text.classList.remove(
                'text-green-700',
                'text-red-700',
                'text-yellow-700',
                'text-orange-700',
                'text-gray-700'
            );
            text.classList.add('text-gray-600');
        });

        // تطبيق الأنماط على الزر المحدد
        if (selectedButton) {
            const icon = selectedButton.querySelector('div');
            const text = selectedButton.querySelector('span');
            const status = selectedButton.dataset.status;

            switch(status) {
                case 'answered':
                    icon.classList.add('border-green-500', 'text-green-700', 'shadow-lg');
                    text.classList.add('text-green-700');
                    break;
                case 'no_answer':
                    icon.classList.add('border-red-500', 'text-red-700', 'shadow-lg');
                    text.classList.add('text-red-700');
                    break;
                case 'busy':
                    icon.classList.add('border-yellow-500', 'text-yellow-700', 'shadow-lg');
                    text.classList.add('text-yellow-700');
                    break;
                case 'not_available':
                    icon.classList.add('border-orange-500', 'text-orange-700', 'shadow-lg');
                    text.classList.add('text-orange-700');
                    break;
                case 'wrong_number':
                    icon.classList.add('border-gray-500', 'text-gray-700', 'shadow-lg');
                    text.classList.add('text-gray-700');
                    break;
            }

            // تحديث حقل الحالة المخفي
            this.selectedCallStatus.value = status;

            // إظهار/إخفاء قسم المكالمة التالية
            if (['no_answer', 'busy', 'not_available'].includes(status)) {
                this.nextCallSection.classList.remove('hidden');
            } else {
                this.nextCallSection.classList.add('hidden');
            }
        } else {
            // إذا لم يتم تحديد أي زر، إعادة تعيين حقل الحالة
            this.selectedCallStatus.value = '';
            this.nextCallSection.classList.add('hidden');
        }
    },

    initializeFormValidation() {
        this.callForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // التحقق من اختيار حالة المكالمة
            if (!this.selectedCallStatus.value) {
                showNotification('الرجاء اختيار نتيجة المكالمة', 'error');
                return;
            }

            // التحقق من إدخال الملاحظات
            if (!this.notesTextarea.value.trim()) {
                showNotification('الرجاء إدخال ملاحظات المكالمة', 'error');
                this.notesTextarea.focus();
                return;
            }

            // إرسال النموذج
            const formData = new FormData(this.callForm);
            this.submitForm(formData);
        });
    },

    async submitForm(formData) {
        const submitButton = this.callForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        try {
            const response = await fetch(BASE_PATH + '/call/record', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 401 && result.redirect) {
                    showNotification(result.message, 'error');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    throw new Error(result.message || `An error occurred: ${response.statusText}`);
                }
                return;
            }

            if (result.success) {
                showNotification('تم تسجيل المكالمة بنجاح', 'success');
                setTimeout(() => {
                    window.location.href = BASE_PATH + '/call';
                }, 500);
            } else {
                throw new Error(result.message || 'حدث خطأ في تسجيل المكالمة');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        } finally {
            submitButton.disabled = false;
        }
    }
};

// تهيئة النموذج عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => CallFormModule.init()); 