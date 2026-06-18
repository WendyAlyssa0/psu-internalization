<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error    = '';
$username = $_COOKIE['remember_user'] ?? '';
$remember = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($username === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare("
            SELECT id, first_name, email, user_role, password_hash
            FROM users
            WHERE email = :username
               OR first_name = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['user_role']  = $user['user_role'];

            if ($remember) {
                setcookie('remember_user', $username, time() + 60 * 60 * 24 * 30, '/', '', true, true);
            } else {
                setcookie('remember_user', '', time() - 3600, '/');
            }

            $role = strtolower(trim($user['user_role']));

            if (in_array($role, ['applicant', 'ap'], true)) {
                header('Location: ../user/home.php');
                exit();
            } else {
                $error = 'Your account does not have access to this portal.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSU Internalization Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/user_login.css">
</head>
<body>

<div class="login-wrapper">

    <!-- LEFT: FORM -->
    <div class="login-left">
        <div class="form-header">
            <div class="form-title">Sign In</div>
            <div class="form-subtitle">Access your PSU Internalization applicant account</div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Username or Email</label>
                <div class="field-wrap">
                    <i class="fa fa-user"></i>
                    <input type="text" name="username"
                           placeholder="Enter username or email"
                           value="<?= htmlspecialchars($username) ?>"
                           required autocomplete="username" autofocus>
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
                    <a href="#" onclick="swapPanel(event)">Forgot password?</a>
                </div>

            <button type="submit" class="login-btn">
                Log In <i class="fa fa-arrow-right"></i>
            </button>
        </form>

        <div class="form-footer">
            &copy; <?= date('Y') ?> Pangasinan State University &nbsp;·&nbsp; PSUxIZN
        </div>
    </div>

    <!-- RIGHT: WELCOME -->
    <div class="login-right">
        <div class="top-design">
            <div class="wave-dark"></div>
            <div class="wave-medium"></div>
            <div class="wave-light"></div>
        </div>
        <div class="deco-circle c1"></div>
        <div class="deco-circle c2"></div>
        <div class="deco-circle c3"></div>

        <div class="right-content">
            <div class="welcome-label">Student Portal</div>
            <div class="welcome-title">
                Welcome<br>to <span>PSU Internalization</span><br>Portal
            </div>
            <div class="welcome-quote">
                Explore new cultures. Expand your horizons.
                <small>Connecting PSU students to global opportunities.</small>
            </div>
            <div class="icon-row">
                <div class="icon-chip"><i class="fa fa-plane"></i> Exchange</div>
                <div class="icon-chip"><i class="fa fa-earth-asia"></i> Global</div>
                <div class="icon-chip"><i class="fa fa-graduation-cap"></i> Scholars</div>
            </div>
        </div>

        <div class="right-footer">
            <div class="right-footer-text">Pangasinan State University &nbsp;·&nbsp; International Programs</div>
        </div>
    </div>

</div>

<script>
function swapPanel(event) {
    event.preventDefault(); // Stop the link from navigating away immediately
    const wrapper = document.querySelector('.login-wrapper');
    
    // Add the class to trigger the CSS transition
    wrapper.classList.add('swap-layout');
    
    // After the animation finishes (e.g., 600ms), navigate to the new page
    setTimeout(() => {
        window.location.href = 'user_forgot_pass.php';
    }, 600);
}

    // This function adds or removes the class based on the page you are on
    function setInitialLayout() {
        const wrapper = document.querySelector('.login-wrapper');
        // If we are on the forgot password page, ensure the class is present
        if (window.location.pathname.includes('user_forgot_pass.php')) {
            wrapper.classList.add('reversed');
        } else {
            wrapper.classList.remove('reversed');
        }
    }
    
    // Run on page load
    window.onload = setInitialLayout;

</script>

</body>
</html>