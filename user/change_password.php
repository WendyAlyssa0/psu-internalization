<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db.php';

$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pass = trim($_POST['current_password'] ?? '');  // trim whitespace
    $new_pass     = trim($_POST['new_password']     ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    // Guard: session must have a valid user ID
    if (empty($_SESSION['user_id'])) {
        $message     = "Session expired. Please log in again.";
        $messageType = 'alert-danger';
    } elseif (strlen($new_pass) < 8) {
        $message     = "New password must be at least 8 characters.";
        $messageType = 'alert-danger';
    } elseif ($new_pass !== $confirm_pass) {
        $message     = "New passwords do not match.";
        $messageType = 'alert-danger';
    } else {
        try {
            $pdo  = db();
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($current_pass, $user['password'])) {
                // Prevent reusing the same password
                if (password_verify($new_pass, $user['password'])) {
                    $message     = "New password must differ from your current one.";
                    $messageType = 'alert-danger';
                } else {
                    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $update   = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($update->execute([$new_hash, $_SESSION['user_id']])) {
                        $message     = "Password changed successfully!";
                        $messageType = 'alert-success';
                    } else {
                        $message     = "Database error. Please try again.";
                        $messageType = 'alert-danger';
                    }
                }
            } else {
                // Constant-time branch to prevent timing attacks
                password_verify('dummy', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234');
                $message     = "Incorrect current password.";
                $messageType = 'alert-danger';
            }
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());  // log, never expose
            $message     = "An unexpected error occurred. Please try again.";
            $messageType = 'alert-danger';
        }
    }
}
?>

<link rel="stylesheet" href ="../asset/css/chang_password.css">

<div class="password-card">

    <div class="password-card-header">
        <div class="password-icon-ring">
            <i class="ti ti-lock" aria-hidden="true"></i>
        </div>
        <div>
            <h3>Change password</h3>
            <p>Update your account password</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert <?= htmlspecialchars($messageType) ?>" role="alert">
            <i class="ti ti-<?= $messageType === 'alert-success' ? 'circle-check' : 'alert-circle' ?>"
               aria-hidden="true"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="password-card-body" novalidate>

        <div class="password-form-group">
            <label for="current_password">Current password</label>
            <div class="input-wrap">
                <input type="password" id="current_password" name="current_password"
                       class="input-field" autocomplete="current-password" required>
                <button type="button" class="input-eye"
                        onclick="toggleVis('current_password', this)"
                        aria-label="Show current password">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-divider"></div>

        <div class="password-form-group">
            <label for="new_password">New password</label>
            <div class="input-wrap">
                <input type="password" id="new_password" name="new_password"
                       class="input-field" autocomplete="new-password"
                       minlength="8" required
                       oninput="checkStrength(this.value)">
                <button type="button" class="input-eye"
                        onclick="toggleVis('new_password', this)"
                        aria-label="Show new password">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            <div class="password-strength-track">
                <div class="password-strength-bar" id="sbar"></div>
            </div>
            <span class="input-hint" id="shint">Enter a new password</span>
        </div>

        <div class="password-form-group">
            <label for="confirm_password">Confirm new password</label>
            <div class="input-wrap">
                <input type="password" id="confirm_password" name="confirm_password"
                       class="input-field" autocomplete="new-password"
                       minlength="8" required
                       oninput="checkMatch()">
                <button type="button" class="input-eye"
                        onclick="toggleVis('confirm_password', this)"
                        aria-label="Show confirm password">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            <span class="input-hint" id="mhint" style="display:none;"></span>
        </div>

        <div class="password-card-footer">
            <button type="submit" class="mark-read-btn">Update password</button>
        </div>

    </form>
</div>

<script>
function toggleVis(id, btn) {
    const inp  = document.getElementById(id);
    const icon = btn.querySelector('i');
    const show = inp.type === 'password';
    inp.type        = show ? 'text' : 'password';
    icon.className  = show ? 'ti ti-eye-off' : 'ti ti-eye';
    btn.setAttribute('aria-label', (show ? 'Hide ' : 'Show ') + id.replace(/_/g, ' '));
}

function checkStrength(val) {
    const bar  = document.getElementById('sbar');
    const hint = document.getElementById('shint');
    let score  = 0;
    if (val.length >= 8)                              score++;
    if (val.length >= 12)                             score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val))      score++;
    if (/\d/.test(val))                               score++;
    if (/[^A-Za-z0-9]/.test(val))                    score++;

    const levels = [
        { w: '0%',   bg: 'transparent', text: 'Enter a new password' },
        { w: '20%',  bg: '#E24B4A',     text: 'Very weak'   },
        { w: '40%',  bg: '#EF9F27',     text: 'Weak'        },
        { w: '60%',  bg: '#BA7517',     text: 'Fair'        },
        { w: '80%',  bg: '#639922',     text: 'Strong'      },
        { w: '100%', bg: '#3B6D11',     text: 'Very strong' },
    ];
    const l = val.length === 0 ? levels[0] : levels[Math.min(score, 5)];
    bar.style.width      = l.w;
    bar.style.background = l.bg;
    hint.textContent     = val.length === 0 ? 'Enter a new password' : l.text;
    checkMatch();
}

function checkMatch() {
    const np   = document.getElementById('new_password').value;
    const cp   = document.getElementById('confirm_password').value;
    const hint = document.getElementById('mhint');
    if (!cp) { hint.style.display = 'none'; return; }
    hint.style.display = 'block';
    const match        = np === cp;
    hint.textContent   = match ? 'Passwords match' : 'Passwords do not match';
    hint.className     = 'input-hint ' + (match ? 'success' : 'danger');
}
</script>