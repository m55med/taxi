<?php
/**
 * Flash message helper
 * EXAMPLE - In controller: flash('register_success', 'You are now registered');
 * DISPLAY IN VIEW - In PHP: <?php echo flash('register_success'); ?>
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        // Set a new flash message
        if (!empty($message) && empty($_SESSION[$name])) {
            if (!empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } 
        // Display the flash message if it exists
        elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . htmlspecialchars($_SESSION[$name]) . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
} 