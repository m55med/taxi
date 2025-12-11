// Storage utility for managing chrome.storage.local

const Storage = {
    // Token management
    async setToken(token) {
        return new Promise((resolve) => {
            chrome.storage.local.set({ token }, () => {
                console.log('Token saved successfully');
                resolve();
            });
        });
    },

    async getToken() {
        return new Promise((resolve) => {
            chrome.storage.local.get(['token'], (result) => {
                resolve(result.token || null);
            });
        });
    },

    async removeToken() {
        return new Promise((resolve) => {
            chrome.storage.local.remove(['token', 'user'], () => {
                console.log('Token and user data removed');
                resolve();
            });
        });
    },

    // User data management
    async setUser(userData) {
        return new Promise((resolve) => {
            chrome.storage.local.set({ user: userData }, () => {
                console.log('User data saved');
                resolve();
            });
        });
    },

    async getUser() {
        return new Promise((resolve) => {
            chrome.storage.local.get(['user'], (result) => {
                resolve(result.user || null);
            });
        });
    },

    // Options/variables cache
    async setOptions(options) {
        return new Promise((resolve) => {
            chrome.storage.local.set({
                options,
                optionsTimestamp: new Date().toISOString()
            }, () => {
                console.log('Options cached successfully');
                resolve();
            });
        });
    },

    async getOptions() {
        return new Promise((resolve) => {
            chrome.storage.local.get(['options'], (result) => {
                resolve(result.options || null);
            });
        });
    },

    // Clear all data
    async clearAll() {
        return new Promise((resolve) => {
            chrome.storage.local.clear(() => {
                console.log('All storage cleared');
                resolve();
            });
        });
    }
};

// Make available globally for content scripts
if (typeof window !== 'undefined') {
    window.Storage = Storage;
}
