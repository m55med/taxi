<?php
// Flash message helper
// EXAMPLE - flash('register_success', 'You are now registered', 'alert alert-danger');
// DISPLAY IN VIEW - <?php echo flash('register_success'); ? >
function flash($name = '', $message = '', $class = 'bg-green-500 text-white p-4 rounded-lg mb-4') {
    if(!empty($name)){
        if(!empty($message) && empty($_SESSION[$name])){
            if(!empty($_SESSION[$name])){
                unset($_SESSION[$name]);
            }

            if(!empty($_SESSION[$name. '_class'])){
                unset($_SESSION[$name. '_class']);
            }

            $_SESSION[$name] = $message;
            $_SESSION[$name. '_class'] = $class;
        } elseif(empty($message) && !empty($_SESSION[$name])){
            $class = !empty($_SESSION[$name. '_class']) ? $_SESSION[$name. '_class'] : '';
            echo '<div class="'.$class.'" id="msg-flash">'.$_SESSION[$name].'</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name. '_class']);
        }
    }
} 