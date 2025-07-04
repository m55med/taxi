// Driver Profile Module

function copyToClipboard(text) {
    if (!navigator.clipboard) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed'; // Prevent scrolling to bottom of page in MS Edge.
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
}

document.addEventListener('DOMContentLoaded', function() {
    // Any additional driver profile specific functionality can be added here
}); 