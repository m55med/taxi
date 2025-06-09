document.addEventListener('DOMContentLoaded', function() {
    // --- Configuration ---
    const config = {
        formId: 'call-form',
        recordEndpoint: BASE_PATH + '/call/record',
        historyEndpoint: BASE_PATH + '/call/history',
        releaseHoldEndpoint: BASE_PATH + '/call/release-hold',
        basePath: typeof BASE_PATH !== 'undefined' ? BASE_PATH : ''
    };

    // --- Element Cache ---
    const elements = {
        form: document.getElementById(config.formId),
        callStatus: document.getElementById('call-status'),
        notes: document.getElementById('call-notes'),
        nextCallDate: document.getElementById('next-call-date'),
        nextCallTime: document.getElementById('next-call-time')
    };

    // --- Event Listeners ---
    if (elements.form) {
        elements.form.addEventListener('submit', handleCallFormSubmission);
    }

    if (elements.callStatus) {
        elements.callStatus.addEventListener('change', handleCallStatusChange);
    }

    // Handle page unload to release driver hold
    window.addEventListener('beforeunload', function() {
        navigator.sendBeacon(config.releaseHoldEndpoint);
    });

    // --- Function Definitions ---
    function handleCallStatusChange() {
        const status = elements.callStatus.value;
        const nextCallContainer = document.getElementById('next-call-container');
        
        if (status === 'rescheduled') {
            nextCallContainer.style.display = 'block';
        } else {
            nextCallContainer.style.display = 'none';
        }
    }

    async function handleCallFormSubmission(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        const buttonText = submitButton.querySelector('span');
        const originalText = buttonText.textContent;

        const formData = new FormData(this);
        
        // إذا تم تحديد موعد المكالمة القادمة
        if (elements.nextCallDate && elements.nextCallTime) {
            const nextCallAt = elements.nextCallDate.value + ' ' + elements.nextCallTime.value;
            formData.append('next_call_at', nextCallAt);
        }

        submitButton.disabled = true;
        buttonText.textContent = 'جاري الحفظ...';

        try {
            const response = await fetch(config.recordEndpoint, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification('تم تسجيل المكالمة بنجاح.', true);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message || 'فشل غير متوقع في تسجيل المكالمة.');
            }
        } catch (error) {
            console.error('Call recording error:', error);
            showNotification(`خطأ: ${error.message}`, false);
        } finally {
            submitButton.disabled = false;
            buttonText.textContent = originalText;
        }
    }
}); 