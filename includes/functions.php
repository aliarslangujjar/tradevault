<?php
// TradeVault Functions - v2.1
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function format_date($date, $format = 'Y-m-d H:i') {
    return date($format, strtotime($date));
}

function calculate_rr($entry, $exit, $position_type) {
    if ($position_type === 'long') {
        return $exit > $entry ? ($exit - $entry) / ($entry - $exit) : 0;
    } else {
        return $entry > $exit ? ($entry - $exit) / ($exit - $entry) : 0;
    }
}

function custom_scripts() {
    // Can be overridden by pages that need custom JS
}

// Flash message system
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name.'_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            echo '<div class="'.$_SESSION[$name.'_class'].'" id="msg-flash">'.$_SESSION[$name].'</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name.'_class']);
        }
    }
}

// Check if current page is active
function is_active($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>