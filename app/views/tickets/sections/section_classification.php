<!-- Section 2: Classification -->
<div class="form-section">
    <h2 class="section-title">2. Problem Classification</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Category, Subcategory, Code selectors -->
        <div>
            <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">Main Category <span class="text-red-500">*</span></label>
            <select id="category_id" name="category_id" class="form-select block w-full" required>
                <option value="" disabled selected>Select Category</option>
                <?php foreach (($data['categories'] ?? []) as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="subcategory_id" class="block text-sm font-semibold text-gray-700 mb-2">Sub-Category <span class="text-red-500">*</span></label>
            <select id="subcategory_id" name="subcategory_id" class="form-select block w-full" disabled required>
                <option value="">Select Main Category First</option>
            </select>
        </div>
        <div>
            <label for="code_id" class="block text-sm font-semibold text-gray-700 mb-2">Code <span class="text-red-500">*</span></label>
            <select id="code_id" name="code_id" class="form-select block w-full" disabled required>
                <option value="">Select Sub-Category First</option>
            </select>
        </div>
    </div>
</div> 