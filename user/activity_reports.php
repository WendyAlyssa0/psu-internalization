<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* DEFAULT DATA (fallback) */
$activities = [];

/*
UNCOMMENT WHEN DB IS READY

if ($user_id) {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT 
            action,
            description,
            created_at
        FROM activity_logs
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");

    $stmt->execute([$user_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
?>

<link rel="stylesheet" href="../asset/css/activity_reports.css">

<div class="content-card">

    <div class="page-header">
        <h2>Activity Reports</h2>
        <p>Track your recent system activities and updates.</p>
    </div>

    <div class="activity-card">

        <?php if (empty($activities)): ?>

            <div class="empty-state">
                <h3>No Activity Found</h3>
                <p>Your actions and updates will appear here.</p>
            </div>

        <?php else: ?>

            <?php foreach ($activities as $a): ?>

                <?php
                    $action = strtolower($a['action'] ?? 'info');

                    $typeClass = match ($action) {
                        'upload' => 'upload',
                        'apply' => 'apply',
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                        default => 'info'
                    };
                ?>

                <div class="activity-item">

                    <div class="activity-icon <?= e($typeClass) ?>"></div>

                    <div class="activity-content">
                        <h3><?= e($a['action'] ?? 'Activity') ?></h3>
                        <p><?= e($a['description'] ?? '') ?></p>
                        <small><?= e($a['created_at'] ?? '') ?></small>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>