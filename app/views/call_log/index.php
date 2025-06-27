<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto p-8">
    <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-2"></div>
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-xl text-center">
        <i class="fas fa-phone-alt fa-3x text-blue-500 mb-4"></i>
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Log Incoming Call</h1>
        <p class="text-gray-600 mb-6">Enter the customer's phone number to start logging the call and create a new ticket.</p>
        
        <form id="start-call-form">
            <div class="mb-4">
                <label for="phone_number" class="sr-only">Phone Number</label>
                <div class="relative">
                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-phone text-gray-400"></i>
                    </div>
                    <input type="tel" id="phone_number" name="phone_number" class="w-full pl-10 pr-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter phone number..." required>
                </div>
            </div>
            <button type="submit" id="start-call-btn" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                <i class="fas fa-play-circle mr-2"></i>Start Call & Create Ticket
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('start-call-form');
    const submitBtn = document.getElementById('start-call-btn');
    const phoneInput = document.getElementById('phone_number');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const phoneNumber = phoneInput.value.trim();
        if (!phoneNumber) {
            showToast('Please enter a phone number.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Starting...';

        try {
            const response = await fetch('<?= BASE_PATH ?>/call_log/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone_number: phoneNumber })
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 1500);
            } else {
                showToast(result.message || 'An error occurred.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-play-circle mr-2"></i>Start Call & Create Ticket';
            }
        } catch (error) {
            showToast('A network error occurred.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-play-circle mr-2"></i>Start Call & Create Ticket';
        }
    });

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';

        toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} flex items-center transition-opacity duration-300 opacity-0`;
        toast.innerHTML = `<i class="fas ${icon} mr-3"></i> <p>${message}</p>`;
        container.appendChild(toast);

        setTimeout(() => toast.classList.remove('opacity-0'), 100);
        setTimeout(() => {
            toast.classList.add('opacity-0');
            toast.addEventListener('transitionend', () => toast.remove());
        }, 5000);
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 