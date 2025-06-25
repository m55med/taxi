<?php
/**
 * Flash message helper
 * EXAMPLE: flash('bonus_message', 'Bonus granted successfully', 'success');
 * DISPLAY IN VIEW: <?php flash('bonus_message'); ?>
 */
function flash($name = '', $message = '', $type = 'success') {
    if (!empty($name)) {
        // Set the flash message
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_type'] = $type;
        } 
        // Display the flash message
        elseif (empty($message) && !empty($_SESSION[$name])) {
            $message = json_encode(['message' => $_SESSION[$name], 'type' => $_SESSION[$name . '_type']]);
            echo "<script>window.dispatchEvent(new CustomEvent('flash-message', { detail: $message }));</script>";
            
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_type']);
        }
    }
} 