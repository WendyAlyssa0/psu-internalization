<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

$errors = [];
$success = false;

$first_name = '';
$last_name = '';
$role = '';
$email = '';
$generatedPassword = '';

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generatePassword(): string {
    return 'PASSWORD';
}

function generateEmail(string $firstName, string $lastName, string $role): string {

    $prefix = match ($role) {
        'super admin' => 'SA',
        'admin' => 'AD',
        'applicant' => 'AP',
        default => 'US',
    };

    $first = strtolower(substr($firstName, 0, 1));
    $last = strtolower(str_replace(' ', '', $lastName));

    return $prefix . $first . $last . '@psuxizn.com';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $role       = strtolower(trim($_POST['role'] ?? ''));
    $isPrivileged = in_array($role, ['admin', 'super admin'], true);

    // VALIDATION
    if ($first_name === '' || $last_name === '' || $role === '') {
        $errors[] = 'Please fill in all required fields.';
    }

    if (!$errors && !in_array($role, ['applicant', 'admin', 'super admin'], true)) {
        $errors[] = 'Invalid role selected.';
    }

    if ($isPrivileged) {
        // ADMIN / SUPER ADMIN: email and password are always generated server-side,
        // never trust anything submitted from the client for these fields.
        $email = generateEmail($first_name, $last_name, $role);
        $password = generatePassword();
        $generatedPassword = $password;
    } else {
        // APPLICANT: manual email + password
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = 'Please fill in all required fields.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        if ($password !== '' && $password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
    }

    if (!$errors) {

        // CHECK EMAIL EXISTENCE
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'Email already exists.';
        } else {

            try {

                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        first_name,
                        last_name,
                        email,
                        password_hash,
                        user_role,
                        status
                    )
                    VALUES (
                        :first_name,
                        :last_name,
                        :email,
                        :password_hash,
                        :user_role,
                        :status
                    )
                ");

                $result = $stmt->execute([
                    ':first_name'    => $first_name,
                    ':last_name'     => $last_name,
                    ':email'         => $email,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':user_role'     => $role,
                    ':status'        => 'Active',
                ]);

                if ($result) {
                    $success = true;
                    header('refresh:2;url=login.php');
                } else {
                    $errors[] = 'Registration failed.';
                }

            } catch (PDOException $e) {
                $errors[] = 'Database error.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 40px 20px;
        }

        .page {
            display: flex;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }

        .card-header {
            background: #0E1C36;
            color: #fff;
            padding: 24px 28px;
        }

        .card-header-titles h2 {
            margin: 0 0 4px 0;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .card-header-titles p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .card-body {
            padding: 28px;
        }

        .error-box, .success-box {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 0.9rem;
        }

        .error-box {
            background: #fdecea;
            color: #b3261e;
            border: 1px solid #f6cac6;
        }

        .success-box {
            background: #e8f5e9;
            color: #1e7e34;
            border: 1px solid #c3e6cb;
        }

        .form-section-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: #0E1C36;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eef0f4;
        }

        .form-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 6px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .col-12 { flex: 0 0 100%; }

        .form-group label {
            font-size: 0.82rem;
            font-weight: 500;
            color: #333;
        }

        .required {
            color: #d64545;
            margin-left: 2px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d9dde3;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0E1C36;
        }

        .form-instructions {
            font-size: 0.78rem;
            color: #888;
            margin: 4px 0 0 0;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 22px;
        }

        .btn-submit {
            background: #0E1C36;
            color: #fff;
            border: none;
            padding: 11px 22px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #162944;
        }

        .btn-link {
            color: #0E1C36;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .footer-note {
            text-align: center;
            margin-top: 18px;
            font-size: 0.85rem;
            color: #555;
        }

        .footer-note a {
            color: #0E1C36;
            font-weight: 600;
            text-decoration: none;
        }

        .footer-note a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="page">
    <div class="card">

        <div class="card-header">
            <div class="card-header-titles">
                <h2>Create Account</h2>
                <p>Register for applicant access to the system.</p>
            </div>
        </div>

        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <strong><i class="fa-solid fa-circle-check"></i> Registration successful!</strong>
                    <?php if ($generatedPassword !== ''): ?>
                        <div style="margin-top: 6px;"><strong>Email Address:</strong> <code><?= e($email) ?></code></div>
                        <div><strong>Temporary Password:</strong> <code><?= e($generatedPassword) ?></code></div>
                    <?php endif; ?>
                    <div style="margin-top: 4px;">Redirecting to login...</div>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">

                <div class="form-section-title">Personal Information</div>

                <div class="form-grid">
                    <div class="form-group col-12">
                        <label>First Name<span class="required">*</span></label>
                        <input type="text" name="first_name" autocomplete="off" placeholder="e.g., Sherwin" required value="<?= e($first_name) ?>">
                    </div>

                    <div class="form-group col-12">
                        <label>Last Name<span class="required">*</span></label>
                        <input type="text" name="last_name" autocomplete="off" placeholder="e.g., Dela Cruz" required value="<?= e($last_name) ?>">
                    </div>

                    <div class="form-group col-12">
                        <label>Role<span class="required">*</span></label>
                        <select name="role" id="roleSelect" required>
                            <option value="">Select Role</option>
                            <option value="applicant" <?= $role === 'applicant' ? 'selected' : '' ?>>Applicant</option>
                            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="super admin" <?= $role === 'super admin' ? 'selected' : '' ?>>Super Admin</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-title">Account Credentials</div>

                <div class="form-grid">

                    <!-- Applicant: manual email + password -->
                    <div class="form-group col-12" id="applicantEmailField">
                        <label>Email Address<span class="required">*</span></label>
                        <input type="email" name="email" id="emailInput" autocomplete="off" placeholder="you@example.com" value="<?= e($email) ?>">
                    </div>

                    <div class="form-group col-12" id="passwordField">
                        <label>Password<span class="required">*</span></label>
                        <input type="password" name="password" id="passwordInput" placeholder="Enter password">
                    </div>

                    <div class="form-group col-12" id="confirmPasswordField">
                        <label>Confirm Password<span class="required">*</span></label>
                        <input type="password" name="confirm_password" id="confirmPasswordInput" placeholder="Re-enter password">
                    </div>

                    <!-- Admin / Super Admin: auto-generated, read-only preview -->
                    <div class="form-group col-12" id="generatedEmailField" style="display: none;">
                        <label>Generated Email Address</label>
                        <input type="text" id="generatedEmailPreview" readonly placeholder="Generates automatically...">
                    </div>

                    <div class="form-group col-12" id="generatedPasswordField" style="display: none;">
                        <label>Default Password</label>
                        <input type="text" id="generatedPasswordPreview" readonly value="PASSWORD">
                    </div>

                    <div class="form-group col-12">
                        <p class="form-instructions">Required items are marked explicitly with (<span class="required">*</span>). Admin and Super Admin accounts have their email and password generated automatically.</p>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="login.php" class="btn-link">Already have an account? Login</a>
                    <button class="btn-submit" type="submit">
                        <i class="fa-solid fa-user-plus"></i> Register
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function updateGeneratedEmailPreview() {
    const first = document.querySelector('[name="first_name"]').value.trim();
    const last = document.querySelector('[name="last_name"]').value.trim();
    const role = document.getElementById('roleSelect').value;

    if (!first || !last || !role) {
        document.getElementById('generatedEmailPreview').value = '';
        return;
    }

    let prefix = 'US';
    if (role === 'super admin') prefix = 'SA';
    else if (role === 'admin') prefix = 'AD';
    else if (role === 'applicant') prefix = 'AP';

    document.getElementById('generatedEmailPreview').value =
        prefix + first[0].toLowerCase() + last.toLowerCase().replace(/\s/g, '') + '@psuxizn.com';
}

function toggleRoleFields() {
    const role = document.getElementById('roleSelect').value;
    const isPrivileged = (role === 'admin' || role === 'super admin');

    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');

    const applicantEmailField = document.getElementById('applicantEmailField');
    const passwordField = document.getElementById('passwordField');
    const confirmPasswordField = document.getElementById('confirmPasswordField');
    const generatedEmailField = document.getElementById('generatedEmailField');
    const generatedPasswordField = document.getElementById('generatedPasswordField');

    if (isPrivileged) {
        applicantEmailField.style.display = 'none';
        passwordField.style.display = 'none';
        confirmPasswordField.style.display = 'none';

        emailInput.removeAttribute('required');
        passwordInput.removeAttribute('required');
        confirmPasswordInput.removeAttribute('required');
        // Disabled fields are not submitted, so the server always
        // generates these itself rather than trusting client values.
        emailInput.disabled = true;
        passwordInput.disabled = true;
        confirmPasswordInput.disabled = true;

        generatedEmailField.style.display = 'flex';
        generatedPasswordField.style.display = 'flex';

        updateGeneratedEmailPreview();
    } else {
        applicantEmailField.style.display = 'flex';
        passwordField.style.display = 'flex';
        confirmPasswordField.style.display = 'flex';

        emailInput.disabled = false;
        passwordInput.disabled = false;
        confirmPasswordInput.disabled = false;
        emailInput.setAttribute('required', 'required');
        passwordInput.setAttribute('required', 'required');
        confirmPasswordInput.setAttribute('required', 'required');

        generatedEmailField.style.display = 'none';
        generatedPasswordField.style.display = 'none';
    }
}

document.querySelector('[name="first_name"]').oninput = updateGeneratedEmailPreview;
document.querySelector('[name="last_name"]').oninput = updateGeneratedEmailPreview;
document.getElementById('roleSelect').onchange = toggleRoleFields;

// Standard Initialization Frame
toggleRoleFields();
</script>

</body>
</html>