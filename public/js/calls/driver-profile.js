console.log("✅ driver-profile.js loaded");
// تعريف الدالة كـ global على الـ window object
window.copyToClipboard = function (text) {
    if (!navigator.clipboard) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showToast('تم نسخ الرقم بنجاح', 'success');
        } catch (err) {
            showToast('فشل نسخ الرقم', 'error');
        }
        document.body.removeChild(textArea);
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        showToast('تم نسخ الرقم بنجاح', 'success');
    }).catch(err => {
        showToast('فشل نسخ الرقم', 'error');
    });
};

