<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$pdo = db();
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== 0) {
        $error = "Please select a valid file.";
    } else {

        $file = $_FILES['document'];

        // =========================
        // FILE SIZE LIMIT (5MB)
        // =========================
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $error = "File is too large. Maximum 5MB allowed.";
        } else {

            // =========================
            // SECURE MIME CHECK
            // =========================
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png'
            ];

            if (!in_array($mime, $allowedTypes)) {
                $error = "Invalid file type.";
            } else {

                // =========================
                // UPLOAD DIRECTORY
                // =========================
                $uploadDir = __DIR__ . '/../uploads/documents/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // =========================
                // FILE NAME (SAFE)
                // =========================
                $fileName = time() . '_' . bin2hex(random_bytes(5)) . '_' . basename($file['name']);
                $filePath = $uploadDir . $fileName;

                // =========================
                // MOVE FILE
                // =========================
                if (move_uploaded_file($file['tmp_name'], $filePath)) {

                    // =========================
                    // INSERT INTO DATABASE
                    // =========================
                    $stmt = $pdo->prepare("
                        INSERT INTO documents
                        (user_id, title, file_type, file_path, status, created_at)
                        VALUES (?, ?, ?, ?, 'pending', NOW())
                    ");

                    $stmt->execute([
                        $user_id,
                        pathinfo($file['name'], PATHINFO_FILENAME),
                        pathinfo($file['name'], PATHINFO_EXTENSION),
                        'uploads/documents/' . $fileName
                    ]);

                    $success = "Document uploaded successfully.";

                } else {
                    $error = "Upload failed. Try again.";
                }
            }
        }
    }
}
?>

<link rel="stylesheet" href="../asset/css/upload_docs.css">

<div class="content-card">

    <div class="page-header">
        <div>
            <h2>Upload Documents</h2>
            <p>Submit required files for your application.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="upload-card">

        <form method="POST" enctype="multipart/form-data" class="upload-form">

            <div class="form-group">
                <label>Select Document</label>
                <input type="file" name="document" required>
            </div>

            <button type="submit" class="btn-upload">
                Upload Document
            </button>

        </form>

    </div>

</div>