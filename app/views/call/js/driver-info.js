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
            const data = Object.fromEntries(formData.entries());

            // Add a loading indicator to the button
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonContent = submitButton.innerHTML;
            submitButton.innerHTML = `<i class="fas fa-spinner fa-spin ml-2"></i> جارٍ الحفظ...`;
            submitButton.disabled = true;

            try {
                const response = await fetch(`${BASE_PATH}/calls/updateDriverInfo`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showToast(result.message || 'تم تحديث البيانات بنجاح!', 'success');
                    
                    if (document.querySelector('#driver-profile-name') && data.name) {
                        document.querySelector('#driver-profile-name').textContent = data.name;
                    }
                    if (document.querySelector('#driver-profile-email') && data.email) {
                        document.querySelector('#driver-profile-email').textContent = data.email;
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
