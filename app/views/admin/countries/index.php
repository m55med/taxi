<?php


$flashMessage = null;

if (isset($_SESSION['country_message'])) {

    $flashMessage = [

        'message' => $_SESSION['country_message'],

        'type' => $_SESSION['country_message_type'] ?? 'success'

    ];

    unset($_SESSION['country_message'], $_SESSION['country_message_type']);

}

?>

<script>

function countriesPage(flashMessage) {

    return {

        toast: { show: false, message: '', type: 'success' },

        modal: { show: false, id: null, name: '' },

        openModal(id, name) {

            this.modal.id = id;

            this.modal.name = name;

            this.modal.show = true;

        },

        showToast(message, type = 'success') {

            this.toast = { show: true, message, type };

            setTimeout(() => this.toast.show = false, 5000);

        },

        init() {

            if (flashMessage) {

                this.showToast(flashMessage.message, flashMessage.type);

            }

        }

    }

}

</script>

<div x-data="countriesPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()" x-cloak>

    <?php require_once __DIR__ . '/../../includes/header.php'; ?>



    <!-- Flash Message Toast -->

    <div class="toast-container fixed top-5 right-5 z-50">

        <div x-show="toast.show"

             x-transition:enter="transition ease-out duration-300"

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



    <!-- Delete Confirmation Modal -->

    <div x-show="modal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="modal.show = false">

            <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>

            <p class="mb-6">Are you sure you want to delete the country <strong x-text="modal.name"></strong>? This action cannot be undone.</p>

            <div class="flex justify-end space-x-4">

                <button @click="modal.show = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Cancel</button>

                <form action="<?= URLROOT ?>/admin/countries/delete" method="POST">

                    <input type="hidden" name="id" :value="modal.id">

                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Yes, Delete</button>

                </form>

            </div>

        </div>

    </div>



    <div class="container mx-auto px-4 py-8">

        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Countries</h1>



        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="md:col-span-1">

                <div class="bg-white rounded-lg shadow-md p-6">

                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Add New Country</h2>

                    <form action="<?= URLROOT ?>/admin/countries/store" method="POST">

                        <div>

                            <label for="name" class="block text-sm font-medium text-gray-700">Country Name</label>

                            <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g., Egypt, Saudi Arabia">

                        </div>

                        <button type="submit" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">

                            <i class="fas fa-plus mr-2"></i>

                            Add Country

                        </button>

                    </form>

                </div>

            </div>



            <div class="md:col-span-2">

                <div class="bg-white rounded-lg shadow-md">

                    <div class="px-6 py-4 border-b border-gray-200">

                        <h2 class="text-xl font-semibold text-gray-800">Existing Countries</h2>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>

                                </tr>

                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">

                                <?php if (empty($data['countries'])): ?>

                                    <tr>

                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">

                                            No countries found.

                                        </td>

                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($data['countries'] as $index => $country): ?>

                                        <tr>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $index + 1 ?></td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($country->name) ?></td>

                                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">

                                                <button @click="openModal(<?= $country->id ?>, '<?= htmlspecialchars($country->name) ?>')" class="text-red-600 hover:text-red-900" title="Delete Country">

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



    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</div> 