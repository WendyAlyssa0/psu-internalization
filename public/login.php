<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error    = '';
$username = $_COOKIE['remember_email'] ?? '';
$remember = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($username === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare("
            SELECT id, first_name, email, user_role, password_hash
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['user_role']  = $user['user_role'];

            if ($remember) {
                setcookie('remember_email', $username, time() + 60 * 60 * 24 * 30, '/', '', true, true);
            } else {
                setcookie('remember_email', '', time() - 3600, '/');
            }

            $role = strtolower(trim($user['user_role']));

            switch ($role) {
                case 'admin':
                case 'ad':
                case 'super admin':
                case 'sa':
                    header('Location: ../admin/dashboard.php');
                    exit();

                default:
                    $error = 'Your account does not have access to this portal.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSU Internalization</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/login.css">
</head>
<body>

<div class="login-card">

    <!-- LEFT PANEL -->
    <div class="login-left">
        <div class="top-design">
            <div class="wave-dark"></div>
            <div class="wave-medium"></div>
            <div class="wave-light"></div>
        </div>
        <div class="deco-circle c1"></div>
        <div class="deco-circle c2"></div>
        <div class="deco-circle c3"></div>

        <div class="left-content">
            <div class="left-logo">
                <img src="../asset/img/psu_logo.png" alt="PSU Logo">
            </div>
            <div class="left-title">PSU Internalization</div>
            <div class="left-sub">Management System<br>for Pangasinan State University</div>
        </div>

        <div class="left-footer">
            <div class="left-dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <div class="left-tagline">Admin Portal &nbsp;·&nbsp; Secure Access</div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="login-right">
        <div class="form-header">
            <div class="form-title">Welcome back</div>
            <div class="form-subtitle">Sign in to your administrator account</div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Email Address</label>
                <div class="field-wrap">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="username"
                           placeholder="AD@psuizn.com"
                           value="<?= htmlspecialchars($username) ?>"
                           required autocomplete="email" autofocus>
                </div>
            </div>

            <div class="field">
                <label>Password</label>
                <div class="field-wrap">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password"
                           placeholder="Enter your password"
                           required autocomplete="current-password">
                </div>
            </div>

            <div class="options">
                <label>
                    <input type="checkbox" name="remember" <?= $remember ? 'checked' : '' ?>>
                    Remember me
                </label>
                <a href="forgot_pass.php" target="_blank">Forgot password?</a>
            </div>

            <button type="submit" class="login-btn">
                Sign In <i class="fa fa-arrow-right"></i>
            </button>
        </form>

        <div class="form-footer">
            &copy; <?= date('Y') ?> Pangasinan State University &nbsp;·&nbsp; PSUxIZN
        </div>
    </div>

</div>

</body>
</html>