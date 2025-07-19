<?php
// Prepare flash message
$message = null;
if (isset($_SESSION['ticket_category_message'])) {
    $message = [
        'text' => $_SESSION['ticket_category_message'],
        'type' => $_SESSION['ticket_category_message_type'] ?? 'success'
    ];
    unset($_SESSION['ticket_category_message'], $_SESSION['ticket_category_message_type']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<style>
    /* Toast Notification */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
    }
    .toast {
        position: relative;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        color: #fff;
        font-size: 1rem;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        min-width: 250px;
        max-width: 350px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }
    .toast.success { background-color: #28a745; }
    .toast.error { background-color: #dc3545; }
    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 5px;
        width: 100%;
        background: rgba(0,0,0,0.2);
        animation: toast-progress-animation 3s linear forwards;
    }
    @keyframes toast-progress-animation {
        from { width: 100%; }
        to { width: 0%; }
    }
    /* Confirmation Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    .modal-overlay.show {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        width: 90%;
        max-width: 450px;
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }
    .modal-overlay.show .modal-content {
        transform: scale(1);
    }
</style>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Categories</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Add New Category</h2>
                <form action="<?= BASE_URL ?>/admin/ticket_categories/store" method="POST">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <button type="submit" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-plus mr-2"></i>
                        Add
                    </button>
                </form>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Existing Categories</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($data['ticket_categories'])): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No categories found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['ticket_categories'] as $index => $category): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $index + 1 ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($category['name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                            <form action="<?= BASE_URL ?>/admin/ticket_categories/delete/<?= $category['id'] ?>" method="POST" class="inline delete-form">
                                                <button type="button" class="text-red-600 hover:text-red-900 delete-btn" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Placeholder -->
<div id="toast-container"></div>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="modal-overlay">
    <div class="modal-content">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Are you sure?</h3>
        <p class="text-gray-600 mb-6">Deleting this category may affect related subcategories and tickets. This action cannot be undone.</p>
        <div class="flex justify-end gap-4">
            <button id="cancel-delete" class="px-6 py-2 rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
            <button id="confirm-delete" class="px-6 py-2 rounded-md text-white bg-red-600 hover:bg-red-700 font-medium">Delete</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Toast Notification Logic ---
    const toastContainer = document.getElementById('toast-container');
    const flashMessage = <?= json_encode($message) ?>;

    function showToast(text, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `${text}<div class="toast-progress"></div>`;
        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Animate out and remove
        setTimeout(() => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => toast.remove());
        }, 3000);
    }

    if (flashMessage) {
        showToast(flashMessage.text, flashMessage.type);
    }

    // --- Confirmation Modal Logic ---
    const modal = document.getElementById('confirmation-modal');
    const cancelBtn = document.getElementById('cancel-delete');
    const confirmBtn = document.getElementById('confirm-delete');
    let formToSubmit = null;

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            formToSubmit = this.closest('.delete-form');
            modal.classList.add('show');
        });
    });

    function closeModal() {
        modal.classList.remove('show');
        formToSubmit = null;
    }

    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    confirmBtn.addEventListener('click', function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
 