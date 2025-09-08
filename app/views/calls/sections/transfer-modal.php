<!-- Transfer Modal -->
<div id="transferModal"
    class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center animated">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md m-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Transfer Driver</h3>
            <button onclick="hideTransferModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <form id="transferForm" class="space-y-6">
            <input type="hidden" name="driver_id" value="<?= $data['driver']->id ?? '' ?>">

            <div>
                <label for="transferToUser" class="block text-sm font-medium text-gray-700 mb-2">Transfer To</label>
                <select id="transferToUser" name="to_user_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a user...</option>
                    <?php foreach (($data['users'] ?? []) as $user): ?>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['username']) ?>
                                <?= ($user['is_online'] ?? false) ? '(Online)' : '' ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="transferNote" class="block text-sm font-medium text-gray-700 mb-2">Transfer Note</label>
                <textarea id="transferNote" name="note" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter the reason for the transfer..."></textarea>
            </div>

            <div class="flex justify-end pt-4 gap-4">
                <button type="button" onclick="hideTransferModal()"
                    class="bg-gray-100 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-semibold flex items-center">
                    <i class="fas fa-check mr-2"></i>
                    Confirm Transfer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log("✅ transfer.js loaded");

        const TransferModule = {
            init() {
                this.modal = document.getElementById('transferModal');
                this.form = document.getElementById('transferForm');
                if (this.modal) this.initializeModal();
                if (this.form) this.initializeForm();
            },

            initializeModal() {
                window.showTransferModal = () => this.modal.classList.remove('hidden');
                window.hideTransferModal = () => {
                    this.modal.classList.add('hidden');
                    if (this.form) this.form.reset();
                };
                this.modal.addEventListener('click', (e) => {
                    if (e.target === this.modal) window.hideTransferModal();
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                        window.hideTransferModal();
                    }
                });
            },

            async initializeForm() {
                this.form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(this.form);
                    const submitButton = this.form.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري التحويل...';

                    try {
                        const response = await fetch(`${URLROOT}/calls/assign`, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Server Error');
                        if (data.success) {
                            showToast(data.message || 'تم تحويل السائق بنجاح', 'success');
                            window.hideTransferModal();
                            setTimeout(() => window.location.href = `${URLROOT}/calls`, 500);
                        } else {
                            throw new Error(data.message || 'فشل في تحويل السائق');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast(error.message || 'حدث خطأ أثناء تحويل السائق', 'error');
                    } finally {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'تأكيد التحويل';
                    }
                });
            }
        };

        TransferModule.init();
    });
</script>