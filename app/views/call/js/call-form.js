// Call Form Module
const CallFormModule = {
    init() {
        this.callForm = document.getElementById('callForm');
        this.statusButtons = document.querySelectorAll('.call-status-btn');
        this.selectedCallStatus = document.getElementById('selectedCallStatus');
        this.notesTextarea = document.querySelector('textarea[name="notes"]');
        this.nextCallSection = document.getElementById('nextCallSection');
        this.nextCallInput = document.querySelector('input[name="next_call_at"]');
        
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
                'border-blue-500', 'text-blue-700',
                'shadow-lg'
            );
            icon.classList.add('border-gray-300', 'text-gray-700');
            
            text.classList.remove(
                'text-green-700',
                'text-red-700',
                'text-yellow-700',
                'text-orange-700',
                'text-gray-700',
                'text-blue-700'
            );
            text.classList.add('text-gray-600');
        });

        // تطبيق الأنماط على الزر المحدد
        if (selectedButton) {
            const icon = selectedButton.querySelector('div');
            const text = selectedButton.querySelector('span');
            const status = selectedButton.dataset.status;

            // تحديث حقل الحالة المخفي
            this.selectedCallStatus.value = status;

            switch(status) {
                case 'answered':
                    icon.classList.add('border-green-500', 'text-green-700', 'shadow-lg');
                    text.classList.add('text-green-700');
                    this.nextCallSection.classList.add('hidden');
                    break;
                case 'no_answer':
                    icon.classList.add('border-red-500', 'text-red-700', 'shadow-lg');
                    text.classList.add('text-red-700');
                    this.nextCallSection.classList.remove('hidden');
                    // تعيين موعد افتراضي بعد ساعة
                    this.setDefaultNextCallTime(1);
                    break;
                case 'busy':
                    icon.classList.add('border-yellow-500', 'text-yellow-700', 'shadow-lg');
                    text.classList.add('text-yellow-700');
                    this.nextCallSection.classList.remove('hidden');
                    // تعيين موعد افتراضي بعد 30 دقيقة
                    this.setDefaultNextCallTime(0.5);
                    break;
                case 'not_available':
                    icon.classList.add('border-orange-500', 'text-orange-700', 'shadow-lg');
                    text.classList.add('text-orange-700');
                    this.nextCallSection.classList.remove('hidden');
                    // تعيين موعد افتراضي بعد 3 ساعات
                    this.setDefaultNextCallTime(3);
                    break;
                case 'wrong_number':
                    icon.classList.add('border-gray-500', 'text-gray-700', 'shadow-lg');
                    text.classList.add('text-gray-700');
                    this.nextCallSection.classList.add('hidden');
                    break;
                case 'rescheduled':
                    icon.classList.add('border-blue-500', 'text-blue-700', 'shadow-lg');
                    text.classList.add('text-blue-700');
                    this.nextCallSection.classList.remove('hidden');
                    break;
            }
        } else {
            // إذا لم يتم تحديد أي زر، إعادة تعيين حقل الحالة
            this.selectedCallStatus.value = '';
            this.nextCallSection.classList.add('hidden');
        }
    },

    setDefaultNextCallTime(hours) {
        if (this.nextCallInput) {
            const now = new Date();
            now.setHours(now.getHours() + hours);
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hour = String(now.getHours()).padStart(2, '0');
            const minute = String(now.getMinutes()).padStart(2, '0');
            this.nextCallInput.value = `${year}-${month}-${day}T${hour}:${minute}`;
        }
    },

    initializeFormValidation() {
        this.callForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = this.callForm.querySelector('button[type="submit"]');

            try {
                // التحقق من اختيار حالة المكالمة
                if (!this.selectedCallStatus.value) {
                    showToast('الرجاء اختيار نتيجة المكالمة', 'error');
                    return;
                }

                // التحقق من إدخال الملاحظات - تم جعله اختيارياً
                /* if (!this.notesTextarea.value.trim()) {
                    showToast('الرجاء إدخال ملاحظات المكالمة', 'error');
                    this.notesTextarea.focus();
                    return;
                } */

                // التحقق من موعد المكالمة التالية إذا كان مطلوباً
                if (!this.nextCallSection.classList.contains('hidden') && !this.nextCallInput.value) {
                    showToast('الرجاء تحديد موعد المكالمة التالية', 'error');
                    this.nextCallInput.focus();
                    return;
                }

                submitButton.disabled = true;
                const formData = new FormData(this.callForm);
                
                const response = await fetch(BASE_PATH + '/call/record', {
                    method: 'POST',
                    body: formData
                });

                // --- DEBUG: عرض الاستجابة الأولية من الخادم ---
                const responseText = await response.text();
                if (!responseText) {
                    alert("الخادم أعاد استجابة فارغة. الرجاء التحقق من سجل أخطاء PHP (error logs).");
                    throw new Error("Empty server response.");
                }

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error("Failed to parse JSON:", responseText);
                    alert("فشل تحليل استجابة الخادم كـ JSON. الاستجابة الكاملة في الكونسول.");
                    throw new Error("Invalid JSON response from server.");
                }
                // --- نهاية DEBUG ---

                if (!response.ok) { // Status not in 200-299 range
                    throw new Error(result.message || `Server error: ${response.statusText}`);
                }

                if (result.success) {
                    showToast('تم تسجيل المكالمة بنجاح، جاري جلب السائق التالي...', 'success');
                    setTimeout(() => {
                        window.location.href = BASE_PATH + '/call';
                    }, 1500);
                } else {
                    throw new Error(result.message || 'حدث خطأ غير متوقع أثناء تسجيل المكالمة.');
                }

            } catch (error) {
                console.error('Error submitting call form:', error);
                showToast(error.message || 'حدث خطأ فني.', 'error');
            } finally {
                if(submitButton) submitButton.disabled = false;
            }
        });
    }
};

// تهيئة النموذج عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => CallFormModule.init()); 