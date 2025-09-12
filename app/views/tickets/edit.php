<?php include_once __DIR__ . '/../includes/header.php'; ?>





<div class="container mx-auto px-4 py-8">



    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Ticket #<?= htmlspecialchars($data['ticket']['ticket_number']) ?></h1>

    <?php if (isset($data['ticket']['edited_by']) && isset($data['current_user_id']) && $data['ticket']['edited_by'] == $data['current_user_id']): ?>
        <div class="max-w-4xl mx-auto mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                <span class="text-green-800 font-medium">You are the original creator of this ticket and can edit it.</span>
            </div>
        </div>
    <?php elseif (isset($data['user_role']) && in_array($data['user_role'], ['admin', 'quality_manager', 'Quality'])): ?>
        <div class="max-w-4xl mx-auto mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                <span class="text-blue-800 font-medium">You have admin privileges and can edit any ticket.</span>
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







<?php include_once __DIR__ . '/../includes/footer.php'; ?>



