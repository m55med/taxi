function usersPage() {
    return {
        forceLogoutModal: {
            open: false,
            userId: null,
            username: '',
            message: ''
        },
        init() {
            // Initialize Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-left",
                "timeOut": "5000",
            };

            // Apply initial classes for status dropdowns
            document.querySelectorAll('.status-select').forEach(select => {
                this.updateStatusClass(select);
            });
        },
        openForceLogoutModal(userId, username) {
            this.forceLogoutModal.userId = userId;
            this.forceLogoutModal.username = username;
            this.forceLogoutModal.message = '';
            this.forceLogoutModal.open = true;
        },
        closeForceLogoutModal() {
            this.forceLogoutModal.open = false;
        },
        submitForceLogout() {
            const formData = new FormData();
            formData.append('user_id', this.forceLogoutModal.userId);
            formData.append('message', this.forceLogoutModal.message);

            fetch(`${BASE_PATH}/dashboard/forceLogout`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message || 'حدث خطأ.');
                }
                this.closeForceLogoutModal();
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('حدث خطأ فادح.');
                this.closeForceLogoutModal();
            });
        },
        updateUserRole(selectElement) {
            const userId = selectElement.dataset.userId;
            const roleId = selectElement.value;

            fetch(`${BASE_PATH}/dashboard/updateUserRole/${userId}/${roleId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    toastr.success('تم تحديث الدور بنجاح.');
                } else {
                    toastr.error(data.message || 'حدث خطأ أثناء تحديث الدور');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('حدث خطأ أثناء تحديث الدور');
                setTimeout(() => location.reload(), 1500);
            });
        },
        updateUserStatus(selectElement) {
            const userId = selectElement.dataset.userId;
            const newStatus = selectElement.value;

            fetch(`${BASE_PATH}/dashboard/updateUserStatus/${userId}/${newStatus}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    this.updateStatusClass(selectElement);
                    toastr.success('تم تحديث الحالة بنجاح.');
                    // You might want to update the "last activity" cell here if needed
                } else {
                    toastr.error(data.message || 'حدث خطأ أثناء تحديث الحالة');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('حدث خطأ أثناء تحديث الحالة');
                setTimeout(() => location.reload(), 1500);
            });
        },
        updateStatusClass(selectElement) {
            const status = selectElement.value;
            selectElement.className = `status-select text-xs font-semibold rounded-full px-2 py-1 ${status}`;
        }
    }
}

// Keep old functions for pages that don't use Alpine
function updateUserRole(selectElement) {
    const userId = selectElement.dataset.userId;
    const roleId = selectElement.value;

    fetch(`${BASE_PATH}/dashboard/updateUserRole/${userId}/${roleId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.status) {
            alert(data.message || 'حدث خطأ أثناء تحديث الدور');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحديث الدور');
        location.reload();
    });
}

function updateUserStatus(selectElement) {
    const userId = selectElement.dataset.userId;
    const newStatus = selectElement.value;

    fetch(`${BASE_PATH}/dashboard/updateUserStatus/${userId}/${newStatus}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            // تحديث الفئة CSS للقائمة المنسدلة
            selectElement.className = `status-select text-xs font-semibold rounded-full px-2 py-1 ${newStatus}`;
            
            // تحديث آخر نشاط
            const row = selectElement.closest('tr');
            const lastActivityCell = row.querySelector('td:nth-last-child(2)');
            lastActivityCell.textContent = 'منذ لحظات';
        } else {
            alert(data.message || 'حدث خطأ أثناء تحديث الحالة');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحديث الحالة');
        location.reload();
    });
}

// تطبيق الفئات الأولية على قوائم الحالة
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.className = `status-select text-xs font-semibold rounded-full px-2 py-1 ${select.value}`;
    });
});