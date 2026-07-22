<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$stmt = $pdo->query("
    SELECT
        p.program_name,
        pr.requirement_name
    FROM program_requirements pr
    INNER JOIN programs p
        ON p.id = pr.program_id
    ORDER BY p.program_name, pr.requirement_name
");

$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../asset/css/download_form.css">

<div class="content-card">

    <div class="page-header">
        <div>
            <h2>Program Requirements</h2>
            <p>Requirements assigned by the administrator for each mobility program.</p>
        </div>
    </div>

    <div class="form-list">

        <?php if (empty($requirements)): ?>

            <div class="form-item">
                <div>
                    <h3>No Requirements Found</h3>
                    <p>No program requirements have been configured yet.</p>
                </div>
            </div>

        <?php else: ?>

            <?php foreach ($requirements as $req): ?>

                <div class="form-item">

                    <div>
                        <h3><?= e($req['requirement_name']) ?></h3>
                        <p><?= e($req['program_name']) ?></p>
                    </div>

                    <button class="btn" disabled>
                        <i class="fa-solid fa-circle-check"></i>
                        Required
                    </button>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>