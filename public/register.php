<<<<<<< HEAD
<?php
// TradeVault Registration - v2.5
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$title = "Register";
$hide_nav = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    $errors = [];
    
    if (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            // Set success message in session
            $_SESSION['registration_success'] = true;
            $_SESSION['registered_email'] = $email;
            
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = strpos($e->getMessage(), 'Duplicate entry') ? 
                'Username or email already exists' : 'Registration failed';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="auth-wrapper">
    <div class="auth-container">
        <h2 class="auth-title">Register for TradeVault</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required minlength="4">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<script>
// Password match validation
document.querySelector('input[name="confirm_password"]').addEventListener('input', function(e) {
    const password = document.querySelector('input[name="password"]');
    if (password.value !== e.target.value) {
        e.target.setCustomValidity("Passwords must match");
    } else {
        e.target.setCustomValidity("");
    }
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
=======
<?php
// TradeVault Registration - v2.5
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$title = "Register";
$hide_nav = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    $errors = [];
    
    if (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            // Set success message in session
            $_SESSION['registration_success'] = true;
            $_SESSION['registered_email'] = $email;
            
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = strpos($e->getMessage(), 'Duplicate entry') ? 
                'Username or email already exists' : 'Registration failed';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="auth-wrapper">
    <div class="auth-container">
        <h2 class="auth-title">Register for TradeVault</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required minlength="4">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<script>
// Password match validation
document.querySelector('input[name="confirm_password"]').addEventListener('input', function(e) {
    const password = document.querySelector('input[name="password"]');
    if (password.value !== e.target.value) {
        e.target.setCustomValidity("Passwords must match");
    } else {
        e.target.setCustomValidity("");
    }
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
>>>>>>> 189a777281163ce1a60fd0e2e61beba42a296835
?>