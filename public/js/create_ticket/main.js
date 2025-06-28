function createTicketForm(platforms, marketers) {
    return {
        formData: {
            ticket_number: '',
            platform_id: '',
            phone: '',
            country_id: '',
            is_vip: false,
            marketer_id: '',
            category_id: '',
            subcategory_id: '',
            code_id: '',
            coupons: [], // Array to hold coupon objects {id, code, value}
            notes: ''
        },
        initialFormData: {}, // To store the initial state for resetting
        platforms: platforms || [],
        marketers: marketers || [],
        subcategories: [],
        codes: [],
        couponInput: '',
        availableCoupons: [],
        isSubmitting: false,
        couponsLoading: false,
        isVipModalOpen: false,
        
        // Initialization
        init() {
            // Store the initial state of the form data
            this.initialFormData = JSON.parse(JSON.stringify(this.formData));

            // Watch for changes in platform_id to set VIP status and open modal
            this.$watch('formData.platform_id', (newValue, oldValue) => {
                // Do not run on initial page load
                if (oldValue === undefined) {
                    return;
                }

                const selectedPlatform = this.platforms.find(p => p.id == newValue);

                if (selectedPlatform && selectedPlatform.name.toLowerCase().includes('vip')) {
                    this.formData.is_vip = true;
                    this.$nextTick(() => {
                        this.isVipModalOpen = true; 
                    });
                } else {
                    this.formData.is_vip = false;
                    this.formData.marketer_id = '';
                    const marketerSelectEl = document.querySelector('[data-model-name="marketer_id"]');
                    if (marketerSelectEl && marketerSelectEl._x_dataStack) {
                         marketerSelectEl._x_dataStack[0].reset();
                    }
                }
            });

            // Watch for changes in marketer_id to close the modal
            this.$watch('formData.marketer_id', (newValue) => {
                if (newValue) {
                    this.isVipModalOpen = false;
                    const selectedMarketer = this.marketers.find(m => m.id == newValue);
                    if (selectedMarketer) {
                        toastr.info(`Assigned to marketer: ${selectedMarketer.username}`);
                    }
                }
            });

            // Watch for changes in country_id to fetch new coupons
            this.$watch('formData.country_id', async (newValue, oldValue) => {
                if (newValue !== oldValue) {
                    if (this.formData.coupons.length > 0) {
                        await this.releaseAllCoupons();
                        this.formData.coupons = [];
                        toastr.info('Coupons cleared because country was changed.');
                    }
                    await this.fetchAvailableCoupons();
                }
            });

            // Watch for changes in category_id to fetch subcategories
            this.$watch('formData.category_id', async (newValue, oldValue) => {
                if (newValue !== oldValue) {
                    // Reset child data arrays to force re-initialization of components
                    this.subcategories = [];
                    this.codes = [];
                    // Reset the model values
                    this.formData.subcategory_id = '';
                    this.formData.code_id = '';
                    
                    if (newValue) {
                        await this.fetchSubcategories();
                    }
                }
            });

            // Watch for changes in subcategory_id to fetch codes
            this.$watch('formData.subcategory_id', async (newValue, oldValue) => {
                if (newValue !== oldValue) {
                     // Reset child data array
                    this.codes = [];
                    // Reset the model value
                    this.formData.code_id = '';

                    if (newValue) {
                        await this.fetchCodes();
                    }
                }
            });

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
        
        async fetchAvailableCoupons() {
            this.availableCoupons = [];
            if (!this.formData.country_id) return;

            this.couponsLoading = true;
            try {
                const response = await fetch(`${URLROOT}/create_ticket/getAvailableCoupons/${this.formData.country_id}`);
                if (!response.ok) throw new Error('Server error');
                this.availableCoupons = await response.json();
            } catch (error) {
                toastr.error('Could not load coupons for the selected country.');
            } finally {
                this.couponsLoading = false;
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

        resetForm() {
            this.releaseAllCoupons(); // Release any held coupons
            this.formData = JSON.parse(JSON.stringify(this.initialFormData)); // Deep copy to reset
            this.subcategories = [];
            this.codes = [];
            this.couponInput = '';
            this.availableCoupons = [];
            document.getElementById('ticket-exists-warning').classList.add('hidden');

            // Manually dispatch events to reset searchable selects
            const selects = document.querySelectorAll('[data-model-name]');
            selects.forEach(select => {
                if (select && select._x_dataStack) {
                    select._x_dataStack[0].reset();
                }
            });

            toastr.info('Form has been reset.');
        },

        // Form Submission
        async submitForm() {
            this.isSubmitting = true;
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
                    toastr.success(result.message || 'Ticket created successfully!');
                    this.resetForm();
                } else {
                    toastr.error(result.message || 'Failed to create ticket.');
                }
            } catch (error) {
                toastr.error('An unexpected error occurred.');
            } finally {
                this.isSubmitting = false;
            }
        }
    };
} 