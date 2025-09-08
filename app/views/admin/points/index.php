<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['points_message'])) {
    $flashMessage = [
        'message' => $_SESSION['points_message'],
        'type' => $_SESSION['points_message_type'] ?? 'success'
    ];
    unset($_SESSION['points_message'], $_SESSION['points_message_type']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>
<div x-data="pointsPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed top-5 right-5 z-50">
        <div :class="{
            'bg-green-500': toast.type === 'success',
            'bg-red-500': toast.type === 'error'
        }" class="text-white px-6 py-3 rounded-lg shadow-md flex items-center">
            <i :class="{
                'fas fa-check-circle': toast.type === 'success',
                'fas fa-times-circle': toast.type === 'error'
            }" class="mr-3"></i>
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Points System Management</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Section for Ticket Code Points -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Ticket Code Points</h2>
                
                <form action="<?= URLROOT ?>/admin/points/setTicketPoints" method="POST" x-data="{ code_id: null }" @option-selected.window="if(event.detail.model === 'code_id') code_id = event.detail.value">
                    <input type="hidden" name="code_id" :value="code_id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="code_id" class="block text-sm font-medium text-gray-700">Code</label>
                            <div x-data="searchableSelect(<?= htmlspecialchars(json_encode($data['ticket_codes']), ENT_QUOTES, 'UTF-8') ?>)" 
                                 data-model-name="code_id"
                                 data-placeholder="Search and select a code..."
                                 class="relative mt-1">
                                <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                                    <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search..."></div>
                                    <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span></li></template>
                                        <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No codes found.</li></template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="points" class="block text-sm font-medium text-gray-700">Points</label>
                            <input type="number" step="0.01" name="points" id="points" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm" placeholder="e.g., 10.5">
                        </div>
                        <div>
                            <label for="valid_from" class="block text-sm font-medium text-gray-700">Valid From</label>
                            <input type="date" name="valid_from" id="valid_from" required value="<?= date('Y-m-d') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_vip" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">VIP Ticket?</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="mt-4 w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i>Save Code Points
                    </button>
                </form>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Current Rules</h3>
                    <div class="max-h-64 overflow-y-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['ticket_points'])): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">No current rules.</td></tr>
                                <?php else: ?>
                                    <?php foreach($data['ticket_points'] as $rule): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($rule['code_name']) ?></td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $rule['is_vip'] ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                                                    <?= $rule['is_vip'] ? 'VIP' : 'Regular' ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm font-bold"><?= $rule['points'] ?></td>
                                            <td class="px-4 py-2 text-sm">
                                                <?= date('Y-m-d', strtotime($rule['valid_from'])) ?> → 
                                                <?php if($rule['valid_to']): ?>
                                                    <?= date('Y-m-d', strtotime($rule['valid_to'])) ?>
                                                <?php else: ?>
                                                    <span class="text-green-600 font-semibold">Active</span>
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

            <!-- Section for Call Points -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Call Points</h2>

                <div x-data="{ tab: 'outgoing' }">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <a href="#" @click.prevent="tab = 'outgoing'" :class="tab === 'outgoing' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Outgoing Calls
                            </a>
                            <a href="#" @click.prevent="tab = 'incoming'" :class="tab === 'incoming' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Incoming Calls
                            </a>
                        </nav>
                    </div>

                    <div x-show="tab === 'outgoing'" class="pt-4">
                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Set Points for Outgoing Calls</h3>
                        <form action="<?= URLROOT ?>/admin/points/setCallPoints" method="POST">
                            <input type="hidden" name="call_type" value="outgoing">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="call_points_outgoing" class="block text-sm font-medium text-gray-700">Points per Call</label>
                                    <input type="number" step="0.01" name="points" id="call_points_outgoing" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm" placeholder="e.g., 1.25">
                                </div>
                                <div>
                                    <label for="call_valid_from_outgoing" class="block text-sm font-medium text-gray-700">Valid From</label>
                                    <input type="date" name="valid_from" id="call_valid_from_outgoing" required value="<?= date('Y-m-d') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                            <button type="submit" class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                                <i class="fas fa-save mr-2"></i>Save Outgoing Call Points
                            </button>
                        </form>
                    </div>

                    <div x-show="tab === 'incoming'" class="pt-4">
                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Set Points for Incoming Calls</h3>
                        <form action="<?= URLROOT ?>/admin/points/setCallPoints" method="POST">
                            <input type="hidden" name="call_type" value="incoming">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="call_points_incoming" class="block text-sm font-medium text-gray-700">Points per Call</label>
                                    <input type="number" step="0.01" name="points" id="call_points_incoming" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm" placeholder="e.g., 0.75">
                                </div>
                                <div>
                                    <label for="call_valid_from_incoming" class="block text-sm font-medium text-gray-700">Valid From</label>
                                    <input type="date" name="valid_from" id="call_valid_from_incoming" required value="<?= date('Y-m-d') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                            <button type="submit" class="mt-4 w-full bg-teal-600 text-white py-2 px-4 rounded-md hover:bg-teal-700 transition duration-300">
                                <i class="fas fa-save mr-2"></i>Save Incoming Call Points
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-6">
                   <h3 class="text-lg font-semibold mb-2">Current Rules for Calls</h3>
                   <div class="max-h-64 overflow-y-auto border rounded-md">
                       <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                               <tr>
                                   <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                   <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                                   <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                               </tr>
                           </thead>
                           <tbody class="bg-white divide-y divide-gray-200">
                               <?php if (empty($data['call_points'])): ?>
                                   <tr><td colspan="3" class="text-center py-4 text-gray-500">No current rules.</td></tr>
                               <?php else: ?>
                                   <?php foreach($data['call_points'] as $rule): ?>
                                       <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm">
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $rule['call_type'] == 'incoming' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                                   <?= htmlspecialchars(ucfirst($rule['call_type'])) ?>
                                               </span>
                                           </td>
                                           <td class="px-4 py-2 text-sm font-bold"><?= $rule['points'] ?></td>
                                           <td class="px-4 py-2 text-sm">
                                               <?= date('Y-m-d', strtotime($rule['valid_from'])) ?> → 
                                               <?php if($rule['valid_to']): ?>
                                                   <?= date('Y-m-d', strtotime($rule['valid_to'])) ?>
                                               <?php else: ?>
                                                   <span class="text-green-600 font-semibold">Active</span>
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
</div>

<script>
    function pointsPage(flashMessage) {
        return {
            toast: {
                show: false,
                message: '',
                type: 'success'
            },
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 3000);
            },
            init() {
                if (flashMessage) {
                    this.showToast(flashMessage.message, flashMessage.type);
                }
            }
        }
    }
</script>
<script src="<?= URLROOT ?>/js/components/searchable-select.js?v=<?= time() ?>"></script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>