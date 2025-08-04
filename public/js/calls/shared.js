console.log("✅ shared.js loaded");

// Notification functions
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) {
        console.error('Notification element not found');
        return;
    }

    const notificationMessage = document.getElementById('notificationMessage');
    const notificationIcon = document.getElementById('notificationIcon');

    if (!notificationMessage || !notificationIcon) {
        console.error('Notification child elements not found');
        return;
    }

    notificationMessage.textContent = message;

    // Reset classes and set icon based on type
    notification.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg z-50 animated';
    if (type === 'success') {
        notificationIcon.className = 'fas fa-check-circle text-green-500 text-xl';
    } else { // 'error'
        notificationIcon.className = 'fas fa-exclamation-circle text-red-500 text-xl';
    }

    notification.classList.add('fadeIn');
    notification.classList.remove('hidden');

    // Auto hide after 3 seconds with fade out animation
    setTimeout(() => {
        notification.classList.add('fadeOut');
        setTimeout(() => {
            notification.classList.add('hidden');
            notification.classList.remove('fadeIn', 'fadeOut');
        }, 300); // Wait for fadeOut animation to finish
    }, 3000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.classList.add('hidden');
    }
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    .animated {
        animation-duration: 300ms;
        animation-fill-mode: both;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -20px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }
    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, 0); }
        to { opacity: 0; transform: translate(-50%, -20px); }
    }
    .fadeIn { animation-name: fadeIn; }
    .fadeOut { animation-name: fadeOut; }
`;
document.head.appendChild(style);

// Loading Overlay Functions
function showLoading() {
    // Avoid creating multiple overlays
    if (document.getElementById('loadingOverlay')) return;

    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
    overlay.innerHTML = `
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent ml-3"></div>
            <span class="text-gray-700">جاري التحميل...</span>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Export functions to window object for global access
window.showNotification = showNotification;
window.hideNotification = hideNotification;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
