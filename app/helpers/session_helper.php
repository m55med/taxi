<?php
/**
 * Flash message helper
 * EXAMPLE - In controller: flash('register_success', 'You are now registered', 'success');
 * DISPLAY IN VIEW - In PHP: <?php echo flash('register_success'); ?>
 * This helper now generates a Toastr.js script for modern, non-blocking notifications.
 */
function flash($name = '', $message = '', $class = 'success') {
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
            $type = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'success';
            $messageText = json_encode(htmlspecialchars($_SESSION[$name]));

            // Backward compatibility: infer type from old Tailwind class names if they are still being used
            if (strpos($type, 'bg-red') !== false || strpos($type, 'error') !== false) {
                $toastrType = 'error';
            } elseif (strpos($type, 'bg-yellow') !== false || strpos($type, 'warning') !== false) {
                $toastrType = 'warning';
            } elseif (strpos($type, 'bg-blue') !== false || strpos($type, 'info') !== false) {
                $toastrType = 'info';
            } else {
                $toastrType = 'success';
            }

            echo "<script>
                    // Ensure this script runs after the DOM is loaded and toastr is available
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof toastr !== 'undefined') {
                            toastr.options = {
                              \"closeButton\": true,
                              \"debug\": false,
                              \"newestOnTop\": true,
                              \"progressBar\": true,
                              \"positionClass\": \"toast-top-left\",
                              \"preventDuplicates\": false,
                              \"onclick\": null,
                              \"showDuration\": \"300\",
                              \"hideDuration\": \"1000\",
                              \"timeOut\": \"5000\",
                              \"extendedTimeOut\": \"1000\",
                              \"showEasing\": \"swing\",
                              \"hideEasing\": \"linear\",
                              \"showMethod\": \"fadeIn\",
                              \"hideMethod\": \"fadeOut\"
                            };
                            toastr['$toastrType']($messageText);
                        } else {
                            console.error('Toastr.js is not loaded, but a flash message was attempted.');
                        }
                    });
                  </script>";

            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
} 