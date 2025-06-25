<?php include_once __DIR__ . '/../includes/header.php'; ?>
<link rel="stylesheet" href="<?= BASE_PATH ?>/app/views/tickets/css/style.css?v=1.1">

<div class="container mx-auto px-4 py-10">
    <header class="text-center mb-10">
        <h1 class="text-4xl font-bold text-gray-800">Ticket Management System</h1>
        <p class="text-lg text-gray-600 mt-2">Create a new ticket or search for an existing one.</p>
    </header>

    <main class="max-w-4xl mx-auto">
        <form id="ticket-form" class="bg-white rounded-xl shadow-lg p-8">
            
            <?php include 'sections/section_details.php'; ?>
            
            <?php include 'sections/section_classification.php'; ?>

            <?php include 'sections/section_assignment.php'; ?>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end space-x-4">
                <button type="button" id="reset-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-colors duration-200"><i class="fas fa-redo mr-2"></i>Clear Fields</button>
                <button type="submit" id="submit-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors duration-200"><i class="fas fa-plus mr-2"></i>Create Ticket</button>
            </div>
        </form>
    </main>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-8 right-8 z-[100] w-full max-w-sm space-y-3"></div>

<script>
    const BASE_PATH = '<?= BASE_PATH ?>';

    document.addEventListener('DOMContentLoaded', function() {
        const ticketNumberInput = document.getElementById('ticket_number');
        if (!ticketNumberInput) return;

        const notificationDiv = document.createElement('div');
        notificationDiv.id = 'ticket-exists-notification';
        notificationDiv.className = 'mt-2 text-sm';
        notificationDiv.style.display = 'none';
        ticketNumberInput.parentNode.insertBefore(notificationDiv, ticketNumberInput.nextSibling);

        let debounceTimer;
        ticketNumberInput.addEventListener('keyup', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const ticketNumber = ticketNumberInput.value.trim();
                if (ticketNumber.length > 3) {
                    checkTicketExistence(ticketNumber);
                } else {
                    notificationDiv.style.display = 'none';
                }
            }, 400); // 400ms delay
        });

        async function checkTicketExistence(ticketNumber) {
            try {
                const response = await fetch(`${BASE_PATH}/tickets/checkTicket/${ticketNumber}`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const result = await response.json();
                
                if (result.exists) {
                    notificationDiv.innerHTML = `
                        <div class="p-3 bg-yellow-100 border-l-4 border-yellow-400 text-yellow-800 rounded-r-md">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            التذكرة موجودة بالفعل. 
                            <a href="${BASE_PATH}/tickets/details/${result.id}" class="font-bold underline hover:text-yellow-900" target="_blank">
                                عرض التفاصيل والسجل
                                <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                            </a>
                        </div>
                    `;
                    notificationDiv.style.display = 'block';
                } else {
                    notificationDiv.style.display = 'none';
                }
            } catch (error) {
                console.error('Error checking ticket:', error);
                notificationDiv.style.display = 'none';
            }
        }
    });
</script>
<script src="<?= BASE_PATH ?>/app/views/tickets/js/main.js?v=1.2"></script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>