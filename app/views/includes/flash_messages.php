<?php
// This partial will now check for specific flash messages we've set in the controllers
// and display them using the new flash() helper function's output mechanism.

// Display Discussion Flash Messages
echo flash('discussion_success');
echo flash('discussion_error');

// Display Review Flash Messages
echo flash('review_success');
echo flash('review_error');

// You can add more flash message checks here as needed
// e.g., echo flash('user_update_success'); 