<!-- Section 3: Assignment & Notes -->
<div class="form-section">
    <h2 class="section-title">3. الإسناد والملاحظات</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="assigned_team_leader_id" class="block text-sm font-semibold text-gray-700 mb-2">قائد الفريق (تلقائي)</label>
            <select id="assigned_team_leader_id" name="assigned_team_leader_id" class="form-select block w-full" disabled>
                <option value="">يتم تحديده تلقائياً</option>
                 <?php foreach ($teamLeaders as $leader): ?>
                    <option value="<?= $leader['id'] ?>"><?= htmlspecialchars($leader['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات</label>
            <textarea id="notes" name="notes" rows="4" class="form-textarea block w-full" placeholder="أضف أي ملاحظات إضافية هنا..."></textarea>
        </div>
    </div>
</div> 