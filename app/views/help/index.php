<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Help & Support Center</h1>
        <p class="text-gray-600">Get help, report bugs, or suggest improvements</p>
    </div>

    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Help Sections -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Documentation -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Documentation</h3>
                        <p class="text-sm text-gray-600">Comprehensive guides and documentation</p>
                    </div>
                </div>
                <a href="<?= URLROOT ?>/documentation" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                    View Documentation <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Knowledge Base -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-lightbulb text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Knowledge Base</h3>
                        <p class="text-sm text-gray-600">Frequently asked questions and solutions</p>
                    </div>
                </div>
                <a href="<?= URLROOT ?>/knowledge_base" class="inline-flex items-center gap-2 text-green-600 hover:text-green-800 font-medium">
                    Browse Knowledge Base <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Report Bug / Suggestion Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bug text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Report Bug or Suggest Improvement</h3>
                        <p class="text-sm text-gray-600">Help us improve by reporting issues or sharing your ideas</p>
                    </div>
                </div>

                <form action="<?= URLROOT ?>/help/submit-feedback" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="feedback_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select type...</option>
                                <option value="bug">🐛 Bug Report</option>
                                <option value="feature">✨ Feature Request</option>
                                <option value="improvement">🚀 Improvement Suggestion</option>
                                <option value="question">❓ Question</option>
                                <option value="other">📝 Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" required placeholder="Brief description of your issue or suggestion"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="4" required placeholder="Please provide detailed information about your bug report or feature request..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300 ease-in-out">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact Support -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Immediate Help?</h3>

                <div class="space-y-3">
                    <a href="mailto:support@company.com" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope text-red-600"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Email Support</div>
                            <div class="text-sm text-gray-500">support@company.com</div>
                        </div>
                    </a>

                    <a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fab fa-telegram-plane text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Live Chat</div>
                            <div class="text-sm text-gray-500">Available 24/7</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Updates -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Updates</h3>

                <div class="space-y-4">
                    <div class="border-l-4 border-green-500 pl-4">
                        <div class="font-medium text-gray-900">New Task Management Features</div>
                        <div class="text-sm text-gray-500">Added file attachments and improved notifications</div>
                        <div class="text-xs text-gray-400 mt-1">2 days ago</div>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="font-medium text-gray-900">Performance Improvements</div>
                        <div class="text-sm text-gray-500">Faster loading times and better responsiveness</div>
                        <div class="text-xs text-gray-400 mt-1">1 week ago</div>
                    </div>

                    <div class="border-l-4 border-purple-500 pl-4">
                        <div class="font-medium text-gray-900">New Help Center</div>
                        <div class="text-sm text-gray-500">Comprehensive documentation and support</div>
                        <div class="text-xs text-gray-400 mt-1">2 weeks ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-resize textarea
document.querySelector('textarea[name="description"]').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const feedbackType = this.querySelector('[name="feedback_type"]').value;
    const subject = this.querySelector('[name="subject"]').value.trim();
    const description = this.querySelector('[name="description"]').value.trim();

    if (!feedbackType) {
        e.preventDefault();
        alert('Please select a feedback type.');
        return;
    }

    if (subject.length < 5) {
        e.preventDefault();
        alert('Please provide a more detailed subject (at least 5 characters).');
        return;
    }

    if (description.length < 10) {
        e.preventDefault();
        alert('Please provide a more detailed description (at least 10 characters).');
        return;
    }
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
