<?php
// 1. Setup and Security
require_once __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (!$token) {
    die("No token provided.");
}

// 2. Validate Token (Matches how you saved it in forgot_pass.php)
try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 3. Handle Password Update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // SECURITY: Update password AND clear the token so it can't be reused
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $update->execute([$new_password, $user['id']]);
            
            $success = "Password successfully updated! You can now <a href='login.php'>login</a>.";
        }
    } else {
        $error = "This link is invalid or has expired.";
    }
} catch (PDOException $e) {
    $error = "Database error.";
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php else: ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter new password" required>
            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>
</body>
</html>