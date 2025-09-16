<?php include_once __DIR__ . '/../includes/header.php'; ?>





<div class="container mx-auto px-4 py-8">



    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Ticket #<?= htmlspecialchars($data['ticket']['ticket_number']) ?></h1>

    <!-- Debug Panel (Remove after testing) -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
    <div class="bg-yellow-100 border border-yellow-400 p-4 mb-6 rounded-lg">
        <h3 class="font-bold text-yellow-800 mb-2">Debug Information:</h3>
        <div class="text-sm text-yellow-700 space-y-1">
            <p><strong>Ticket ID:</strong> <?= $data['ticket']['id'] ?? 'N/A' ?></p>
            <p><strong>Ticket Number:</strong> <?= $data['ticket']['ticket_number'] ?? 'N/A' ?></p>
            <p><strong>Platform ID:</strong> <?= $data['ticket']['platform_id'] ?? 'N/A' ?></p>
            <p><strong>Category ID:</strong> <?= $data['ticket']['category_id'] ?? 'N/A' ?></p>
            <p><strong>Subcategory ID:</strong> <?= $data['ticket']['subcategory_id'] ?? 'N/A' ?></p>
            <p><strong>Code ID:</strong> <?= $data['ticket']['code_id'] ?? 'N/A' ?></p>
            <p><strong>Country ID:</strong> <?= $data['ticket']['country_id'] ?? 'N/A' ?></p>
            <p><strong>Is VIP:</strong> <?= ($data['ticket']['is_vip'] ?? 0) ? 'Yes' : 'No' ?></p>
            <p><strong>Edited By:</strong> <?= $data['ticket']['edited_by'] ?? 'N/A' ?></p>
            <p><strong>Current User ID:</strong> <?= $data['current_user_id'] ?? 'N/A' ?></p>
            <p><strong>User Role:</strong> <?= $data['user_role'] ?? 'N/A' ?></p>
        </div>
        <div class="mt-2">
            <a href="?debug=1" class="text-xs bg-yellow-200 px-2 py-1 rounded">Refresh Debug</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($data['ticket']['edited_by']) && isset($data['current_user_id']) && $data['ticket']['edited_by'] == $data['current_user_id']): ?>
        <div class="max-w-4xl mx-auto mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                <span class="text-green-800 font-medium">You are the original creator of this ticket and can edit it.</span>
            </div>
        </div>
    <?php elseif (isset($data['user_role']) && in_array(strtolower($data['user_role']), ['admin', 'quality_manager', 'quality'])): ?>
        <div class="max-w-4xl mx-auto mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                <span class="text-blue-800 font-medium">You have admin privileges and can edit any ticket.</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Debug Information (Remove after testing) -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
    <div class="max-w-4xl mx-auto mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="font-bold text-yellow-800 mb-2">Debug Information:</h3>
        <div class="text-sm text-yellow-700">
            <p><strong>Current User ID:</strong> <?= $data['current_user_id'] ?? 'Not set' ?></p>
            <p><strong>User Role:</strong> <?= $data['user_role'] ?? 'Not set' ?></p>
            <p><strong>Ticket Edited By:</strong> <?= $data['ticket']['edited_by'] ?? 'Not set' ?></p>
            <p><strong>Ticket ID:</strong> <?= $data['ticket']['id'] ?? 'Not set' ?></p>
            <p><strong>Ticket Number:</strong> <?= $data['ticket']['ticket_number'] ?? 'Not set' ?></p>
        </div>
    </div>
    <?php endif; ?>







    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-6">



        <form action="<?= BASE_URL ?>/tickets/update/<?= $data['ticket']['id'] ?>" method="POST">



            



            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">



                <!-- Platform -->



                <div>



                    <label for="platform_id" class="block text-sm font-medium text-gray-700">Platform</label>



                    <select name="platform_id" id="platform_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">



                        <?php foreach ($data['platforms'] as $platform): ?>



                            <option value="<?= $platform['id'] ?>" <?= ($platform['id'] == $data['ticket']['platform_id']) ? 'selected' : '' ?>>



                                <?= htmlspecialchars($platform['name']) ?>



                            </option>



                        <?php endforeach; ?>



                    </select>



                </div>







                <!-- Phone Number -->



                <div>



                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>



                    <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($data['ticket']['phone']) ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">



                </div>







                <!-- Category -->



                <div>



                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>



                    <select name="category_id" id="category_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">



                        <?php foreach ($data['categories'] as $category): ?>



                            <option value="<?= $category['id'] ?>" <?= ($category['id'] == $data['ticket']['category_id']) ? 'selected' : '' ?>>



                                <?= htmlspecialchars($category['name']) ?>



                            </option>



                        <?php endforeach; ?>



                    </select>



                </div>







                <!-- Subcategory -->



                <div>



                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Subcategory</label>



                    <select name="subcategory_id" id="subcategory_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">



                        <!-- Options will be loaded by JavaScript -->



                    </select>



                </div>







                <!-- Code -->



                <div>



                    <label for="code_id" class="block text-sm font-medium text-gray-700">Code</label>



                    <select name="code_id" id="code_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">



                        <!-- Options will be loaded by JavaScript -->



                    </select>



                </div>







                <!-- Country -->



                <div>



                    <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>



                    <select name="country_id" id="country_id" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">



                        <?php foreach ($data['countries'] as $country): ?>



                            <option value="<?= $country->id ?>" <?= ($country->id == $data['ticket']['country_id']) ? 'selected' : '' ?>>



                                <?= htmlspecialchars($country->name) ?>



                            </option>



                        <?php endforeach; ?>



                    </select>



                </div>







                <!-- Assigned Team Leader -->

                <div>

                    <label for="assigned_team_leader_id" class="block text-sm font-medium text-gray-700">Assigned Team Leader</label>

                    <input type="text" value="<?= htmlspecialchars($data['ticket']['assigned_team_leader_name'] ?? 'N/A') ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm" disabled>

                    <input type="hidden" name="assigned_team_leader_id" value="<?= $data['ticket']['assigned_team_leader_id'] ?>">

                </div>



                <!-- VIP Ticket -->

                <div class="flex items-center">



                    <input type="checkbox" name="is_vip" id="is_vip" value="1" <?= ($data['ticket']['is_vip'] ?? 0) ? 'checked' : '' ?> class="h-4 w-4 text-indigo-600 border-gray-300 rounded">



                    <label for="is_vip" class="ml-2 block text-sm text-gray-900">VIP Ticket</label>



                </div>



            </div>



            



            <!-- Notes -->



            <div class="mt-6">



                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>



                <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm"><?= htmlspecialchars($data['ticket']['notes']) ?></textarea>



            </div>







            <div class="mt-6 flex justify-end gap-4">



                <a href="<?= BASE_URL ?>/tickets/view/<?= $data['ticket']['ticket_id'] ?>" class="px-6 py-2 rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium">Cancel</a>



                <button type="submit" class="px-6 py-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700 font-medium">



                    Update Ticket



                </button>



            </div>



        </form>



    </div>



</div>







<script>



    document.addEventListener('DOMContentLoaded', function() {



        const categorySelect = document.getElementById('category_id');



        const subcategorySelect = document.getElementById('subcategory_id');



        const codeSelect = document.getElementById('code_id');







        const initialSubcategoryId = <?= json_encode($data['ticket']['subcategory_id']) ?>;



        const initialCodeId = <?= json_encode($data['ticket']['code_id']) ?>;







        function fetchSubcategories(categoryId, selectedId = null) {



            if (!categoryId) {



                subcategorySelect.innerHTML = '<option value="">Select a category first</option>';



                codeSelect.innerHTML = '<option value="">Select a subcategory first</option>';



                return;



            }



            fetch(`<?= BASE_URL ?>/create_ticket/subcategories/${categoryId}`)



                .then(response => response.json())



                .then(data => {



                    subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';



                    data.forEach(subcategory => {



                        const option = new Option(subcategory.name, subcategory.id);



                        if (selectedId && subcategory.id == selectedId) {



                            option.selected = true;



                        }



                        subcategorySelect.add(option);



                    });



                    // If a subcategory was pre-selected, automatically fetch the codes for it.



                    if (selectedId) {



                        fetchCodes(selectedId, initialCodeId);



                    }



                });



        }







        function fetchCodes(subcategoryId, selectedId = null) {



            if (!subcategoryId) {



                codeSelect.innerHTML = '<option value="">Select a subcategory first</option>';



                return;



            }



            fetch(`<?= BASE_URL ?>/create_ticket/codes/${subcategoryId}`)



                .then(response => response.json())



                .then(data => {



                    codeSelect.innerHTML = '<option value="">Select a code</option>';



                    data.forEach(code => {



                        const option = new Option(code.name, code.id);



                        if (selectedId && code.id == selectedId) {



                            option.selected = true;



                        }



                        codeSelect.add(option);



                    });



                });



        }







        categorySelect.addEventListener('change', () => {



            // When user changes category, fetch subcategories, then clear codes.



            fetchSubcategories(categorySelect.value);



            codeSelect.innerHTML = '<option value="">Select a subcategory first</option>';



        });







        subcategorySelect.addEventListener('change', () => {



            // When user changes subcategory, fetch codes.



            fetchCodes(subcategorySelect.value);



        });







        // Initial load



        fetchSubcategories(categorySelect.value, initialSubcategoryId);



    });



</script>







<!-- Edit Logs Section -->
<div class="max-w-4xl mx-auto mt-8 bg-white rounded-lg shadow-sm p-6">

    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">

        <i class="fas fa-history text-gray-500 mr-3"></i>

        سجلات التعديلات والحذوفات

    </h2>

    <?php
    // Get logs for this ticket
    $ticketLogs = $data['listingModel']->getTicketLogs($data['ticket']['ticket_id']);
    if (!empty($ticketLogs)):
        $title = 'سجلات التذكرة';
        $compact = false;
        include __DIR__ . '/../listings/partials/ticket_logs.php';
    else:
    ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-history text-gray-300 text-3xl mb-3"></i>
            <p class="text-lg">لا توجد سجلات تعديلات</p>
            <p class="text-sm text-gray-400">سيتم عرض أي تعديلات أو حذف مستقبلاً هنا</p>
        </div>
    <?php endif; ?>

</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>



