<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['document_type_message'])) {
    $flashMessage = [
        'message' => $_SESSION['document_type_message'],
        'type' => $_SESSION['document_type_message_type'] ?? 'success'
    ];
    unset($_SESSION['document_type_message'], $_SESSION['document_type_message_type']);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Document Types</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .toast-container {
            z-index: 1000;
        }
    </style>
</head>

<body class="bg-gray-100" x-data="documentTypesPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)"
    x-init="init()" x-cloak>

    <?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <!-- Flash Message Toast -->
    <div class="toast-container fixed top-5 right-5">
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="p-4 rounded-lg shadow-lg text-white font-semibold"
            :class="{ 'bg-green-500': toast.type === 'success', 'bg-red-500': toast.type === 'error' }">
            <p x-text="toast.message"></p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Document Types</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Add/Edit Form Card -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4"
                        x-text="form.id ? 'Edit Document Type' : 'Add New Document Type'">Add New Document Type</h2>
                    <form @submit.prevent="submitForm">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Document Type Name</label>
                            <input type="text" name="name" id="name" x-model="form.name" required
                                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="flex items-center mt-4 space-x-2">
                            <button type="submit"
                                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center">
                                <i class="fas" :class="form.id ? 'fa-save' : 'fa-plus'"></i>
                                <span class="ml-2" x-text="form.id ? 'Save Changes' : 'Add Type'"></span>
                            </button>
                            <button type="button" x-show="form.id" @click="resetForm()"
                                class="w-auto bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Document Types List -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Existing Document Types</h2>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($data['document_types'])): ?>
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No document types found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['document_types'] as $index => $docType): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?= $index + 1 ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($docType['name']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                                    <button @click="editType(<?= htmlspecialchars(json_encode($docType)) ?>)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        @click="confirmDelete(<?= $docType['id'] ?>, '<?= htmlspecialchars(addslashes($docType['name'])) ?>')"
                                                        class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
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
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @keydown.escape.window="deleteModal.open = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="deleteModal.open = false">
            <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
            <p class="mb-6" x-html="deleteModal.message"></p>
            <div class="flex justify-end space-x-4">
                <button @click="deleteModal.open = false"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Cancel</button>
                <form :action="deleteModal.actionUrl" method="POST" class="inline">
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Yes,
                        Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Add/Edit -->
    <form x-ref="form" action="" method="POST" class="hidden">
        <input type="hidden" name="id" x-model="form.id">
        <input type="hidden" name="name" x-model="form.name">
    </form>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        function documentTypesPage(flashMessage) {
            return {
                toast: { show: false, message: '', type: 'success' },
                deleteModal: { open: false, message: '', actionUrl: '' },
                form: { id: null, name: '' },

                init() {
                    if (flashMessage) {
                        this.showToast(flashMessage.message, flashMessage.type);
                    }
                },

                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => this.toast.show = false, 4000);
                },

                editType(docType) {
                    this.form.id = docType.id;
                    this.form.name = docType.name;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                resetForm() {
                    this.form.id = null;
                    this.form.name = '';
                },

                submitForm() {
                    const formRef = this.$refs.form;
                    formRef.action = this.form.id
                        ? `<?= URLROOT ?>/admin/document_types/update`
                        : `<?= URLROOT ?>/admin/document_types/store`;
                    formRef.submit();
                },

                confirmDelete(id, name) {
                    this.deleteModal.message = `Are you sure you want to delete the document type "<strong>${name}</strong>"? This action cannot be undone.`;
                    this.deleteModal.actionUrl = `<?= URLROOT ?>/admin/document_types/delete/${id}`;
                    this.deleteModal.open = true;
                }
            }
        }
    </script>
</body>

</html>