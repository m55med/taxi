// Notification functions
function showNotification(message, isSuccess = true) {
    const notification = document.getElementById('notification');
    const notificationMessage = document.getElementById('notificationMessage');
    const notificationIcon = document.getElementById('notificationIcon');
    
    notificationMessage.textContent = message;
    
    if (isSuccess) {
        notificationIcon.className = 'fas fa-check-circle text-green-500 text-xl';
    } else {
        notificationIcon.className = 'fas fa-exclamation-circle text-red-500 text-xl';
    }
    
    notification.classList.remove('hidden');
    
    // Auto hide after 3 seconds
    setTimeout(hideNotification, 3000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.classList.add('hidden');
}

// Documents section toggle
function toggleDocuments() {
    const section = document.getElementById('documentsSection');
    const icon = document.getElementById('documentsIcon');
    
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        section.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('تم نسخ الرقم بنجاح');
    });
}

// Show/hide transfer modal
function showTransferModal() {
    document.getElementById('transferModal').classList.remove('hidden');
}

function hideTransferModal() {
    document.getElementById('transferModal').classList.add('hidden');
}

// Update app status
function updateAppStatus(driverId, status) {
    console.log('Updating app status:', { driverId, status });
    
    fetch(BASE_PATH + '/driver/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            driver_id: driverId,
            app_status: status
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response text:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification('تم تحديث الحالة بنجاح');
        } else {
            throw new Error(data.message || 'فشل في تحديث الحالة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ أثناء تحديث الحالة: ' + error.message, false);
    });
}

// Initialize all event listeners
function initCallCenter() {
    // Driver info form submission
    const driverInfoForm = document.getElementById('driverInfoForm');
    if (driverInfoForm) {
        driverInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission started');

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            // Log form data
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Validate required fields
            if (!formData.get('driver_id')) {
                console.error('Missing driver_id');
                showNotification('خطأ: معرف السائق مفقود', false);
                submitButton.disabled = false;
                return;
            }

            if (!formData.get('name')) {
                console.error('Missing name');
                showNotification('خطأ: اسم السائق مطلوب', false);
                submitButton.disabled = false;
                return;
            }

            console.log('Sending request to:', BASE_PATH + '/driver/update');
            
            fetch(BASE_PATH + '/driver/update', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    showNotification('تم حفظ البيانات بنجاح');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'فشل في حفظ البيانات');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ أثناء حفظ البيانات: ' + error.message, false);
            })
            .finally(() => {
                submitButton.disabled = false;
            });
        });
    }

    // Documents form submission
    const documentsForm = document.getElementById('documentsForm');
    if (documentsForm) {
        documentsForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'جاري الحفظ...';
            
            const formData = new FormData(this);
            try {
                const response = await fetch(BASE_PATH + '/call/updateDocuments', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('تم حفظ المستندات بنجاح');
                    // تأخير إعادة تحميل الصفحة لمدة ثانية واحدة للسماح للمستخدم برؤية رسالة النجاح
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(result.message || 'حدث خطأ أثناء حفظ المستندات', false);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('حدث خطأ أثناء حفظ المستندات', false);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'حفظ المستندات';
            }
        });
    }

    // Call form submission
    const callForm = document.getElementById('callForm');
    if (callForm) {
        // تعطيل زر الحفظ عند بدء التحميل
        const submitButton = callForm.querySelector('button[type="submit"]');
        const loadingSpinner = document.createElement('span');
        loadingSpinner.className = 'spinner hidden ml-2';
        loadingSpinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        submitButton.appendChild(loadingSpinner);

        // إضافة مستمعي الأحداث لحقول النموذج
        const callStatusSelect = callForm.querySelector('select[name="call_status"]');
        const notesTextarea = callForm.querySelector('textarea[name="notes"]');
        const nextCallInput = callForm.querySelector('input[name="next_call_at"]');
        
        // تحديث حالة زر الحفظ
        function updateSubmitButtonState() {
            const isValid = callStatusSelect.value !== '' && 
                          (callStatusSelect.value === 'no_answer' || notesTextarea.value.trim() !== '');
            submitButton.disabled = !isValid;
            submitButton.classList.toggle('opacity-50', !isValid);
        }

        // إضافة مستمعي الأحداث
        callStatusSelect.addEventListener('change', function() {
            const isNoAnswer = this.value === 'no_answer';
            if (isNoAnswer) {
                const nextHour = new Date();
                nextHour.setHours(nextHour.getHours() + 1);
                nextCallInput.value = nextHour.toISOString().slice(0, 16);
                nextCallInput.parentElement.classList.remove('hidden');
            } else {
                nextCallInput.parentElement.classList.add('hidden');
            }
            updateSubmitButtonState();
        });

        notesTextarea.addEventListener('input', updateSubmitButtonState);

        // معالجة تقديم النموذج
        callForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');

            submitButton.disabled = true;
            submitButton.querySelector('.spinner').classList.remove('hidden');

            try {
                const response = await fetch(BASE_PATH + '/call/recordCall', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();

                if (result.success) {
                    showNotification('تم تسجيل المكالمة بنجاح');

                    // بعد أي مكالمة ناجحة، انتقل دائمًا إلى السائق التالي
                    setTimeout(() => {
                        window.location.href = BASE_PATH + '/call';
                    }, 500); // تأخير بسيط للسماح للمستخدم برؤية الرسالة
                    
                } else {
                    showNotification(result.message || 'حدث خطأ', false);
                    submitButton.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('حدث خطأ فادح', false);
                submitButton.disabled = false;
            } finally {
                submitButton.querySelector('.spinner').classList.add('hidden');
            }
        });

        // استدعاء أولي لتحديث حالة الزر
        updateSubmitButtonState();
    }

    // Transfer form submission
    const transferForm = document.getElementById('transferForm');
    if (transferForm) {
        transferForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                const response = await fetch(BASE_PATH + '/call/assignDriver', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('تم تحويل السائق بنجاح');
                    hideTransferModal();
                    // الانتقال مباشرة للرقم التالي
                    window.location.href = BASE_PATH + '/call';
                } else {
                    showNotification(result.message || 'حدث خطأ أثناء تحويل السائق', false);
                    submitButton.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('حدث خطأ أثناء تحويل السائق', false);
                submitButton.disabled = false;
            }
        });
    }

    // Handle call status change
    const callStatus = document.getElementById('callStatus');
    if (callStatus) {
        callStatus.addEventListener('change', function() {
            const timeAvailabilityContainer = document.getElementById('timeAvailabilityContainer');
            const nextCallContainer = document.getElementById('nextCallContainer');
            
            if (this.value === 'answered') {
                timeAvailabilityContainer.classList.remove('hidden');
                nextCallContainer.classList.add('hidden');
            } else if (['busy', 'not_available', 'rescheduled'].includes(this.value)) {
                timeAvailabilityContainer.classList.add('hidden');
                nextCallContainer.classList.remove('hidden');
            } else if (this.value === 'no_answer') {
                timeAvailabilityContainer.classList.add('hidden');
                nextCallContainer.classList.add('hidden'); // إخفاء حقل تحديد الموعد لأنه سيتم تحديده تلقائياً
            } else {
                timeAvailabilityContainer.classList.add('hidden');
                nextCallContainer.classList.add('hidden');
            }
        });
    }

    // Handle time suitability change
    const timeSuitableInputs = document.querySelectorAll('input[name="time_suitable"]');
    if (timeSuitableInputs.length > 0) {
        timeSuitableInputs.forEach(radio => {
            radio.addEventListener('change', function() {
                const nextCallContainer = document.getElementById('nextCallContainer');
                nextCallContainer.classList.toggle('hidden', this.value === 'yes');
            });
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initCallCenter); 