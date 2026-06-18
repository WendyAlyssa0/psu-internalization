<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/user_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php?page=applications');
    exit();
}

$pdo = db();

$applicant_id = $_SESSION['user_id'];
$program = trim($_POST['program'] ?? '');
$department = trim($_POST['department'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($program === '' || $department === '') {
    $_SESSION['error'] = 'Program and Department are required.';
    header('Location: home.php?page=applications');
    exit();
}

$documents_status = 'pending';

/* Generate Reference Number */
$application_reference = 'APP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

try {

    $stmt = $pdo->prepare("
        INSERT INTO applications (
            applicant_id,
            program,
            department,
            application_reference,
            notes,
            status,
            documents_status,
            submitted_at,
            created_at,
            updated_at
        )
        VALUES (
            ?, ?, ?, ?, ?,
            'Pending',
            ?,
            NOW(),
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        $applicant_id,
        $program,
        $department,
        $application_reference,
        $notes,
        $documents_status
    ]);

    $_SESSION['success'] = 'Application submitted successfully.';
    header('Location: home.php?page=applications');
    exit();

} catch (PDOException $e) {

    error_log($e->getMessage());
    $_SESSION['error'] = 'Unable to submit application at this time. Please try again.';
    header('Location: home.php?page=applications');
    exit();

}