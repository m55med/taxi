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
        heldCoupons: new Map(), // Tracks coupons held by the current user {selectorId: couponId}
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
            App.showToast('حدث خطأ في الشبكة. يرجى المحاولة مرة أخرى.', 'error');
        return { ok: false, data: { message: error.message } };
        },

    // --- Toast Notifications ---
        showToast: (message, type = 'success') => {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';

        toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} flex items-center transition-transform transform translate-x-full`;
        toast.innerHTML = `<i class="fas ${icon} ml-3"></i> <p>${message}</p>`;
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
                App.showToast('فشل اللصق من الحافظة.', 'error');
            }
        }
    };

    // --- Classification Logic (section_classification.php) ---
    const ClassificationHandler = {
        init: () => {
            App.categorySelect.addEventListener('change', ClassificationHandler.handleCategoryChange);
            App.subcategorySelect.addEventListener('change', ClassificationHandler.handleSubcategoryChange);
        },
        updateDropdown: async (selectElement, endpoint, params, defaultOptionText) => {
        selectElement.disabled = true;
        selectElement.innerHTML = `<option value="">جاري التحميل...</option>`;

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
            selectElement.innerHTML = `<option value="">${result.data.message || 'لا توجد بيانات'}</option>`;
            }
        },
        handleCategoryChange: () => {
            ClassificationHandler.updateDropdown(App.subcategorySelect, 'tickets/data/getSubcategories', { category_id: App.categorySelect.value }, 'اختر التصنيف الفرعي');
            App.codeSelect.disabled = true;
            App.codeSelect.innerHTML = '<option value="">اختر التصنيف الفرعي أولاً</option>';
        },
        handleSubcategoryChange: () => {
            ClassificationHandler.updateDropdown(App.codeSelect, 'tickets/data/getCodes', { subcategory_id: App.subcategorySelect.value }, 'اختر الكود');
        }
    };

    // --- Details Logic (section_details.php) ---
    const DetailsHandler = {
        init: () => {
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

                App.submitBtn.innerHTML = '<i class="fas fa-sync-alt ml-2"></i> تحديث التذكرة';
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
            App.couponsSection.classList.toggle('hidden', !App.countrySelect.value);
        },
        getExistingCouponIds: () => {
            return Array.from(App.couponsContainer.querySelectorAll('.coupon-select')).map(s => s.value).filter(Boolean);
        },
        handleAddCouponClick: async () => {
            const countryId = App.countrySelect.value;
            if (!countryId) {
                App.showToast('الرجاء اختيار الدولة أولاً.', 'error');
                return;
            }
            const result = await App.api.get('tickets/data/getCouponsByCountry', {
                country_id: countryId,
                exclude_ids: DetailsHandler.getExistingCouponIds().join(',')
            });
            if (result.ok && result.data.coupons.length > 0) {
                DetailsHandler.addCouponSelector(result.data.coupons);
            } else {
                App.showToast('لا توجد كوبونات متاحة لهذه الدولة.', 'info');
            }
        },
        addCouponSelector: (coupons, selectedCouponId = null) => {
        const selectorId = `coupon-${Date.now()}`;
        const couponDiv = document.createElement('div');
        couponDiv.className = 'flex items-center space-x-2 space-x-reverse';
        
        const select = document.createElement('select');
        select.id = selectorId;
        select.name = 'coupons[]';
        select.className = 'form-select block w-full coupon-select';
        select.innerHTML = `<option value="" selected>اختر كوبون</option>`;
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

            select.addEventListener('change', () => DetailsHandler.handleCouponChange(select, copyBtn));
            removeBtn.addEventListener('click', () => DetailsHandler.handleCouponRemove(couponDiv, select.id));
            copyBtn.addEventListener('click', () => DetailsHandler.handleCouponCopy(select));
            
        if (selectedCouponId) {
            select.disabled = true;
            copyBtn.disabled = false;
                removeBtn.classList.add('hidden');
        }
        },
        handleCouponChange: async (select, copyBtn) => {
            const newCouponId = select.value;
            const oldCouponId = App.heldCoupons.get(select.id);
    
            if (newCouponId === oldCouponId) return;

        if (oldCouponId) {
                await App.api.post('tickets/data/releaseCoupon', { coupon_id: oldCouponId });
                App.heldCoupons.delete(select.id);
        }
        copyBtn.disabled = true;

        if (newCouponId) {
                const result = await App.api.post('tickets/data/holdCoupon', { coupon_id: newCouponId });
            if (result.ok && result.data.success) {
                    App.heldCoupons.set(select.id, newCouponId);
                copyBtn.disabled = false;
                    App.showToast('تم حجز الكوبون بنجاح.', 'success');
            } else {
                    App.showToast(result.data.message || 'فشل حجز الكوبون، قد يكون مستخدماً.', 'error');
                select.querySelector(`option[value="${newCouponId}"]`)?.remove();
                    select.value = '';
                    App.heldCoupons.delete(select.id);
                }
            }
        },
        handleCouponRemove: async (couponDiv, selectorId) => {
            const couponId = App.heldCoupons.get(selectorId);
        if (couponId) {
                await App.api.post('tickets/data/releaseCoupon', { coupon_id: couponId });
                App.heldCoupons.delete(selectorId);
        }
        couponDiv.remove();
            App.showToast('تم حذف الكوبون.', 'info');
        },
        handleCouponCopy: (select) => {
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;
        const couponCode = selectedOption.textContent.split(' ')[0];
        navigator.clipboard.writeText(couponCode)
                .then(() => App.showToast(`تم نسخ الكود: ${couponCode}`, 'success'))
                .catch(() => App.showToast('فشل نسخ الكود.', 'error'));
        }
    };
    
    // --- Main Form Logic ---
    const FormHandler = {
        init: () => {
            App.form.addEventListener('submit', FormHandler.handleSubmit);
            App.resetBtn.addEventListener('click', () => FormHandler.resetForm(true, true));
            App.pasteTicketBtn.addEventListener('click', () => App.pasteFromClipboard(App.ticketNumberInput));
            App.pastePhoneBtn.addEventListener('click', () => App.pasteFromClipboard(App.phoneInput));
            window.addEventListener('beforeunload', FormHandler.handlePageUnload);
            DetailsHandler.updateCouponSectionVisibility();
        },
        resetForm: (fullReset = true, showToast = true) => {
            // Hide feedback elements
            App.ticketExistsError.classList.add('hidden');
            App.viewTicketContainer.classList.add('hidden-transition');

            // Reset update mode
            App.isUpdateMode = false;
            App.submitBtn.innerHTML = '<i class="fas fa-plus ml-2"></i> إنشاء تذكرة';

            if (fullReset) {
                // Clear all form fields
                App.form.reset();

                // Manually trigger change events for selects to reset dependent dropdowns
                App.categorySelect.dispatchEvent(new Event('change'));
                App.countrySelect.dispatchEvent(new Event('change'));

                // Clear dynamic elements
                App.couponsContainer.innerHTML = '';
                App.heldCoupons.clear();

                if (showToast) {
                    App.showToast('تم مسح جميع الحقول.', 'info');
                }
            } else {
                // On partial reset (ticket search), only reset fields that are filled by search
                const fieldsToReset = [
                    App.platformSelect, App.phoneInput, App.isVipCheckbox, App.notesTextarea,
                    App.teamLeaderSelect, App.countrySelect, App.categorySelect, App.subcategorySelect, App.codeSelect
                ];
                fieldsToReset.forEach(field => {
                    if (field.type === 'checkbox') field.checked = false;
                    else field.value = '';
                });

                App.couponsContainer.innerHTML = '';
                App.heldCoupons.clear();
                DetailsHandler.updateCouponSectionVisibility();
                ClassificationHandler.handleCategoryChange();
            }
        },
        handleSubmit: async (e) => {
            e.preventDefault();
            const formData = new FormData(App.form);
            const data = Object.fromEntries(formData.entries());
            data.coupons = DetailsHandler.getExistingCouponIds();

            const endpoint = App.isUpdateMode ? 'tickets/update' : 'tickets/store';
            const result = await App.api.post(endpoint, data);

            if (result.ok && result.data.success) {
                App.showToast(result.data.message, 'success');
                App.heldCoupons.clear();
                FormHandler.resetForm();
            } else {
                const actionText = App.isUpdateMode ? 'تحديث' : 'حفظ';
                App.showToast(result.data.message || `حدث خطأ أثناء ${actionText} التذكرة.`, 'error');
            }
        },
        handlePageUnload: () => {
            if (App.heldCoupons.size > 0) {
                 navigator.sendBeacon(`${BASE_PATH}/tickets/data/releaseAllCoupons`);
            }
        }
    };

    // --- Initialize Handlers ---
    ClassificationHandler.init();
    DetailsHandler.init();
    FormHandler.init();
});
