<?php if (isset($data['ticket']) && !empty($data['ticket'])): ?>
<div class="mb-6 bg-white p-4 rounded-lg shadow-md border border-gray-200">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        
        <!-- Ticket Info -->
        <div class="flex items-center">
            <h2 class="text-lg font-semibold text-gray-800">Ticket Actions</h2>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2">
            
            <!-- Edit Logs Button (Admin Only) -->
            <?php if (\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('developer')): ?>
                <a href="<?= BASE_URL ?>/tickets/edit-logs/<?= $data['ticket']['id'] ?>" 
                   class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-4 rounded-md text-sm transition duration-150 flex items-center">
                    <i class="fas fa-history mr-2"></i>
                    View Edit Logs
                </a>
            <?php endif; ?>

            <!-- Other action buttons can be added here -->
            
        </div>
    </div>
</div>
<?php endif; ?>
