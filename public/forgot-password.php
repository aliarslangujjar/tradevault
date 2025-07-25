<?php
// forgot-password.php - v2.1
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php'; // Add this line

$title = "Forgot Password";
$hide_nav = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing tokens for this email
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
        
        // Store new token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);
        
        // Create reset link
        $reset_link = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
                     $_SERVER['HTTP_HOST'] . 
                     dirname($_SERVER['PHP_SELF']) . 
                     '/reset-password.php?token=' . $token;
        
        // Send email
        if (send_password_reset_email($email, $reset_link)) {
            $success = "If an account exists with this email, we've sent a password reset link.";
        } else {
            $error = "Failed to send email. Please try again later.";
        }
    } else {
        // Show same message whether user exists or not (security best practice)
        $success = "If an account exists with this email, we've sent a password reset link.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-container">
        <h2 class="auth-title">Forgot Password</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="post" class="auth-form">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
            </form>
        <?php endif; ?>
        
        <div class="auth-footer">
            Remember your password? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>