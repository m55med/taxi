    </main>
    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4">
            <div class="text-center text-gray-600">
                <a href="<?php echo BASE_PATH; ?>/documentation/index" class="text-blue-600 hover:text-blue-800 hover:underline px-2">Documentation</a> |
                All rights reserved &copy; <?php echo date('Y'); ?> Taxi System
            </div>
        </div>
    </footer>
    
    <?php if (isset($mandatory_notifications) && !empty($mandatory_notifications)): ?>
    <!-- ============= MANDATORY NOTIFICATION MODAL (Now rendered conditionally) ============================ -->
    <div id="mandatory-notification-modal" 
         class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
         x-data="notificationModal(<?= htmlspecialchars(json_encode($mandatory_notifications, JSON_UNESCAPED_UNICODE)) ?>)" 
         x-init="showModal = true"
         x-show="showModal"
         x-transition.opacity>
        <div class="bg-white rounded-lg shadow-xl p-6 sm:p-8 w-11/12 max-w-2xl transform transition-all" 
             @click.away="markAllAsRead()"
             x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Important Updates & Notifications</h2>
            <div id="notification-content" class="prose max-w-none max-h-80 overflow-y-auto mb-6 pr-4 -mr-2 text-sm sm:text-base">
                <?php foreach($mandatory_notifications as $notification): ?>
                    <div class="notification-item mb-5 pb-5 border-b last:border-b-0">
                        <h3 class="font-bold text-lg mb-1"><?= htmlspecialchars($notification['title']) ?></h3>
                        <p class="text-xs text-gray-500 mb-3">Posted on: <?= date('F j, Y, g:i a', strtotime($notification['created_at'])) ?></p>
                        <div class="text-gray-700"><?= $notification['message'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-right">
                <button @click="markAllAsRead()"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200">
                    Acknowledge & Close
                </button>
            </div>
        </div>
    </div>
    
    <script>
    function notificationModal(notifications) {
        return {
            showModal: false,
            notifications: notifications,
            markAllAsRead() {
                const promises = this.notifications.map(n => {
                    const formData = new FormData();
                    formData.append('notification_id', n.id);
                    return fetch('<?= BASE_PATH ?>/notifications/markRead', {
                        method: 'POST',
                        body: formData
                    });
                });
    
                Promise.all(promises).then(() => {
                    this.showModal = false;
                    if (window.updateNavNotifications) {
                        window.updateNavNotifications();
                    }
                }).catch(err => console.error('Error marking notifications as read:', err));
            }
        }
    }
    </script>
    <?php endif; ?>

    <!-- Quill Editor for Discussions -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="<?= BASE_PATH ?>/js/quill-initializer.js"></script>

    <!-- Custom JavaScript -->
</body>
</html> 