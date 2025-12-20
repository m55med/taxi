// API utility for CS Taxif Extension

const API = {
    baseURL: 'https://cs.taxif.com/api/extension',

    // Generic API call handler
    async makeRequest(endpoint, options = {}) {
        const token = await Storage.getToken();

        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (token) {
            headers['X-EXT-TOKEN'] = token;
        }

        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                ...options,
                headers
            });

            // Read body once to avoid "body stream already read" error
            const text = await response.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch (e) {
                // Response is not JSON
                if (!response.ok) {
                    if (response.status === 401) {
                        await Storage.removeToken();
                        throw new Error('TOKEN_EXPIRED');
                    }
                    throw new Error(text || `HTTP ${response.status}`);
                }
            }

            // Check for auth errors in JSON data or 401 status
            if (response.status === 401 ||
                (data && (data.error === 'Token is required' ||
                    data.error === 'Invalid token' ||
                    data.error === 'Invalid or expired token'))) {

                await Storage.removeToken();
                throw new Error('TOKEN_EXPIRED');
            }

            if (!response.ok) {
                throw new Error(data?.error || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // Validate token and get user info
    async validateToken(token) {
        try {
            // Temporarily use the provided token
            const savedToken = await Storage.getToken();
            await Storage.setToken(token);

            const data = await this.makeRequest('/me');

            // Token is valid, keep it
            if (Array.isArray(data) && data.length > 0) {
                return data[0]; // Returns user object from array
            } else if (data && typeof data === 'object' && data.id) {
                return data; // Returns user object directly
            } else {
                // Invalid response format
                await Storage.setToken(savedToken); // Restore old token
                throw new Error('Invalid response format');
            }
        } catch (error) {
            // Restore old token on error
            throw error;
        }
    },

    // Get ticket creation options
    async getTicketOptions() {
        try {
            const data = await this.makeRequest('/options');

            // API returns object with success field, not array
            if (data && data.success && data.data) {
                return data.data;
            } else {
                throw new Error('Failed to fetch options');
            }
        } catch (error) {
            console.error('Error fetching options:', error);
            throw error;
        }
    },

    // Get ticket details
    async getTicketDetails(ticketNumber) {
        try {
            // Use ticket number as is, without padding
            const cleanNumber = ticketNumber.trim().replace(/\D/g, '');

            const data = await this.makeRequest(`/tickets/${cleanNumber}/details`);

            // Check if response is an array (new format) or object (old format)
            if (Array.isArray(data)) {
                const result = data[0];
                if (result) {
                    // If success is true OR if helper data exists, return it
                    if (result.success || result.helper) {
                        return result;
                    }
                }
            } else if (data) {
                // API returns object with success field
                if ((data.success && data.ticket_detail) || data.helper) {
                    return data; // Return the whole response object
                }
            }

            throw new Error('Ticket not found');
        } catch (error) {
            console.error('Error fetching ticket details:', error);
            throw error;
        }
    },

    // Create new ticket
    async createTicket(ticketData) {
        try {
            const data = await this.makeRequest('/tickets/create', {
                method: 'POST',
                body: JSON.stringify(ticketData)
            });

            return data;
        } catch (error) {
            console.error('Error creating ticket:', error);
            throw error;
        }
    },

    // Submit report (bug or suggestion)
    async submitReport(reportData) {
        try {
            const data = await this.makeRequest('/report', {
                method: 'POST',
                body: JSON.stringify(reportData)
            });

            return data;
        } catch (error) {
            console.error('Error submitting report:', error);
            throw error;
        }
    }
};

// Make available globally for content scripts
if (typeof window !== 'undefined') {
    window.API = API;
}
