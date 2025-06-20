document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('driverInfoToggle');
    const contentDiv = document.getElementById('driverInfoContent');
    const icon = document.getElementById('driverInfoIcon');
    const form = document.getElementById('driverInfoForm');

    // Toggle visibility of the driver info form
    if (toggleButton && contentDiv && icon) {
        toggleButton.addEventListener('click', () => {
            contentDiv.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    }

    // Handle form submission
    if (form) {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(form);

            // Add a loading indicator to the button
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonContent = submitButton.innerHTML;
            submitButton.innerHTML = `<i class="fas fa-spinner fa-spin ml-2"></i> جارٍ الحفظ...`;
            submitButton.disabled = true;

            try {
                const response = await fetch(`${BASE_PATH}/driver/update`, {
                    method: 'POST',
                    body: formData // Send as form data, not JSON
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showToast(result.message || 'تم تحديث البيانات بنجاح!', 'success');
                    
                    // Update Name
                    const nameElement = document.getElementById('driver-profile-name');
                    if (nameElement && formData.get('name')) {
                        nameElement.textContent = formData.get('name');
                    }

                    // Update Email
                    const emailElement = document.getElementById('driver-profile-email');
                    if (emailElement) {
                        emailElement.textContent = formData.get('email') || 'غير متوفر';
                    }

                    // Update App Status
                    const statusElement = document.getElementById('driverAppStatus');
                    if (statusElement && formData.get('app_status')) {
                        const statusMap = {
                            active: { text: 'نشط', class: 'bg-green-100 text-green-800' },
                            inactive: { text: 'غير نشط', class: 'bg-yellow-100 text-yellow-800' },
                            banned: { text: 'محظور', class: 'bg-red-100 text-red-800' },
                        };
                        const statusInfo = statusMap[formData.get('app_status')] || { text: formData.get('app_status'), class: 'bg-gray-100 text-gray-800' };
                        
                        statusElement.textContent = statusInfo.text;
                        statusElement.className = `px-2 py-1 text-xs font-medium rounded-full ${statusInfo.class}`;
                    }

                    // Update Gender
                    const genderElement = document.getElementById('driver-profile-gender');
                    if (genderElement && formData.get('gender')) {
                        genderElement.textContent = formData.get('gender') === 'male' ? 'ذكر' : 'أنثى';
                    }

                    // Update Nationality
                    const nationalityElement = document.getElementById('driver-profile-nationality');
                    const countrySelect = form.querySelector('select[name="country_id"]');
                    if (nationalityElement && countrySelect && countrySelect.selectedIndex > 0) {
                        nationalityElement.textContent = countrySelect.options[countrySelect.selectedIndex].text;
                    }

                    // Update Car Type
                    const carTypeElement = document.getElementById('driver-profile-car-type');
                    const carTypeSelect = form.querySelector('select[name="car_type_id"]');
                    if (carTypeElement && carTypeSelect && carTypeSelect.selectedIndex > 0) {
                        carTypeElement.textContent = carTypeSelect.options[carTypeSelect.selectedIndex].text;
                    }

                    // Update Notes
                    const notesElement = document.getElementById('driver-profile-notes');
                    if (notesElement) {
                        notesElement.textContent = formData.get('notes') || 'لا يوجد';
                    }

                } else {
                    showToast(result.message || 'فشل تحديث البيانات.', 'error');
                }

            } catch (error) {
                console.error('Error submitting driver info form:', error);
                showToast('حدث خطأ فني أثناء الاتصال بالخادم.', 'error');
            } finally {
                // Restore button state
                submitButton.innerHTML = originalButtonContent;
                submitButton.disabled = false;
            }
        });
    }
});
