<<<<<<< HEAD
<?php
// TradeVault Index - v2.1
session_start();

// Redirect to dashboard if logged in, otherwise to login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
=======
<?php
// TradeVault Index - v2.1
session_start();

// Redirect to dashboard if logged in, otherwise to login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
>>>>>>> 189a777281163ce1a60fd0e2e61beba42a296835
?>