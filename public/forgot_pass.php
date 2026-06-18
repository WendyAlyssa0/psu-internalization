<?php
// 1. Force errors to display so we can see the problem
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            $pdo = db(); // Using your singleton db function
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $reset_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $update_stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $update_stmt->execute([$reset_token, $token_expiry, $user['id']]);
                
                // Debugging: Comment this out when in production
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Internalization_management/public/reset_password.php?token=" . $reset_token;
                $message = "Reset link (for testing): " . $reset_link; 
            } else {
                $message = 'If an account exists with that email, a reset link will be sent.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../asset/css/login.css">
</head>
<body>
    <div class="login-card">
        <div class="login-left">
            <div class="top-design">
                <div class="wave-dark"></div>
                <div class="wave-medium"></div>
                <div class="wave-light"></div>
            </div>
            <div class="left-content">
                <div class="left-logo">
                    <img src="../asset/img/psu_logo.png" alt="PSU Logo">
                </div>
                <div class="left-title">PSUxIZN</div>
                <div class="left-sub">Password Recovery</div>
            </div>
        </div>

        <div class="login-right">
            <div class="form-header">
                <div class="form-title">Reset Password</div>
                <div class="form-subtitle">Enter your email to receive a reset link</div>
            </div>

            <?php if ($error): ?>
                <div class="error" style="color: #d9534f; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="success" style="color: #5cb85c; margin-bottom: 15px;"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Email Address</label>
                    <div class="field-wrap">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    Send Reset Link <i class="fa fa-arrow-right"></i>
                </button>

                <a href="login.php" class="back-btn">
                    Back to Login
                </a>
        </div>
    </div>
</body>
</html>