<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$user_id = $_SESSION['user_id'] ?? null;

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$applications = [];

/*
if ($user_id) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
?>

<link rel="stylesheet" href="../asset/css/application_status.css">

<div class="content-card">

    <div class="page-header">
        <h2>Application Status</h2>
        <p>Track your submitted applications.</p>
    </div>

    <?php if (empty($applications)): ?>
        <div class="empty-state">
            <h3>No Applications Yet</h3>
            <p>Your submitted applications will appear here.</p>
        </div>
    <?php endif; ?>

</div>