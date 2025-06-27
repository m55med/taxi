function createTicketForm() {
    return {
        formData: {
            ticket_number: '',
            platform_id: '',
            phone: '',
            country_id: '',
            is_vip: false,
            category_id: '',
            subcategory_id: '',
            code_id: '',
            coupons: [], // Array to hold coupon objects {id, code, value}
            notes: ''
        },
        subcategories: [],
        codes: [],
        couponInput: '',
        availableCoupons: [],
        
        // Initialization
        init() {
            // Add event listener to release coupons if user leaves the page
            window.addEventListener('beforeunload', (event) => {
                if (this.formData.coupons.length > 0) {
                    // This will not guarantee execution, but it's the best we can do.
                    // A more robust solution would involve a cron job to clean up stale held coupons.
                    this.releaseAllCoupons();
                }
            });
        },

        // Methods for dependent dropdowns
        async fetchSubcategories() {
            this.formData.subcategory_id = '';
            this.formData.code_id = '';
            this.codes = [];
            if (!this.formData.category_id) {
                this.subcategories = [];
                return;
            }
            try {
                const response = await fetch(`${URLROOT}/create_ticket/getSubcategories/${this.formData.category_id}`);
                this.subcategories = await response.json();
            } catch (error) {
                console.error('Error fetching subcategories:', error);
                toastr.error('Failed to load subcategories.');
            }
        },

        async fetchCodes() {
            this.formData.code_id = '';
            if (!this.formData.subcategory_id) {
                this.codes = [];
                return;
            }
            try {
                const response = await fetch(`${URLROOT}/create_ticket/getCodes/${this.formData.subcategory_id}`);
                this.codes = await response.json();
            } catch (error) {
                console.error('Error fetching codes:', error);
                toastr.error('Failed to load codes.');
            }
        },

        // Method to check if ticket exists
        async checkTicketExists() {
            const ticketWarningDiv = document.getElementById('ticket-exists-warning');
            const viewTicketLink = document.getElementById('view-ticket-link');
            if (!this.formData.ticket_number) {
                ticketWarningDiv.classList.add('hidden');
                return;
            }
            try {
                const response = await fetch(`${URLROOT}/create_ticket/checkTicketExists/${this.formData.ticket_number}`);
                const data = await response.json();
                if (data.exists) {
                    ticketWarningDiv.classList.remove('hidden');
                    // Assuming a route structure like /tickets/view/{id}
                    viewTicketLink.href = `${URLROOT}/tickets/view/${data.ticket_id}`; 
                } else {
                    ticketWarningDiv.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error checking ticket:', error);
            }
        },
        
        async countryChanged() {
            // When country changes, release all coupons as they are country-specific
            if (this.formData.coupons.length > 0) {
                this.releaseAllCoupons();
                this.formData.coupons = [];
                toastr.info('Coupons cleared because country was changed.');
            }
            await this.fetchAvailableCoupons();
        },

        async fetchAvailableCoupons() {
            this.availableCoupons = [];
            if (!this.formData.country_id) return;

            try {
                const response = await fetch(`${URLROOT}/create_ticket/getAvailableCoupons/${this.formData.country_id}`);
                if (!response.ok) throw new Error('Server error');
                this.availableCoupons = await response.json();
            } catch (error) {
                console.error('Error fetching available coupons:', error);
                toastr.error('Could not load coupons for the selected country.');
            }
        },

        // Coupon Management
        async addSelectedCoupon() {
            if (!this.couponInput) return;

            const couponId = this.couponInput;
            
            // Check if coupon already added
            if (this.formData.coupons.some(c => c.id == couponId)) {
                toastr.warning('This coupon has already been added.');
                return;
            }

            try {
                const response = await fetch(`${URLROOT}/create_ticket/holdCoupon`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ coupon_id: couponId })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.formData.coupons.push(result.coupon);
                    // Remove the added coupon from the available list
                    this.availableCoupons = this.availableCoupons.filter(c => c.id != couponId);
                    this.couponInput = ''; // Reset dropdown
                    toastr.success('Coupon added and reserved for 5 minutes.');
                } else {
                    toastr.error(result.message || 'Coupon could not be added.');
                    // If it failed (e.g., another user just took it), refresh the list
                    await this.fetchAvailableCoupons();
                }
            } catch (error) {
                console.error('Error adding coupon:', error);
                toastr.error('An error occurred while adding the coupon.');
            }
        },

        async removeCoupon(index) {
            const coupon = this.formData.coupons[index];
            try {
                 await fetch(`${URLROOT}/create_ticket/releaseCoupon`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ coupon_id: coupon.id })
                });
                this.formData.coupons.splice(index, 1);
                // Since the coupon is now free, add it back to the available list if it belongs to the current country
                this.fetchAvailableCoupons();
                toastr.info(`Coupon ${coupon.code} has been released.`);
            } catch (error) {
                console.error('Error removing coupon:', error);
                toastr.error('An error occurred while removing the coupon.');
            }
        },
        
        async releaseAllCoupons() {
             for (const coupon of this.formData.coupons) {
                try {
                    await fetch(`${URLROOT}/create_ticket/releaseCoupon`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ coupon_id: coupon.id })
                    });
                } catch (error) {
                    console.error(`Error releasing coupon ${coupon.code}:`, error);
                }
            }
        },
        
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                toastr.success(`'${text}' copied to clipboard.`);
            }, (err) => {
                toastr.error('Failed to copy text.');
                console.error('Could not copy text: ', err);
            });
        },

        // Form Submission
        async submitForm() {
            const couponIds = this.formData.coupons.map(c => c.id);
            const payload = { ...this.formData, coupons: couponIds };
            
            try {
                const response = await fetch(`${URLROOT}/create_ticket/store`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();

                if (response.ok && result.success) {
                    toastr.success(result.message);
                    // Reset form
                    this.formData = {
                        ticket_number: '', platform_id: '', phone: '', country_id: '', is_vip: false,
                        category_id: '', subcategory_id: '', code_id: '', coupons: [], notes: ''
                    };
                    document.getElementById('ticket-exists-warning').classList.add('hidden');
                } else {
                    toastr.error(result.message || 'Failed to create ticket.');
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                toastr.error('An unexpected error occurred.');
            }
        }
    };
} 