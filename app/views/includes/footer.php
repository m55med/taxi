

            </main>



            <footer class="border-t bg-white px-4 py-3 text-center text-sm text-gray-500">

                &copy; <?= date('Y') ?> Taxi CS. All Rights Reserved.

            </footer>

        </div>

    </div>

    <!-- Notifications Modal and Handling -->

    <div x-data="notificationsComponent" x-init="init()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="modalOpen" @keydown.escape.window="modalOpen = false" style="display: none;">

        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            <!-- Background overlay -->

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="modalOpen = false" aria-hidden="true"></div>



            <!-- Modal panel -->

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

                    <div class="sm:flex sm:items-start">

                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">

                            <i class="fas fa-bell text-blue-600"></i>

                        </div>

                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">

                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">

                                Important Notifications

                            </h3>

                            <div class="mt-2">

                                <p class="text-sm text-gray-500">

                                    You have unread notifications that require your attention.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="border-t border-gray-200 px-4 py-4 sm:px-6 max-h-80 overflow-y-auto">

                    <template x-if="notifications.length === 0">

                        <p class="text-center text-gray-500 py-4">No new notifications.</p>

                    </template>

                    <template x-for="notification in notifications" :key="notification.id">

                        <div class="p-4 mb-2 bg-gray-50 rounded-lg border border-gray-200">

                            <h4 class="font-bold text-gray-800" x-text="notification.title"></h4>

                            <div class="text-sm text-gray-600 mt-1" x-html="notification.message"></div>

                            <div class="text-xs text-gray-400 mt-2" x-text="new Date(notification.created_at).toLocaleString()"></div>

                        </div>

                    </template>

                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">

                    <button @click="markAllAsReadAndClose" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">

                        Acknowledge & Close

                    </button>

                </div>

            </div>

        </div>

    </div>



    <?php require_once APPROOT . '/helpers/help_helper.php'; ?>

    <?php $helpVideoId = get_help_video_url(); ?>

    <?php if ($helpVideoId) : ?>

    <div x-data="helpButton('<?= $helpVideoId ?>')" class="fixed bottom-5 right-5 z-50">

        <!-- Floating Help Button -->

        <button @click="openModal()" class="bg-blue-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-blue-700 transition-transform transform hover:scale-110">

            <i class="fas fa-question text-xl"></i>

        </button>

        

        <!-- Help Modal -->

        <div x-show="modalOpen" @keydown.escape.window="closeModal()" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="display: none;">

            <div @click.away="closeModal()" class="bg-white rounded-lg shadow-2xl w-full max-w-4xl p-4 relative">

                <!-- Close Button -->

                <button @click="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">

                    <i class="fas fa-times text-2xl"></i>

                </button>



                <!-- Video Player -->

                <div class="aspect-w-16 aspect-h-9">

                    <iframe :src="videoUrl" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

                </div>

                

                <!-- Actions -->

                <div class="flex justify-end mt-4">

                    <a :href="'https://www.youtube.com/watch?v=' + videoId" target="_blank" rel="noopener noreferrer" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">

                        <i class="fab fa-youtube mr-2"></i>

                        Open on YouTube

                    </a>

                </div>

            </div>

        </div>

    </div>

    <?php endif; ?>



    <script>

    document.addEventListener('alpine:init', () => {

        Alpine.data('notificationsComponent', () => ({

            modalOpen: false,

            notifications: [],

            init() {

                this.fetchUnreadNotifications();

                setInterval(() => {

                    this.fetchUnreadNotifications();

                }, 30000); // 30 seconds

            },

            fetchUnreadNotifications() {

                fetch('<?= URLROOT ?>/notifications/getNavNotifications')

                    .then(response => response.json())

                    .then(data => {

                        if (data && data.unread_count > 0) {

                            this.notifications = data.notifications.filter(n => n.is_read === '0');

                            if (this.notifications.length > 0) {

                                this.modalOpen = true;

                            }

                        }

                    })

                    .catch(error => console.error('Error fetching notifications:', error));

            },

            markAllAsReadAndClose() {

                const notificationIds = this.notifications.map(n => n.id);

                if (notificationIds.length === 0) {

                    this.modalOpen = false;

                    return;

                }



                const promises = notificationIds.map(id => {

                    let formData = new FormData();

                    formData.append('notification_id', id);

                    // Add the current user's ID to the request

                    formData.append('user_id', '<?= $_SESSION['user_id'] ?? '' ?>');

                    return fetch('<?= URLROOT ?>/notifications/markRead', {

                        method: 'POST',

                        body: formData

                    });

                });

                

                Promise.all(promises)

                    .then(() => {

                        this.notifications = [];

                        this.modalOpen = false;

                    })

                    .catch(error => console.error('Error marking notifications as read:', error));

            }

        }));



        Alpine.data('helpButton', (videoId) => ({

            modalOpen: false,

            videoId: videoId,

            videoUrl: '',

            openModal() {

                this.videoUrl = `https://www.youtube.com/embed/${this.videoId}?autoplay=1`;

                this.modalOpen = true;

            },

            closeModal() {

                this.modalOpen = false;

                this.videoUrl = ''; // Stop the video

            }

        }));

    });

    </script>

</body>

</html>

