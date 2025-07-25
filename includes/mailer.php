<?php
// includes/mailer.php - v1.0

function send_password_reset_email($email, $reset_link) {
    // In production, use PHPMailer or similar library
    // This is a basic implementation for development
    
    $to = $email;
    $subject = 'TradeVault - Password Reset Request';
    $message = "
        <html>
        <head>
            <title>Password Reset</title>
        </head>
        <body>
            <h2>Password Reset Request</h2>
            <p>You requested a password reset for your TradeVault account.</p>
            <p>Please click the link below to reset your password:</p>
            <p><a href='$reset_link'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@tradevault.com\r\n";
    $headers .= "Reply-To: no-reply@tradevault.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $message, $headers);
}