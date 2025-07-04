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
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
            submitButton.disabled = true;

            try {
                const response = await fetch(`${URLROOT}/driver/update`, {
                    method: 'POST',
                    body: formData // Send as form data, not JSON
                });

                const parsedData = await response.json();

                if (parsedData.success && parsedData.driver) {
                    showToast(parsedData.message || 'تم تحديث البيانات بنجاح!', 'success');
                    const driver = parsedData.driver;

                    // --- Update the display card using data confirmed by the server ---

                    const nameElement = document.getElementById('driver-profile-name');
                    if (nameElement) nameElement.textContent = driver.name;

                    const emailElement = document.getElementById('driver-profile-email');
                    if (emailElement) emailElement.textContent = driver.email || 'غير متوفر';

                    const statusElement = document.getElementById('driverAppStatus');
                    if (statusElement) {
                        const statusMap = {
                            active: { text: 'نشط', class: 'bg-green-100 text-green-800' },
                            inactive: { text: 'غير نشط', class: 'bg-yellow-100 text-yellow-800' },
                            banned: { text: 'محظور', class: 'bg-red-100 text-red-800' },
                        };
                        const statusInfo = statusMap[driver.app_status] || { text: driver.app_status, class: 'bg-gray-100 text-gray-800' };
                        
                        statusElement.textContent = statusInfo.text;
                        statusElement.className = `px-2 py-1 text-xs font-medium rounded-full ${statusInfo.class}`;
                    }

                    const genderElement = document.getElementById('driver-profile-gender');
                    if (genderElement) {
                        if (driver.gender === 'male') genderElement.textContent = 'ذكر';
                        else if (driver.gender === 'female') genderElement.textContent = 'أنثى';
                        else genderElement.textContent = 'غير محدد';
                    }
                    
                    const nationalityElement = document.getElementById('driver-profile-nationality');
                    if (nationalityElement) nationalityElement.textContent = driver.country_name || 'غير محدد';

                    const carTypeElement = document.getElementById('driver-profile-car-type');
                    if (carTypeElement) carTypeElement.textContent = driver.car_type_name || 'غير محدد';

                    const notesElement = document.getElementById('driver-profile-notes');
                    if (notesElement) notesElement.textContent = driver.notes || 'لا يوجد';

                    const tripsStatusElement = document.getElementById('driverTripsStatus');
                    if (tripsStatusElement) {
                        if (!!parseInt(driver.has_many_trips)) {
                            tripsStatusElement.className = 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800';
                            tripsStatusElement.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Exceeds 10 Trips';
                        } else {
                            tripsStatusElement.className = 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
                            tripsStatusElement.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Under 10 Trips';
                        }
                    }
                    
                    // --- Also update the form fields to keep them in sync ---
                    
                    form.querySelector('[name="name"]').value = driver.name;
                    form.querySelector('[name="email"]').value = driver.email;
                    form.querySelector('[name="gender"]').value = driver.gender;
                    form.querySelector('[name="country_id"]').value = driver.country_id;
                    form.querySelector('[name="app_status"]').value = driver.app_status;
                    form.querySelector('[name="car_type_id"]').value = driver.car_type_id;
                    form.querySelector('[name="notes"]').value = driver.notes;

                    const hasManyTripsToggle = form.querySelector('[name="has_many_trips"][type="checkbox"]');
                    if(hasManyTripsToggle) {
                        hasManyTripsToggle.checked = !!parseInt(driver.has_many_trips);
                    }

                } else {
                    showToast(parsedData.message || 'فشل تحديث البيانات.', 'error');
                }

            } catch (error) {
                console.error('Error submitting form:', error);
                showToast('error', 'An unexpected error occurred.');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save mr-2"></i> Update Driver Info';
            }
        });
    }
});
