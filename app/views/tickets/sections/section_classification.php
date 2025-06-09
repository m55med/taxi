<!-- Section 2: Classification -->
<div class="form-section">
    <h2 class="section-title">2. تصنيف المشكلة</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Category, Subcategory, Code selectors -->
        <div>
            <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">التصنيف الرئيسي <span class="text-red-500">*</span></label>
            <select id="category_id" name="category_id" class="form-select block w-full" required>
                <option value="" disabled selected>اختر التصنيف</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="subcategory_id" class="block text-sm font-semibold text-gray-700 mb-2">التصنيف الفرعي <span class="text-red-500">*</span></label>
            <select id="subcategory_id" name="subcategory_id" class="form-select block w-full" disabled required>
                <option value="">اختر التصنيف الرئيسي أولاً</option>
            </select>
        </div>
        <div>
            <label for="code_id" class="block text-sm font-semibold text-gray-700 mb-2">الكود <span class="text-red-500">*</span></label>
            <select id="code_id" name="code_id" class="form-select block w-full" disabled required>
                <option value="">اختر التصنيف الفرعي أولاً</option>
            </select>
        </div>
    </div>
</div> 