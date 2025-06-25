document.addEventListener('DOMContentLoaded', () => {
    // --- Main Application Object ---
    const App = {
        // --- DOM Elements ---
        form: document.getElementById('ticket-form'),
        countrySelect: document.getElementById('country_id'),
        categorySelect: document.getElementById('category_id'),
        subcategorySelect: document.getElementById('subcategory_id'),
        codeSelect: document.getElementById('code_id'),
        ticketNumberInput: document.getElementById('ticket_number'),
        couponsContainer: document.getElementById('coupons-container'),
        addCouponBtn: document.getElementById('add-coupon-btn'),
        couponsSection: document.getElementById('coupons-section'),
        teamLeaderSelect: document.getElementById('assigned_team_leader_id'),
        platformSelect: document.getElementById('platform_id'),
        phoneInput: document.getElementById('phone'),
        isVipCheckbox: document.getElementById('is_vip'),
        notesTextarea: document.getElementById('notes'),
        pastePhoneBtn: document.getElementById('paste-phone-number'),
        pasteTicketBtn: document.getElementById('paste-ticket-number'),
        submitBtn: document.getElementById('submit-btn'),
        resetBtn: document.getElementById('reset-btn'),
        ticketExistsError: document.getElementById('ticket-exists-error'),
        viewTicketContainer: document.getElementById('view-ticket-container'),
        viewTicketBtn: document.getElementById('view-ticket-btn'),

        // --- State Management ---
        heldCoupons: new Map(),
        isUpdateMode: false,

        // --- API Helper ---
        api: {
            get: async (endpoint, params = {}) => {
                const url = new URL(`${BASE_PATH}/${endpoint}`);
                if (params) {
                    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
                }
                try {
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    return await App.handleResponse(response);
                } catch (error) {
                    return App.handleError(error);
                }
            },
            post: async (endpoint, body = {}) => {
                try {
                    const response = await fetch(`${BASE_PATH}/${endpoint}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(body)
                    });
                    return await App.handleResponse(response);
                } catch (error) {
                    return App.handleError(error);
                }
            }
        },

        // --- Response Handlers ---
        handleResponse: async (response) => {
            const data = await response.json().catch(() => ({}));
            return { ok: response.ok, status: response.status, data };
        },
        handleError: (error) => {
            console.error('API Error:', error);
            App.showToast('Network error. Please try again.', 'error');
            return { ok: false, data: { message: error.message } };
        },

        // --- Toast Notifications ---
        showToast: (message, type = 'success') => {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';

            toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} flex items-center transition-transform transform translate-x-full`;
            toast.innerHTML = `<i class="fas ${icon} mr-3"></i> <p>${message}</p>`;
            container.appendChild(toast);

            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 5000);
        },
        
        // --- Clipboard Helper ---
        pasteFromClipboard: async (inputElement) => {
            try {
                const text = await navigator.clipboard.readText();
                inputElement.value = text;
                inputElement.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (err) {
                App.showToast('Failed to paste from clipboard.', 'error');
            }
        }
    };

    // --- Classification Logic (section_classification.php) ---
    const ClassificationHandler = {
        init: () => {
            if (!App.categorySelect) return;
            App.categorySelect.addEventListener('change', ClassificationHandler.handleCategoryChange);
            App.subcategorySelect.addEventListener('change', ClassificationHandler.handleSubcategoryChange);
        },
        updateDropdown: async (selectElement, endpoint, params, defaultOptionText) => {
            selectElement.disabled = true;
            selectElement.innerHTML = `<option value="">Loading...</option>`;

            const result = await App.api.get(endpoint, params);
            selectElement.innerHTML = `<option value="" disabled selected>${defaultOptionText}</option>`;
            
            let items = [];
            if (result.ok && result.data.success) {
                const dataKey = Object.keys(result.data).find(key => Array.isArray(result.data[key]));
                if (dataKey) items = result.data[dataKey];
            }
            
            if (items.length > 0) {
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    selectElement.appendChild(option);
                });
                selectElement.disabled = false;
            } else {
                selectElement.innerHTML = `<option value="">${result.data.message || 'No data available'}</option>`;
            }
        },
        handleCategoryChange: () => {
            ClassificationHandler.updateDropdown(App.subcategorySelect, 'tickets/data/getSubcategories', { category_id: App.categorySelect.value }, 'Select Sub-Category');
            App.codeSelect.disabled = true;
            App.codeSelect.innerHTML = '<option value="">Select Sub-Category first</option>';
        },
        handleSubcategoryChange: () => {
            ClassificationHandler.updateDropdown(App.codeSelect, 'tickets/data/getCodes', { subcategory_id: App.subcategorySelect.value }, 'Select Code');
        }
    };

    // --- Details Logic (section_details.php) ---
    const DetailsHandler = {
        init: () => {
            if (!App.ticketNumberInput) return;
            App.ticketNumberInput.addEventListener('paste', () => setTimeout(DetailsHandler.findTicketByNumber, 50));
            App.ticketNumberInput.addEventListener('change', DetailsHandler.findTicketByNumber);
            App.countrySelect.addEventListener('change', DetailsHandler.updateCouponSectionVisibility);
            App.addCouponBtn.addEventListener('click', DetailsHandler.handleAddCouponClick);
        },
        findTicketByNumber: async () => {
            const ticketNumber = App.ticketNumberInput.value.trim();
            FormHandler.resetForm(false, false);
            if (!ticketNumber) return;

            const result = await App.api.get('tickets/data/getTicket', { ticket_number: ticketNumber });
            
            if (result.ok && result.data.success) {
                const ticket = result.data.ticket;
                DetailsHandler.fillFormWithTicketData(ticket);
                App.ticketExistsError.classList.remove('hidden');
                
                App.viewTicketBtn.href = `${BASE_PATH}/tickets/details/${ticket.id}`;
                App.viewTicketContainer.classList.remove('hidden-transition');

                App.submitBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Update Ticket';
                App.isUpdateMode = true;
            }
        },
        fillFormWithTicketData: (ticket) => {
            App.platformSelect.value = ticket.platform_id;
            App.phoneInput.value = ticket.phone;
            App.isVipCheckbox.checked = ticket.is_vip == 1;
            App.notesTextarea.value = ticket.notes;
            App.teamLeaderSelect.value = ticket.assigned_team_leader_id;
            
            if (ticket.country_id) {
                App.countrySelect.value = ticket.country_id;
                DetailsHandler.updateCouponSectionVisibility();
                App.couponsContainer.innerHTML = '';
                ticket.coupons.forEach(coupon => {
                    DetailsHandler.addCouponSelector([coupon], coupon.id);
                });
            }
            
            App.categorySelect.value = ticket.category_id;
            App.categorySelect.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                App.subcategorySelect.value = ticket.subcategory_id;
                App.subcategorySelect.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    App.codeSelect.value = ticket.code_id;
                }, 500);
            }, 500);
        },
        
        // Coupon Management
        updateCouponSectionVisibility: () => {
            if (!App.couponsSection || !App.countrySelect) return;
            App.couponsSection.classList.toggle('hidden', !App.countrySelect.value);
        },
        getExistingCouponIds: () => {
            return Array.from(App.couponsContainer.querySelectorAll('.coupon-select')).map(s => s.value).filter(Boolean);
        },
        handleAddCouponClick: async () => {
            const countryId = App.countrySelect.value;
            if (!countryId) {
                App.showToast('Please select a country first.', 'error');
                return;
            }
            const result = await App.api.get('tickets/data/getCouponsByCountry', {
                country_id: countryId,
                exclude_ids: DetailsHandler.getExistingCouponIds().join(',')
            });
            if (result.ok && result.data.coupons.length > 0) {
                DetailsHandler.addCouponSelector(result.data.coupons);
            } else {
                App.showToast('No more coupons available for this country.', 'info');
            }
        },
        addCouponSelector: (coupons, selectedCouponId = null) => {
            const selectorId = `coupon-${Date.now()}`;
            const couponDiv = document.createElement('div');
            couponDiv.className = 'flex items-center space-x-2';
            
            const select = document.createElement('select');
            select.id = selectorId;
            select.name = 'coupons[]';
            select.className = 'form-select block w-full coupon-select';
            select.innerHTML = `<option value="" selected>Select Coupon</option>`;
            coupons.forEach(coupon => {
                const option = document.createElement('option');
                option.value = coupon.id;
                option.textContent = `${coupon.code} (${coupon.value})`;
                select.appendChild(option);
            });
            if (selectedCouponId) select.value = selectedCouponId;

            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'btn btn-secondary p-2 copy-coupon-btn';
            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
            copyBtn.disabled = !selectedCouponId;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn bg-red-500 hover:bg-red-600 text-white p-2 remove-coupon-btn';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';

            couponDiv.appendChild(select);
            couponDiv.appendChild(copyBtn);
            couponDiv.appendChild(removeBtn);

            App.couponsContainer.appendChild(couponDiv);

            select.addEventListener('change', () => {
                copyBtn.disabled = !select.value;
                if (select.value) {
                    DetailsHandler.handleCouponHold(selectorId, select.value);
                }
            });
            copyBtn.addEventListener('click', () => {
                const selectedText = select.options[select.selectedIndex].text;
                navigator.clipboard.writeText(selectedText.split(' ')[0]);
                App.showToast('Coupon code copied!', 'success');
            });
            removeBtn.addEventListener('click', () => {
                DetailsHandler.handleCouponRelease(selectorId);
                couponDiv.remove();
            });

            if (selectedCouponId) {
                DetailsHandler.handleCouponHold(selectorId, selectedCouponId);
            }
        },
        handleCouponHold: async (selectorId, couponId) => {
            if (App.heldCoupons.has(selectorId)) {
                await DetailsHandler.handleCouponRelease(selectorId);
            }
            const result = await App.api.post('tickets/data/holdCoupon', { coupon_id: couponId });
            if (result.ok && result.data.success) {
                App.heldCoupons.set(selectorId, couponId);
            } else {
                App.showToast(result.data.message || 'Failed to hold coupon.', 'error');
                const select = document.getElementById(selectorId);
                if (select) select.value = '';
            }
        },
        handleCouponRelease: async (selectorId) => {
            if (App.heldCoupons.has(selectorId)) {
                const couponId = App.heldCoupons.get(selectorId);
                await App.api.post('tickets/data/releaseCoupon', { coupon_id: couponId });
                App.heldCoupons.delete(selectorId);
            }
        },
        releaseAllHeldCoupons: async () => {
            const promises = Array.from(App.heldCoupons.values()).map(couponId =>
                App.api.post('tickets/data/releaseCoupon', { coupon_id: couponId })
            );
            await Promise.all(promises);
            App.heldCoupons.clear();
        }
    };

    // --- Main Form Logic ---
    const FormHandler = {
        init: () => {
            if (!App.form) return;
            App.form.addEventListener('submit', FormHandler.handleSubmit);
            App.resetBtn.addEventListener('click', () => FormHandler.resetForm(true, true));
            App.pasteTicketBtn.addEventListener('click', () => App.pasteFromClipboard(App.ticketNumberInput));
            App.pastePhoneBtn.addEventListener('click', () => App.pasteFromClipboard(App.phoneInput));
            window.addEventListener('beforeunload', DetailsHandler.releaseAllHeldCoupons);
        },
        resetForm: (clearTicketNumber = true, showToast = false) => {
            App.isUpdateMode = false;
            if (clearTicketNumber) App.ticketNumberInput.value = '';
            App.platformSelect.selectedIndex = 0;
            App.phoneInput.value = '';
            App.countrySelect.selectedIndex = 0;
            App.isVipCheckbox.checked = false;
            App.notesTextarea.value = '';
            App.categorySelect.selectedIndex = 0;
            App.subcategorySelect.innerHTML = '<option value="">Select Main Category First</option>';
            App.subcategorySelect.disabled = true;
            App.codeSelect.innerHTML = '<option value="">Select Sub-Category First</option>';
            App.codeSelect.disabled = true;
            
            App.ticketExistsError.classList.add('hidden');
            App.viewTicketContainer.classList.add('hidden-transition');
            App.viewTicketBtn.href = '#';

            DetailsHandler.releaseAllHeldCoupons();
            App.couponsContainer.innerHTML = '';
            DetailsHandler.updateCouponSectionVisibility();

            App.submitBtn.innerHTML = '<i class="fas fa-plus mr-2"></i> Create Ticket';

            if (showToast) App.showToast('Form has been cleared.', 'info');
        },
        handleSubmit: async (e) => {
            e.preventDefault();
            const requiredFields = [App.ticketNumberInput, App.platformSelect, App.categorySelect, App.subcategorySelect, App.codeSelect];
            if (requiredFields.some(field => !field.value)) {
                App.showToast('Please fill all required fields.', 'error');
                return;
            }

            App.submitBtn.disabled = true;
            App.submitBtn.innerHTML = App.isUpdateMode ? 'Updating...' : 'Creating...';
            
            const formData = new FormData(App.form);
            const data = Object.fromEntries(formData.entries());
            data.is_vip = App.isVipCheckbox.checked ? '1' : '0';
            data.coupons = Array.from(App.couponsContainer.querySelectorAll('.coupon-select')).map(s => s.value).filter(Boolean);

            const endpoint = 'tickets/store';
            const result = await App.api.post(endpoint, data);

            if (result.ok && result.data.success) {
                App.showToast(result.data.message, 'success');
                setTimeout(() => {
                    if (result.data.ticket_id) {
                        window.location.href = `${BASE_PATH}/tickets/details/${result.data.ticket_id}`;
                    } else {
                        FormHandler.resetForm(true);
                    }
                }, 1500);
            } else {
                App.showToast(result.data.message || 'An unknown error occurred.', 'error');
            }
            
            App.submitBtn.disabled = false;
            App.submitBtn.innerHTML = App.isUpdateMode ? '<i class="fas fa-sync-alt mr-2"></i> Update Ticket' : '<i class="fas fa-plus mr-2"></i> Create Ticket';
        }
    };

    // --- Initialize all modules ---
    if (document.getElementById('ticket-form')) {
        ClassificationHandler.init();
        DetailsHandler.init();
        FormHandler.init();
    }
});
