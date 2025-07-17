<?php
// Prepare flash message
$message = null;
if (!empty($_SESSION['role_message'])) {
    $message = [
        'message' => $_SESSION['role_message'],
        'type' => !empty($_SESSION['role_message_class']) ? $_SESSION['role_message_class'] : 'success'
    ];
    unset($_SESSION['role_message'], $_SESSION['role_message_class']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<!-- Professional Animated Flash Message -->
<?php if ($message): ?>
<div id="flash-message" 
     class="fixed top-5 right-5 z-50 w-full max-w-xs p-4 rounded-lg shadow-lg text-white 
            transform translate-x-full opacity-0 transition-all duration-500 ease-in-out
            <?= $message['type'] === 'success' ? 'bg-green-600' : 'bg-red-600' ?>">
    <div class="flex items-start">
        <!-- Icon -->
        <div class="flex-shrink-0">
            <?php if($message['type'] === 'success'): ?>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?php else: ?>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?php endif; ?>
        </div>
        <!-- Message -->
        <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium"><?= htmlspecialchars($message['message']) ?></p>
        </div>
        <!-- Close Button -->
        <div class="ml-4 flex-shrink-0 flex">
            <button id="flash-close-btn" class="inline-flex text-white opacity-70 hover:opacity-100 transition">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
        </div>
    </div>
    <!-- Progress Bar -->
    <div class="absolute bottom-0 left-0 h-1 bg-white/50" id="flash-progress"></div>
</div>
<?php endif; ?>


<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Roles</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Add New Role Form Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Add New Role</h2>
                <form action="/admin/roles/store" method="POST">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-center mt-4">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium flex items-center justify-center">
                            <i class="fas fa-plus"></i>
                            <span class="ml-2">Add Role</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Roles List -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Existing Roles</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($data['roles'])): ?>
                                <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No roles found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['roles'] as $role): ?>
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($role->id) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <form action="/admin/roles/update" method="POST" class="inline-form">
                                                <input type="hidden" name="id" value="<?= $role->id ?>">
                                                <input type="text" name="name" value="<?= htmlspecialchars($role->name) ?>" class="form-input-table" <?php if ($role->id == 1) echo 'disabled'; ?>>
                                        </td>
                                        <td class="px-6 py-4 text-left text-sm font-medium">
                                                <?php if ($role->id != 1): ?>
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Update"><i class="fas fa-save"></i></button>
                                                </form>
                                                <form action="/admin/roles/delete/<?= $role->id ?>" method="POST" class="inline-form" onsubmit="return showDeleteConfirm(event, '<?= htmlspecialchars(addslashes($role->name)) ?>', this);">
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                                <?php endif; ?>
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

<!-- Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" onclick="closeModal(event)">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" onclick="event.stopPropagation()">
        <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
        <p id="delete-confirm-message" class="mb-6"></p>
        <div class="flex justify-end space-x-4">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
            <button id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Yes, Delete</button>
        </div>
    </div>
</div>


<script>
// Professional auto-hiding flash messages with animation
document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flash-message');
    if (!flashMessage) return;

    const progressBar = document.getElementById('flash-progress');
    const closeBtn = document.getElementById('flash-close-btn');
    const duration = 4000; // 4 seconds
    let timeoutId;

    const hideToast = () => {
        // Animate out: slide right and fade out
        flashMessage.classList.add('translate-x-full', 'opacity-0');
        // Clean up after transition
        flashMessage.addEventListener('transitionend', () => {
            flashMessage.remove();
        }, { once: true });
    };

    const startTimer = () => {
        progressBar.style.transition = `width ${duration}ms linear`;
        progressBar.style.width = '0%';
        timeoutId = setTimeout(hideToast, duration);
    };

    // Animate in: slide from right and fade in
    setTimeout(() => {
        flashMessage.classList.remove('translate-x-full', 'opacity-0');
        startTimer();
    }, 100);

    // Manual close
    closeBtn.addEventListener('click', () => {
        clearTimeout(timeoutId);
        hideToast();
    });
});

let formToSubmit = null;
const modal = document.getElementById('delete-confirm-modal');
const confirmBtn = document.getElementById('confirm-delete-btn');
const messageEl = document.getElementById('delete-confirm-message');

function showDeleteConfirm(event, roleName, form) {
    event.preventDefault();
    formToSubmit = form;
    messageEl.innerHTML = `Are you sure you want to delete the role "<strong>${roleName}</strong>"? This action cannot be undone.`;
    modal.classList.remove('hidden');
    return false; // Prevent form submission
}

function closeModal() {
    if (modal) {
        modal.classList.add('hidden');
    }
    formToSubmit = null;
}

// Handle modal background click
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

confirmBtn.addEventListener('click', function() {
    if (formToSubmit) {
        formToSubmit.submit();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeModal();
    }
});

</script>

<style>
.inline-form {
    display: inline;
}
.form-input-table {
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 5px;
    width: calc(100% - 20px);
}
.form-input-table:disabled {
    background-color: #f8f8f8;
    color: #888;
    border-color: #ddd;
}
</style>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>