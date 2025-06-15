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