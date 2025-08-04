console.log('✅ utils.js loaded');

/**
 * utils.js
 * Contains shared utility functions for the application.
 * - API helper for making fetch requests.
 * - Toast notification handler.
 */

// --- API Helper ---
const api = {
    get: async (endpoint, params = {}) => {
        const url = new URL(`${BASE_URL}/${endpoint}`);
        if (params) {
            Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        }
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            return await handleResponse(response);
        } catch (error) {
            return handleError(error);
        }
    },
    post: async (endpoint, body = {}) => {
        try {
            const response = await fetch(`${BASE_URL}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });
            return await handleResponse(response);
        } catch (error) {
            return handleError(error);
        }
    }
};

const handleResponse = async (response) => {
    const data = await response.json().catch(() => ({})); // Handle empty/invalid JSON
    return { ok: response.ok, status: response.status, data };
};

const handleError = (error) => {
    console.error('API Error:', error);
    showToast('حدث خطأ في الشبكة. يرجى المحاولة مرة أخرى.', 'error');
    return { ok: false, data: { message: error.message } };
};

// --- Toast Notifications ---
const showToast = (message, type = 'success') => {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';

    toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} flex items-center transition-transform transform translate-x-full`;
    toast.innerHTML = `<i class="fas ${icon} ml-3"></i> <p>${message}</p>`;
    container.appendChild(toast);

    // Animate in
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    // Animate out
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 5000);
};

function copyToClipboard(text, entityName = 'Text') {
    if (!navigator.clipboard) {
        showToast('Clipboard API not available.', 'error');
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        showToast(`${entityName} copied to clipboard!`);
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showToast('Failed to copy to clipboard.', 'error');
    });
}
