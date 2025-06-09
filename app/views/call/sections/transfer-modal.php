<!-- Transfer Modal -->
<div id="transferModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 animated">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl p-6 w-96">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">تحويل السائق</h3>
            <button onclick="hideTransferModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="transferForm" class="space-y-4">
            <input type="hidden" name="driver_id" value="<?= $driver['id'] ?? '' ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تحويل إلى</label>
                <select name="to_user_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['username']) ?>
                                <?= $user['is_online'] ? '(متصل)' : '' ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات التحويل</label>
                <textarea name="note" rows="2" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md"
                    placeholder="أدخل سبب التحويل هنا..."></textarea>
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="hideTransferModal()"
                    class="ml-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                    إلغاء
                </button>
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    تأكيد التحويل
                </button>
            </div>
        </form>
    </div>
</div> 