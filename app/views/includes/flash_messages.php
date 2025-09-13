<?php

// This partial will now check for specific flash messages we've set in the controllers

// and display them using the new flash() helper function's output mechanism.



// Display Discussion Flash Messages

echo flash('discussion_success');

echo flash('discussion_error');



// Display Review Flash Messages

echo flash('review_success');

echo flash('review_error');



// Display Driver Assignment Flash Messages

echo flash('driver_assignment_success');

echo flash('driver_assignment_error');



// Display General Flash Messages

echo flash('success');

echo flash('error');

echo flash('info');

echo flash('warning');

// Display Legacy Flash Messages (for backward compatibility)

if (isset($_SESSION['success_message'])) {
    echo '<div id="flash-success" class="max-w-4xl mx-auto mt-4 mb-4 flash-message animate-slideInRight" role="alert">
        <div class="bg-green-50 border-l-4 border-green-500 rounded-r-lg p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-green-800 font-medium">' . htmlspecialchars($_SESSION['success_message']) . '</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="closeFlashMessage(\'flash-success\')" class="text-green-500 hover:text-green-700 focus:outline-none">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
            </div>
            <div class="progress-bar bg-green-200 h-1 mt-3 rounded-full overflow-hidden">
                <div class="progress-fill bg-green-500 h-full rounded-full animate-progress"></div>
            </div>
        </div>
    </div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div id="flash-error" class="max-w-4xl mx-auto mt-4 mb-4 flash-message animate-slideInRight" role="alert">
        <div class="bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-red-800 font-medium">' . htmlspecialchars($_SESSION['error_message']) . '</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="closeFlashMessage(\'flash-error\')" class="text-red-500 hover:text-red-700 focus:outline-none">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
            </div>
            <div class="progress-bar bg-red-200 h-1 mt-3 rounded-full overflow-hidden">
                <div class="progress-fill bg-red-500 h-full rounded-full animate-progress"></div>
            </div>
        </div>
    </div>';
    unset($_SESSION['error_message']);
}



// You can add more flash message checks here as needed

// e.g., echo flash('user_update_success');
?>

<style>
/* Professional Flash Messages Styles */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

.animate-slideInRight {
    animation: slideInRight 0.5s ease-out;
}

.animate-progress {
    animation: progress 5s linear forwards;
}

/* Enhanced hover effects */
.flash-message:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}

.flash-message:hover .progress-bar {
    transform: scaleY(1.5);
    transition: transform 0.2s ease;
}

/* Responsive design improvements */
@media (max-width: 640px) {
    .flash-message {
        margin: 1rem;
        max-width: calc(100vw - 2rem);
    }

    .flash-message .flex {
        flex-direction: column;
        align-items: flex-start;
    }

    .flash-message .ml-3 {
        margin-left: 0;
        margin-top: 0.5rem;
    }

    .flash-message .ml-auto {
        margin-left: 0;
        margin-top: 0.5rem;
        align-self: flex-end;
    }
}
</style>

<script>
// Professional Flash Messages with Enhanced UX
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach(function(message, index) {
        // Stagger animations for multiple messages
        setTimeout(function() {
            message.style.opacity = '1';
        }, index * 200);

        // Auto fade out after 5 seconds with smooth animation
        const timeoutId = setTimeout(function() {
            fadeOut(message);
        }, 5000);

        // Add close button functionality with improved UX
        const closeButtons = message.querySelectorAll('button[onclick*="closeFlashMessage"]');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                clearTimeout(timeoutId);
                fadeOut(message);
            });
        });

        // Pause auto-fade on hover
        message.addEventListener('mouseenter', function() {
            clearTimeout(timeoutId);
        });

        // Resume auto-fade on mouse leave
        message.addEventListener('mouseleave', function() {
            setTimeout(function() {
                fadeOut(message);
            }, 2000); // Resume with 2 seconds remaining
        });
    });
});

function fadeOut(element) {
    if (!element) return;

    // Enhanced fade out with scale effect
    element.style.transition = 'all 0.5s ease-out';
    element.style.opacity = '0';
    element.style.transform = 'translateX(100%) scale(0.95)';

    // Remove element after animation completes
    setTimeout(function() {
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }, 500);
}

// Manual close function with enhanced feedback
function closeFlashMessage(messageId) {
    const message = document.getElementById(messageId);
    if (message) {
        // Add visual feedback before closing
        message.style.transform = 'scale(0.98)';
        setTimeout(function() {
            fadeOut(message);
        }, 100);
    }
}

// Add keyboard accessibility
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(function(message) {
            fadeOut(message);
        });
    }
});
</script>

<?php
