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
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}

// Driver Modal Functions
function showDriverModal(driverId) {
    document.getElementById('driverModal').classList.remove('hidden');
    document.getElementById('reviewDriverId').value = driverId;
    
    fetch(`${BASE_URL}/review/getDriverDetails/${driverId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showNotification(data.error, false);
                return;
            }

            document.querySelector('#reviewForm select[name="status"]').value = data.driver.main_system_status || 'completed';
            document.querySelector('#reviewForm textarea[name="notes"]').value = data.driver.notes || '';
            
            const documentsContainer = document.getElementById('documentsList');
            documentsContainer.innerHTML = ''; // Clear previous content

            if (data.documents && data.documents.length > 0) {
                documentsContainer.innerHTML = data.documents.map(doc => `
                    <div class="document-item p-2 border-b" data-doc-id="${doc.id}">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">${doc.name}</span>
                            <div class="flex items-center space-x-2">
                                <span class="status-text text-xs font-semibold"></span>
                                <button type="button" class="approve-btn bg-green-500 text-white px-2 py-1 rounded text-xs">قبول</button>
                                <button type="button" class="reject-btn bg-red-500 text-white px-2 py-1 rounded text-xs">رفض</button>
                            </div>
                        </div>
                        <div class="rejection-note-container hidden mt-2">
                            <input type="text" placeholder="سبب الرفض..." class="rejection-note-input w-full p-1 border rounded text-xs">
                        </div>
                        <input type="hidden" class="doc-status" name="documents[${doc.id}][status]" value="${doc.status}">
                        <input type="hidden" class="doc-note" name="documents[${doc.id}][note]" value="${doc.note || ''}">
                    </div>
                `).join('');
            } else {
                documentsContainer.innerHTML = '<p class="text-sm text-gray-500">لا توجد مستندات مطلوبة لهذا السائق.</p>';
            }
            
            // Initialize states and attach event listeners after rendering
            initializeDocumentStates();

        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('حدث خطأ أثناء جلب بيانات السائق', false);
        });
}

function initializeDocumentStates() {
    document.querySelectorAll('.document-item').forEach(item => {
        const statusInput = item.querySelector('.doc-status');
        const noteInput = item.querySelector('.doc-note');
        const statusText = item.querySelector('.status-text');
        const approveBtn = item.querySelector('.approve-btn');
        const rejectBtn = item.querySelector('.reject-btn');
        const rejectionContainer = item.querySelector('.rejection-note-container');
        const rejectionInput = item.querySelector('.rejection-note-input');

        function updateState(status, note = '') {
            statusInput.value = status;
            noteInput.value = note;
            
            approveBtn.classList.remove('bg-green-700', 'ring-2', 'ring-green-300');
            rejectBtn.classList.remove('bg-red-700', 'ring-2', 'ring-red-300');
            rejectionContainer.classList.add('hidden');
            
            if (status === 'submitted') {
                statusText.textContent = 'مقبول';
                statusText.className = 'status-text text-xs font-semibold text-green-600';
                approveBtn.classList.add('bg-green-700', 'ring-2', 'ring-green-300');
            } else if (status === 'rejected') {
                statusText.textContent = 'مرفوض';
                statusText.className = 'status-text text-xs font-semibold text-red-600';
                rejectBtn.classList.add('bg-red-700', 'ring-2', 'ring-red-300');
                rejectionContainer.classList.remove('hidden');
                rejectionInput.value = note;
            } else {
                statusText.textContent = 'غير محدد';
                statusText.className = 'status-text text-xs font-semibold text-gray-500';
            }
        }

        approveBtn.addEventListener('click', () => {
            updateState('submitted');
        });

        rejectBtn.addEventListener('click', () => {
            updateState('rejected', rejectionInput.value);
        });

        rejectionInput.addEventListener('input', (e) => {
           if (statusInput.value === 'rejected') {
               noteInput.value = e.target.value;
           }
        });

        // Set initial state
        updateState(statusInput.value, noteInput.value);
    });
}

function hideDriverModal() {
    document.getElementById('driverModal').classList.add('hidden');
}

// Transfer Modal Functions
function showTransferModal(driverId) {
    document.getElementById('transferModal').classList.remove('hidden');
    document.getElementById('transferDriverId').value = driverId;
}

function hideTransferModal() {
    document.getElementById('transferModal').classList.add('hidden');
}

// Form Submissions
document.getElementById('reviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    
    try {
        const response = await fetch(`${BASE_URL}/review/updateDriver`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('تم تحديث بيانات السائق بنجاح');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(result.message || 'حدث خطأ أثناء تحديث البيانات');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message || 'حدث خطأ أثناء تحديث البيانات', false);
        submitButton.disabled = false;
    }
});

document.getElementById('transferForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    
    try {
        const response = await fetch(`${BASE_URL}/review/transferDriver`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('تم تحويل السائق بنجاح');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(result.message || 'حدث خطأ أثناء تحويل السائق');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message || 'حدث خطأ أثناء تحويل السائق', false);
        submitButton.disabled = false;
    }
}); 