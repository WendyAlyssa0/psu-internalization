<?php

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    exit();
}

$pdo = db();


$stmt = $pdo->prepare("
    SELECT *
    FROM audit_trail
    ORDER BY created_at DESC
    LIMIT 50
");

$stmt->execute();

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="content">

<div class="notification-card">

<div class="notification-header">

<h3>
<i class="ti ti-history"></i>
Audit Trail
</h3>

<p>
System activity records
</p>

</div>


<?php if (!empty($logs)): ?>

<?php foreach($logs as $log): ?>

<div class="notification-item">

<div class="notification-title">
<?= htmlspecialchars($log['action']) ?>
</div>

<div>
<?= htmlspecialchars($log['description']) ?>
</div>


<div class="notification-time">

<?= date(
'M d, Y g:i A',
strtotime($log['created_at'])
) ?>

</div>


</div>

<?php endforeach; ?>


<?php else: ?>

<div class="notification-empty">
No activity recorded.
</div>


<?php endif; ?>


</div>

</div>