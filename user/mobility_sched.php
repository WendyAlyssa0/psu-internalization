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
$schedule = [];

/*
UNCOMMENT WHEN DB IS READY

if ($user_id) {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT 
            program_name,
            destination,
            start_date,
            end_date,
            status
        FROM applications
        WHERE user_id = ?
        ORDER BY start_date ASC
    ");

    $stmt->execute([$user_id]);
    $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
?>

<link rel="stylesheet" href="../asset/css/mobility_sched.css">

<div class="content-card">

    <div class="page-header">
        <h2>Mobility Schedule</h2>
        <p>Your program timeline and travel schedule overview.</p>
    </div>

    <?php if (empty($schedule)): ?>

        <div class="empty-state">
            <h3>No Schedule Available</h3>
            <p>Your mobility schedule will appear once your application is approved.</p>
        </div>

    <?php else: ?>

        <div class="timeline">

            <?php foreach ($schedule as $s): ?>

                <?php
                    $status = strtolower($s['status'] ?? 'pending');

                    $statusClass = match ($status) {
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                        default => 'pending'
                    };
                ?>

                <div class="timeline-item">

                    <div class="timeline-dot <?= e($statusClass) ?>"></div>

                    <div class="timeline-content">

                        <h3><?= e($s['program_name'] ?? '') ?></h3>

                        <p class="location">
                            📍 <?= e($s['destination'] ?? '') ?>
                        </p>

                        <p class="date">
                            <?= e($s['start_date'] ?? '--') ?>
                            →
                            <?= e($s['end_date'] ?? '--') ?>
                        </p>

                        <span class="badge <?= e($statusClass) ?>">
                            <?= e(ucfirst($status)) ?>
                        </span>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>