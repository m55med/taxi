// Main Document Handling Code
document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('saveDocuments');
    const form = document.getElementById('documentsForm');
    
    if (!saveButton || !form) return;

    saveButton.addEventListener('click', async function(e) {
        e.preventDefault();
        
        try {
            // إظهار مؤشر التحميل
            showLoading();

            const driverId = form.dataset.driverId;
            const documents = [];

            // تجميع بيانات المستندات
            form.querySelectorAll('.document-item').forEach(item => {
                const checkbox = item.querySelector('.document-checkbox');
                const noteElement = item.querySelector('textarea');
                const docId = checkbox.value;
                
                documents.push({
                    id: docId,
                    status: checkbox.checked ? 'missing' : 'submitted',
                    note: noteElement ? noteElement.value.trim() : ''
                });
            });

            console.log('Sending data:', { driver_id: driverId, documents: documents });

            // إرسال البيانات إلى الخادم
            const response = await fetch(`${BASE_PATH}/call/documents/updateDocuments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    driver_id: driverId,
                    documents: documents
                })
            });

            const data = await response.json();
            console.log('Server response:', data);

            if (data.success) {
                showNotification('تم حفظ المستندات بنجاح', 'success');
                
                // تحديث معلومات التحديث لكل مستند
                if (data.documents) {
                    Object.entries(data.documents).forEach(([docId, doc]) => {
                        updateDocumentInfo(docId, doc);
                    });
                }
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء حفظ المستندات');
            }
        } catch (error) {
            console.error('Error saving documents:', error);
            showNotification(error.message || 'حدث خطأ في الاتصال بالخادم', 'error');
        } finally {
            hideLoading();
        }
    });

    // إضافة مستمع لتغيير حالة الصناديق
    document.querySelectorAll('.document-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const textarea = this.closest('.document-item').querySelector('textarea');
            if (textarea.hasAttribute('required')) {
                if (this.checked) {
                    textarea.classList.add('bg-yellow-50');
                } else {
                    textarea.classList.remove('bg-yellow-50', 'border-red-500');
                }
            }
        });
    });

    // تحديث حالة المستند
    document.querySelectorAll('.document-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.document-card');
            const documentId = card.dataset.documentId;
            const status = this.dataset.status;
            const note = card.querySelector('.document-note').value;
            const driverId = document.querySelector('[data-driver-id]').dataset.driverId;

            updateDocumentStatus(driverId, documentId, status, note, card);
        });
    });

    // حفظ الملاحظات عند الخروج من حقل النص
    document.querySelectorAll('.document-note').forEach(textarea => {
        textarea.addEventListener('blur', function() {
            const card = this.closest('.document-card');
            const documentId = card.dataset.documentId;
            const status = card.querySelector('.document-status-btn[class*="text-white"]').dataset.status;
            const note = this.value;
            const driverId = document.querySelector('[data-driver-id]').dataset.driverId;

            updateDocumentStatus(driverId, documentId, status, note, card);
        });
    });
});

function updateDocumentStatus(driverId, documentId, status, note, card) {
    // إظهار مؤشر التحميل
    showLoading();

    const formData = new FormData();
    formData.append('driver_id', driverId);
    formData.append('document_id', documentId);
    formData.append('status', status);
    formData.append('note', note);

    fetch(`${BASE_PATH}/call/documents/updateDocument`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // تحديث واجهة المستخدم
            updateDocumentUI(card, data.document);
            showNotification('تم تحديث حالة المستند بنجاح', 'success');
        } else {
            showNotification(data.message || 'حدث خطأ أثناء تحديث حالة المستند', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'حدث خطأ في الاتصال بالخادم', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

function updateDocumentUI(card, document) {
    // تحديث حالة الأزرار
    card.querySelectorAll('.document-status-btn').forEach(button => {
        const isActive = button.dataset.status === document.status;
        button.className = `flex-1 px-3 py-1 rounded text-sm font-medium transition-colors duration-200 document-status-btn ${
            isActive ? getStatusButtonClass(document.status) : 'bg-gray-100 text-gray-700 hover:' + getStatusButtonClass(button.dataset.status)
        }`;
    });

    // تحديث شارة الحالة
    const statusBadge = card.querySelector('.absolute span');
    const statusClasses = {
        'missing': 'bg-red-100 text-red-800',
        'submitted': 'bg-green-100 text-green-800',
        'rejected': 'bg-yellow-100 text-yellow-800'
    };
    const statusText = {
        'missing': 'مفقود',
        'submitted': 'مقدم',
        'rejected': 'مرفوض'
    };
    statusBadge.className = `px-2 py-1 rounded text-sm ${statusClasses[document.status]}`;
    statusBadge.textContent = statusText[document.status];

    // تحديث معلومات التحديث
    const updateInfo = card.querySelector('p.text-sm.text-gray-500');
    if (updateInfo) {
        updateInfo.innerHTML = `
            <i class="fas fa-clock ml-1"></i>
            آخر تحديث: ${new Date().toLocaleString('ar-SA')}
            <span class="mr-2">
                <i class="fas fa-user ml-1"></i>
                بواسطة: ${document.updated_by_name}
            </span>
        `;
    }
}

function getStatusButtonClass(status) {
    const classes = {
        'submitted': 'bg-green-600 text-white',
        'missing': 'bg-red-600 text-white',
        'rejected': 'bg-yellow-600 text-white'
    };
    return classes[status] || '';
}

function updateDocumentInfo(docId, doc) {
    const docItem = document.querySelector(`.document-item [value="${docId}"]`).closest('.document-item');
    if (!docItem) return;

    const infoDiv = docItem.querySelector('.document-info');
    if (!infoDiv) {
        // إنشاء div جديد لمعلومات التحديث إذا لم يكن موجوداً
        const newInfoDiv = document.createElement('div');
        newInfoDiv.className = 'mt-2 text-sm text-gray-500 mr-7 document-info';
        docItem.appendChild(newInfoDiv);
    }

    const updateTime = new Date(doc.updated_at).toLocaleString('ar-SA', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
    });

    const updateInfo = `
        <i class="fas fa-clock ml-1"></i>
        آخر تحديث: ${updateTime}
        ${doc.updated_by_name ? `
            <span class="mr-2">
                <i class="fas fa-user ml-1"></i>
                بواسطة: ${doc.updated_by_name}
            </span>
        ` : ''}
    `;

    (infoDiv || docItem.querySelector('.document-info')).innerHTML = updateInfo;
} 