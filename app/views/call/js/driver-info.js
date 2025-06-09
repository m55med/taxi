const DriverInfoModule = {
    init() {
        this.form = document.getElementById('driverInfoForm');
        if (this.form) {
            this.initializeForm();
        }

        // Toggle functionality
        const toggleButton = document.getElementById('driverInfoToggle');
        const content = document.getElementById('driverInfoContent');
        const icon = document.getElementById('driverInfoIcon');
        
        if (toggleButton && content && icon) {
            toggleButton.addEventListener('click', function() {
                const isHidden = content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
                
                // Toggle border visibility
                if (isHidden) {
                    toggleButton.classList.add('border-transparent');
                    toggleButton.classList.remove('border-gray-200');
                } else {
                    toggleButton.classList.remove('border-transparent');
                    toggleButton.classList.add('border-gray-200');
                }
            });
        }
    },

    initializeForm() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(this.form);
            const submitButton = this.form.querySelector('button[type="submit"]');
            
            // Validate required fields
            if (!formData.get('driver_id')) {
                showNotification('خطأ: معرف السائق مفقود', 'error');
                return;
            }

            if (!formData.get('name')) {
                showNotification('خطأ: اسم السائق مطلوب', 'error');
                return;
            }

            submitButton.disabled = true;
            
            try {
                const response = await fetch(BASE_PATH + '/driver/update', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.driver) {
                    showNotification('تم حفظ البيانات بنجاح', 'success');
                    this.updateProfileCard(data.driver);
                } else {
                    throw new Error(data.error || 'فشل في حفظ البيانات');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('حدث خطأ أثناء حفظ البيانات: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
            }
        });
    },

    updateProfileCard(driver) {
        // Update Name and ID
        document.querySelector('.text-xl.font-bold').textContent = driver.name;
        document.querySelector('.text-sm.text-gray-500').textContent = `ID: ${driver.id}`;

        // Update Phone
        const phoneSpan = document.getElementById('driverPhone');
        if(phoneSpan) phoneSpan.textContent = driver.phone;
        
        // Helper maps for translation
        const dataSourceMap = {
            'form': 'نموذج تسجيل', 'referral': 'توصية', 'telegram': 'تلغرام', 
            'staff': 'عن طريق موظف', 'admin': 'إضافة إدارية'
        };
        const appStatusMap = {
            'active': { text: 'نشط', class: 'bg-green-100 text-green-800' },
            'inactive': { text: 'غير نشط', class: 'bg-yellow-100 text-yellow-800' },
            'banned': { text: 'محظور', class: 'bg-red-100 text-red-800' }
        };

        // Update Data Source
        const dataSourceSpan = document.getElementById('driverDataSource');
        if(dataSourceSpan) dataSourceSpan.textContent = dataSourceMap[driver.data_source] || driver.data_source;
        
        // Update App Status
        const appStatusSpan = document.getElementById('driverAppStatus');
        if(appStatusSpan) {
            const statusInfo = appStatusMap[driver.app_status] || { text: driver.app_status, class: 'bg-gray-100 text-gray-800' };
            appStatusSpan.textContent = statusInfo.text;
            appStatusSpan.className = `px-2 py-1 text-xs font-medium rounded-full ${statusInfo.class}`;
        }
    }
};

// Initialize module when DOM is loaded
document.addEventListener('DOMContentLoaded', () => DriverInfoModule.init()); 