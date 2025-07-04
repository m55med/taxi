function discussionsComponent() {
    return {
        discussions: [],
        currentUser: {},
        selectedDiscussionId: null,
        searchTerm: '',
        filterStatus: 'all', // 'all', 'open', 'closed'
        newReplyMessage: '',
        showCloseConfirmation: false,
        canUserCloseDiscussion: false,
        isLoading: true,

        async init() {
            await this.fetchDiscussions();
            
            this.$watch('selectedDiscussionId', () => {
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.updateCloseButtonVisibility();
                });
            });
        },
        
        async fetchDiscussions() {
            this.isLoading = true;
            try {
                const response = await fetch('/taxi/api/discussions');
                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }
                const data = await response.json();
                this.discussions = data.discussions;
                this.currentUser = data.currentUser;
                
                // After data is loaded, select the first discussion or one from the hash
                if (this.filteredDiscussions.length > 0) {
                    const hash = window.location.hash;
                    if (hash && hash.startsWith('#discussion-')) {
                        const idFromHash = parseInt(hash.substring('#discussion-'.length), 10);
                        if (this.discussions.some(d => d.id === idFromHash)) {
                            this.selectedDiscussionId = idFromHash;
                        }
                    } else {
                        this.selectedDiscussionId = this.filteredDiscussions[0].id;
                    }
                }
                this.updateCloseButtonVisibility();

            } catch (error) {
                console.error('Failed to fetch discussions:', error);
                // Optionally, show an error message in the UI
            } finally {
                this.isLoading = false;
            }
        },

        updateCloseButtonVisibility() {
            if (!this.selectedDiscussion) {
                this.canUserCloseDiscussion = false;
                return;
            }
            const canCloseRoles = ['admin', 'quality_manager', 'Team_leader'];
            this.canUserCloseDiscussion = this.selectedDiscussion.status === 'open' && canCloseRoles.includes(this.currentUser.role);
        },

        get filteredDiscussions() {
            return this.discussions.filter(d => {
                const searchMatch = this.searchTerm.toLowerCase() === '' ||
                                    d.reason.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                                    (d.ticket_number && d.ticket_number.toString().includes(this.searchTerm.toLowerCase())) ||
                                    d.opener_name.toLowerCase().includes(this.searchTerm.toLowerCase());

                const statusMatch = this.filterStatus === 'all' || d.status === this.filterStatus;

                return searchMatch && statusMatch;
            });
        },
        
        get selectedDiscussion() {
            if (!this.selectedDiscussionId) return this.filteredDiscussions.length > 0 ? this.filteredDiscussions[0] : null;
            let discussion = this.discussions.find(d => d.id == this.selectedDiscussionId);
            if (!discussion) return this.filteredDiscussions.length > 0 ? this.filteredDiscussions[0] : null;
            return discussion;
        },

        selectDiscussion(id) {
            this.selectedDiscussionId = id;
            window.location.hash = 'discussion-' + id;
            
            // Mark as read
            const discussion = this.discussions.find(d => d.id == id);
            if (discussion && discussion.unread_count > 0) {
                fetch(`/taxi/api/discussions/${id}/mark-as-read`, { method: 'POST' })
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
                const response = await fetch(`/taxi/api/discussions/${discussionId}/replies`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }

                const newReply = await response.json();
                
                // Add reply to the discussion in the frontend state
                const discussionIndex = this.discussions.findIndex(d => d.id == discussionId);
                if (discussionIndex !== -1) {
                    this.discussions[discussionIndex].replies.push(newReply.reply);
                    
                    // Update last activity to bring discussion to top
                    this.discussions[discussionIndex].last_activity_at = newReply.reply.created_at; 
                    
                    // Sort discussions by last activity
                    this.discussions.sort((a, b) => new Date(b.last_activity_at) - new Date(a.last_activity_at));
                }
                
                this.newReplyMessage = ''; // Clear input
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
                    // Find the discussion and update its status
                    const discussionIndex = this.discussions.findIndex(d => d.id == discussionId);
                    if (discussionIndex !== -1) {
                        this.discussions[discussionIndex].status = 'closed';
                    }
                    // Re-evaluate computed properties
                    this.updateCloseButtonVisibility();
                } else {
                    alert(result.message || 'Failed to close the discussion.');
                }
        
            } catch (error) {
                console.error('Error closing discussion:', error);
                alert(error.message || 'An error occurred. Please try again.');
            } finally {
                this.showCloseConfirmation = false;
            }
        }
    };
} 