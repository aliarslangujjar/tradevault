<?php
// TradeVault Login - v2.1
session_start();

if (isset($_SESSION['password_reset_success'])) {
    $success_message = "Your password has been reset successfully. Please login with your new password.";
    unset($_SESSION['password_reset_success']);
}

if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
    $success_message = "Registration successful! Please login with your credentials.";
    unset($_SESSION['registration_success']); // Clear the flag
    
    // Optional: Pre-fill email if you want
    $prefilled_email = $_SESSION['registered_email'] ?? '';
    unset($_SESSION['registered_email']);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        
        // Redirect to original page if exists
        $redirect = $_SESSION['redirect_url'] ?? 'dashboard.php';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}

$title = "Login";
$hide_nav = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-container">
        <h2 class="auth-title">Login to TradeVault</h2>
<?php if (!empty($success_message)): ?>
<div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>
        <?php if (!empty($success_message)): ?>
<div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registration successful! Please login.</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
<div class="text-center mt-3">
    <a href="forgot-password.php">Forgot your password?</a>
</div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>