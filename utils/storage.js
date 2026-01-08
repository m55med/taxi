// Storage utility for managing chrome.storage.local

const Storage = {
    // Token management
    async setToken(token) {
        return new Promise((resolve, reject) => {
            chrome.storage.local.set({ token }, () => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    console.log('Token saved successfully');
                    resolve();
                }
            });
        });
    },

    async getToken() {
        return new Promise((resolve, reject) => {
            chrome.storage.local.get(['token'], (result) => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    resolve(result.token || null);
                }
            });
        });
    },

    async removeToken() {
        return new Promise((resolve, reject) => {
            chrome.storage.local.remove(['token', 'user'], () => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    console.log('Token and user data removed');
                    resolve();
                }
            });
        });
    },

    // User data management
    async setUser(userData) {
        return new Promise((resolve, reject) => {
            chrome.storage.local.set({ user: userData }, () => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    console.log('User data saved');
                    resolve();
                }
            });
        });
    },

    async getUser() {
        return new Promise((resolve, reject) => {
            chrome.storage.local.get(['user'], (result) => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    resolve(result.user || null);
                }
            });
        });
    },

    // Options/variables cache
    async setOptions(options) {
        return new Promise((resolve, reject) => {
            chrome.storage.local.set({
                options,
                optionsTimestamp: new Date().toISOString()
            }, () => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    console.log('Options cached successfully');
                    resolve();
                }
            });
        });
    },

    async getOptions() {
        return new Promise((resolve, reject) => {
            chrome.storage.local.get(['options'], (result) => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    resolve(result.options || null);
                }
            });
        });
    },

    // Clear all data
    async clearAll() {
        return new Promise((resolve, reject) => {
            chrome.storage.local.clear(() => {
                if (chrome.runtime.lastError) {
                    reject(new Error(chrome.runtime.lastError.message));
                } else {
                    console.log('All storage cleared');
                    resolve();
                }
            });
        });
    }
};

// Make available globally for content scripts (only in extension context)
if (typeof window !== 'undefined' && typeof chrome !== 'undefined' && chrome.runtime) {
    // Only expose in secure extension context
    window.Storage = Storage;
}
