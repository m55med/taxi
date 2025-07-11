function discussionsComponent() {
    return {
        discussions: [],
        currentUser: {},
        selectedDiscussionId: null,
        searchTerm: '',
        filterStatus: 'all', // 'all', 'open', 'closed'
        newReplyMessage: '',
        showCloseConfirmation: false,
        showReopenConfirmation: false, // For reopen modal
        canUserCloseDiscussion: false,
        isLoading: true,

        async init() {
            await this.fetchDiscussions();
            
            this.$watch('selectedDiscussionId', () => {
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.updateActionButtonsVisibility();
                });
            });

            this.$watch('filterStatus', () => {
                // If the currently selected discussion is no longer in the filtered list,
                // select the first one from the new list.
                const isSelectedVisible = this.filteredDiscussions.some(d => d.id == this.selectedDiscussionId);
                if (!isSelectedVisible && this.filteredDiscussions.length > 0) {
                    this.selectDiscussion(this.filteredDiscussions[0].id);
                } else if (this.filteredDiscussions.length === 0) {
                    this.selectedDiscussionId = null;
                }
            });
        },
        
        async fetchDiscussions() {
            this.isLoading = true;
            try {
                const response = await fetch('/taxi/discussions/get');
                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }
                const data = await response.json();
                this.discussions = data.discussions;
                this.currentUser = data.currentUser;
                
                if (this.filteredDiscussions.length > 0) {
                    const hash = window.location.hash;
                    if (hash && hash.startsWith('#discussion-')) {
                        const idFromHash = parseInt(hash.substring('#discussion-'.length), 10);
                        if (this.discussions.some(d => d.id === idFromHash)) {
                            this.selectedDiscussionId = idFromHash;
                        }
                    } 
                    if (!this.selectedDiscussionId) {
                        this.selectedDiscussionId = this.filteredDiscussions[0].id;
                    }
                }
                this.updateActionButtonsVisibility();

            } catch (error) {
                console.error('Failed to fetch discussions:', error);
            } finally {
                this.isLoading = false;
            }
        },

        updateActionButtonsVisibility() {
            if (!this.selectedDiscussion) {
                this.canUserCloseDiscussion = false;
                return;
            }
            const canCloseRoles = ['admin', 'quality_manager', 'Team_leader'];
            this.canUserCloseDiscussion = this.selectedDiscussion.status === 'open' && canCloseRoles.includes(this.currentUser.role);
        },

        get filteredDiscussions() {
            const filtered = this.discussions.filter(d => {
                const statusMatch = this.filterStatus === 'all' || d.status === this.filterStatus;
                const search = this.searchTerm.toLowerCase().trim();
                const searchMatch = !search ||
                    (d.reason && d.reason.toLowerCase().includes(search)) ||
                    (d.ticket_number && d.ticket_number.toString().includes(search)) ||
                    (d.opener_name && d.opener_name.toLowerCase().includes(search)) ||
                    (d.reviewer_name && d.reviewer_name.toLowerCase().includes(search)) ||
                    (d.replies.some(r => r.message.toLowerCase().includes(search)));
                return statusMatch && searchMatch;
            });
            return filtered.sort((a, b) => new Date(b.last_activity_at) - new Date(a.last_activity_at));
        },
        
        get selectedDiscussion() {
            if (!this.selectedDiscussionId) return null;
            let discussion = this.discussions.find(d => d.id == this.selectedDiscussionId);
            return discussion || null;
        },

        selectDiscussion(id) {
            this.selectedDiscussionId = id;
            window.location.hash = 'discussion-' + id;
            
            const discussion = this.discussions.find(d => d.id == id);
            if (discussion && discussion.unread_count > 0) {
                fetch(`/taxi/discussions/${id}/mark-as-read`, { method: 'POST' })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            discussion.unread_count = 0;
                        }
                    })
                    .catch(err => console.error('Failed to mark as read:', err));
            }
        },

        formatDateTime(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        },
        
        scrollToBottom() {
            let chatBody = this.$refs.chatBody;
            if(chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        },

        async submitReply() {
            if (this.newReplyMessage.trim() === '') return;

            const discussionId = this.selectedDiscussion.id;
            const message = this.newReplyMessage;

            try {
                const response = await fetch(`/taxi/discussions/${discussionId}/replies`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) throw new Error('Network response was not ok.');

                const newReply = await response.json();
                
                const discussionIndex = this.discussions.findIndex(d => d.id == discussionId);
                if (discussionIndex !== -1) {
                    this.discussions[discussionIndex].replies.push(newReply.reply);
                    this.discussions[discussionIndex].last_activity_at = newReply.reply.created_at; 
                    this.discussions.sort((a, b) => new Date(b.last_activity_at) - new Date(a.last_activity_at));
                }
                
                this.newReplyMessage = '';
                this.$nextTick(() => this.scrollToBottom());

            } catch (error) {
                console.error('Error submitting reply:', error);
                alert('Failed to submit reply. Please try again.');
            }
        },

        async closeDiscussion() {
            if (!this.selectedDiscussion) return;
        
            const discussionId = this.selectedDiscussion.id;
        
            try {
                const response = await fetch(`/taxi/discussions/close/${discussionId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                });
        
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Network response was not ok.');
                }
        
                const result = await response.json();
        
                if (result.success) {
                    const discussionIndex = this.discussions.findIndex(d => d.id == discussionId);
                    if (discussionIndex !== -1) {
                        this.discussions[discussionIndex].status = 'closed';
                    }
                    this.updateActionButtonsVisibility();
                } else {
                    alert(result.message || 'Failed to close the discussion.');
                }
        
            } catch (error) {
                console.error('Error closing discussion:', error);
                alert(error.message || 'An error occurred. Please try again.');
            } finally {
                this.showCloseConfirmation = false;
            }
        },

        async reopenDiscussion() {
            if (!this.selectedDiscussion || this.selectedDiscussion.status !== 'closed') {
                return;
            }

            const discussionId = this.selectedDiscussion.id;

            try {
                const response = await fetch(`/taxi/discussions/reopen/${discussionId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to reopen the discussion.');
                }

                const result = await response.json();

                if (result.success) {
                    // Find the discussion and update its status and get the new reply
                    const discussionIndex = this.discussions.findIndex(d => d.id == discussionId);
                    if (discussionIndex !== -1) {
                       await this.fetchDiscussions(); // Refetch to get all updates
                    }
                } else {
                    alert(result.message || 'Failed to reopen the discussion.');
                }

            } catch (error) {
                console.error('Error reopening discussion:', error);
                alert(error.message || 'An error occurred. Please try again.');
            } finally {
                this.showReopenConfirmation = false;
            }
        }
    };
} 