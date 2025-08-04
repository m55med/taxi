<?php include_once APPROOT . '/views/includes/header.php'; ?>

<!-- Link to the new Tailwind CSS stylesheet -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/dist/style.css">
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-screen-xl mx-auto">
        <div class="font-sans antialiased text-gray-800 bg-white rounded-lg shadow-xl overflow-hidden"
            x-data="discussionsComponent()" x-init="init()">


            <!-- Loading State -->
            <template x-if="isLoading">
                <div class="flex justify-center items-center" style="height: 85vh;">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-4 text-lg">Loading Discussions...</p>
                    </div>
                </div>
            </template>

            <!-- Main Content -->
            <template x-if="!isLoading">
                <div class="flex" style="height: 85vh;">
                    <!-- Sidebar -->
                    <aside class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
                        <!-- Sidebar Header -->
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold">Discussions</h2>
                            <div class="relative mt-4">
                                <i
                                    class="fas fa-search absolute top-1/2 left-3 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" x-model="searchTerm" placeholder="Search ticket, name, or keyword..."
                                    class="w-full pl-10 pr-4 py-2 border rounded-full bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                            <div class="flex justify-center space-x-2 mt-4">
                                <button @click="filterStatus = 'all'"
                                    :class="{'bg-blue-500 text-white': filterStatus === 'all', 'bg-gray-200 text-gray-700': filterStatus !== 'all'}"
                                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition">All</button>
                                <button @click="filterStatus = 'open'"
                                    :class="{'bg-blue-500 text-white': filterStatus === 'open', 'bg-gray-200 text-gray-700': filterStatus !== 'open'}"
                                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition">Open</button>
                                <button @click="filterStatus = 'closed'"
                                    :class="{'bg-blue-500 text-white': filterStatus === 'closed', 'bg-gray-200 text-gray-700': filterStatus !== 'closed'}"
                                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition">Closed</button>
                            </div>
                        </div>

                        <!-- Discussions List -->
                        <div class="flex-1 overflow-y-auto">
                            <template x-if="filteredDiscussions.length === 0">
                                <p class="text-center text-gray-500 mt-8">No discussions found.</p>
                            </template>
                            <template x-for="discussion in filteredDiscussions" :key="discussion.id">
                                <div @click="selectDiscussion(discussion.id)"
                                    class="p-4 border-b border-gray-200 cursor-pointer hover:bg-blue-50 relative"
                                    :class="{'bg-blue-100 border-r-4 border-blue-500': selectedDiscussionId == discussion.id}">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-bold text-sm truncate pr-2" x-text="discussion.reason"></h3>
                                        <span class="text-xs text-gray-500 whitespace-nowrap"
                                            x-text="formatDateTime(discussion.last_activity_at)"></span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <span
                                            x-text="discussion.ticket_number ? `Ticket #${discussion.ticket_number}` : 'No Ticket'"></span>
                                        &bull;
                                        <span x-text="`By: ${discussion.opener_name}`"></span>
                                    </p>
                                    <div class="flex justify-start items-center mt-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full" :class="{
                                'bg-green-100 text-green-800': discussion.status === 'open',
                                'bg-red-100 text-red-800': discussion.status === 'closed'
                            }" x-text="discussion.status"></span>
                                    </div>
                                    <!-- Unread count badge -->
                                    <template x-if="discussion.unread_count > 0">
                                        <span x-text="discussion.unread_count"
                                            class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </aside>

                    <!-- Main Content -->
                    <main class="w-2/3 flex flex-col bg-gray-50">
                        <template x-if="!selectedDiscussion && discussions.length > 0">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-comments fa-4x mb-4"></i>
                                    <h2 class="text-2xl font-semibold">Welcome to Discussions</h2>
                                    <p>Select a conversation from the left to get started.</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!selectedDiscussion && discussions.length === 0">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-comment-slash fa-4x mb-4"></i>
                                    <h2 class="text-2xl font-semibold">No Discussions Yet</h2>
                                    <p>There are no discussions to show at the moment.</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedDiscussion">
                            <div class="flex flex-col h-full">
                                <!-- Header -->
                                <div class="p-4 bg-white border-b border-gray-200 flex justify-between items-center">
                                    <div>
                                        <h2 class="text-lg font-bold" x-text="selectedDiscussion.reason"></h2>
                                        <div class="text-sm text-gray-500 mt-2 flex items-center space-x-4">
                                            <!-- Link to related item -->
                                            <template
                                                x-if="selectedDiscussion.reviewable_type_simple === 'ticket_detail' && selectedDiscussion.ticket_id">
                                                <a :href="`<?= URLROOT ?>/tickets/view/${selectedDiscussion.ticket_id}`"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline font-semibold flex items-center space-x-1">
                                                    <i class="fas fa-ticket-alt"></i>
                                                    <span x-text="`Ticket #${selectedDiscussion.ticket_number}`"></span>
                                                </a>
                                            </template>
                                            <template
                                                x-if="selectedDiscussion.reviewable_type_simple === 'driver_call' && selectedDiscussion.driver_id">
                                                <a :href="`<?= URLROOT ?>/drivers/details/${selectedDiscussion.driver_id}`"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline font-semibold flex items-center space-x-1">
                                                    <i class="fas fa-headset"></i>
                                                    <span>View Call Context</span>
                                                </a>
                                            </template>

                                            <!-- Reviewer Info -->
                                            <template x-if="selectedDiscussion.reviewer_name">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-user-check text-gray-400"></i>
                                                    <span>Reviewed by: <span class="font-semibold text-gray-700"
                                                            x-text="selectedDiscussion.reviewer_name"></span></span>
                                                </div>
                                            </template>

                                            <!-- Review Score -->
                                            <template x-if="selectedDiscussion.review_score !== null">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-star text-yellow-500"></i>
                                                    <span>Score: <span class="font-semibold text-gray-700"
                                                            x-text="selectedDiscussion.review_score + ' / 100'"></span></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Show close button only if the discussion is open and user has permission -->
                                        <template x-if="canUserCloseDiscussion">
                                            <button @click="showCloseConfirmation = true"
                                                class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm font-semibold flex items-center space-x-2">
                                                <i class="fas fa-lock"></i>
                                                <span>Close Discussion</span>
                                            </button>
                                        </template>

                                        <!-- Show Re-open Button for Authorized Roles on Closed Discussions -->
                                        <template
                                            x-if="selectedDiscussion && selectedDiscussion.status === 'closed' && ['admin', 'quality_manager', 'Team_leader', 'developer'].includes(currentUser.role)">
                                            <button @click="showReopenConfirmation = true"
                                                class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-sm font-semibold flex items-center space-x-2">
                                                <i class="fas fa-lock-open"></i>
                                                <span>Re-open Discussion</span>
                                            </button>
                                        </template>

                                        <!-- Show Closed Badge for Unauthorized Roles on Closed Discussions -->
                                        <template
                                            x-if="selectedDiscussion && selectedDiscussion.status === 'closed' && !['admin', 'quality_manager', 'Team_leader', 'developer'].includes(currentUser.role)">
                                            <div
                                                class="bg-red-100 text-red-800 px-4 py-2 rounded-lg text-sm font-semibold flex items-center space-x-2">
                                                <i class="fas fa-lock"></i>
                                                <span>Closed</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Chat Body -->
                                <div class="flex-1 p-4 overflow-y-auto" x-ref="chatBody">
                                    <template x-for="reply in selectedDiscussion.replies" :key="reply.id">
                                        <div class="mb-4">
                                            <div class="flex items-start"
                                                :class="{'justify-end': reply.user_id == currentUser.id}">
                                                <div class="flex-shrink-0 mr-3"
                                                    x-show="reply.user_id != currentUser.id">
                                                    <img class="w-8 h-8 rounded-full"
                                                        :src="`https://ui-avatars.com/api/?name=${reply.user_name}&background=random`"
                                                        :alt="reply.user_name">
                                                </div>

                                                <div class="max-w-md p-3 rounded-lg" :class="{
                                                    'bg-blue-500 text-white': reply.user_id == currentUser.id,
                                                    'bg-white border': reply.user_id != currentUser.id
                                    }">
                                                    <p class="text-sm" x-text="reply.message"></p>
                                                    <div class="text-xs mt-2"
                                                        :class="{'text-blue-200': reply.user_id == currentUser.id, 'text-gray-500': reply.user_id != currentUser.id}"
                                                        x-text="`${reply.user_name} • ${formatDateTime(reply.created_at)}`">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Reply Form -->
                                <div class="p-4 bg-white border-t border-gray-200">
                                    <form @submit.prevent="submitReply">
                                        <div class="relative">
                                            <input type="text" x-model="newReplyMessage"
                                                placeholder="Type your reply..."
                                                class="w-full pl-4 pr-12 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400"
                                                :disabled="selectedDiscussion.status === 'closed'">
                                            <button type="submit"
                                                class="absolute top-1/2 right-3 transform -translate-y-1/2 text-blue-500 hover:text-blue-700 disabled:text-gray-400"
                                                :disabled="newReplyMessage.trim() === '' || selectedDiscussion.status === 'closed'">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </main>
                </div>
            </template>
            <!-- Close Confirmation Modal -->
            <template x-if="showCloseConfirmation">
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full">
                        <h3 class="text-lg font-bold">Confirm Closure</h3>
                        <p class="text-gray-600 mt-2">Are you sure you want to close this discussion? This action cannot
                            be undone.</p>
                        <div class="flex justify-end space-x-4 mt-6">
                            <button @click="showCloseConfirmation = false"
                                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                            <button @click="closeDiscussion()"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Close
                                Discussion</button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Re-open Confirmation Modal -->
            <template x-if="showReopenConfirmation">
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
                    style="z-index: 9999;">
                    <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full">
                        <h3 class="text-lg font-bold">Confirm Re-opening</h3>
                        <p class="text-gray-600 mt-2">Are you sure you want to re-open this discussion? It will become
                            active again.
                        </p>
                        <div class="flex justify-end space-x-4 mt-6">
                            <button @click="showReopenConfirmation = false"
                                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                            <button @click="reopenDiscussion()"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Re-open</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script src="<?= URLROOT ?>/js/discussions/main.js"></script>
<?php include_once APPROOT . '/views/includes/footer.php'; ?>