<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$pdo = db();

/* =========================
   FETCH NOTIFICATIONS
========================= */
$stmt = $pdo->prepare("
    SELECT id, type, title, message, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");

$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$unreadCount = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unreadCount++;
}

/* ICON MAP */
$typeMap = [
    'application'  => ['ti-clipboard-check', 'Application Update'],
    'approval'     => ['ti-check', 'Approval Update'],
    'document'     => ['ti-file-alert', 'Document Requirement'],
    'reminder'     => ['ti-alarm', 'Travel Reminder'],
    'announcement' => ['ti-speakerphone', 'System Announcement'],
    'default'      => ['ti-bell', 'Notification']
];
?>

<link rel="stylesheet" href="../asset/css/notifications.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="content">

<div class="notification-card">

    <div class="notification-header">
        <div>
            <h3><i class="ti ti-bell"></i> Notifications</h3>
            <p>System-wide alerts and updates</p>
        </div>

        <?php if ($unreadCount > 0): ?>
            <span class="notification-badge" id="badge">
                <?= $unreadCount ?> unread
            </span>
        <?php endif; ?>
    </div>

    <div class="notification-filters">
        <button class="active" onclick="filter('all', this)">All</button>
        <button onclick="filter('unread', this)">Unread</button>
        <button onclick="filter('application', this)">Applications</button>
        <button onclick="filter('approval', this)">Approvals</button>
        <button onclick="filter('reminder', this)">Reminders</button>
        <button onclick="filter('announcement', this)">Announcements</button>
    </div>

    <div class="notification-list">

    <?php if (!empty($notifications)): ?>
        <?php foreach ($notifications as $n):

            $map = $typeMap[$n['type']] ?? $typeMap['default'];

            $icon = $map[0];
            $label = $map[1];

            $isUnread = !$n['is_read'];
        ?>

        <div class="notification-item <?= $isUnread ? 'unread' : '' ?>"
             data-id="<?= $n['id'] ?>"
             data-type="<?= h($n['type']) ?>"
             data-read="<?= $isUnread ? 'unread' : 'read' ?>"
             onclick="markRead(this)">

            <div class="unread-dot"></div>

            <div class="notification-icon">
                <i class="ti <?= h($icon) ?>"></i>
            </div>

            <div class="notification-content">
                <div class="notification-label"><?= h($label) ?></div>
                <div class="notification-title"><?= h($n['title']) ?></div>
                <div class="notification-message"><?= h($n['message']) ?></div>

                <div class="notification-time">
                    <i class="ti ti-clock"></i>
                    <?= date('M d, Y g:i A', strtotime($n['created_at'])) ?>
                </div>
            </div>

        </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="notification-empty">No notifications yet.</div>
    <?php endif; ?>

    </div>

    <div class="notification-footer">
        <span>Showing <?= count($notifications) ?> notifications</span>
        <button onclick="markAllRead()">Mark all as read</button>
    </div>

</div>

</div>

<script>
function markRead(el) {

    if (el.dataset.read === 'read') return;

    fetch('../admin/markread.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: el.dataset.id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            el.classList.remove('unread');
            el.dataset.read = 'read';
            el.querySelector('.unread-dot').style.display = 'none';
            updateBadge();
        }
    });
}

function markAllRead() {

    fetch('../admin/markallread.php', { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        if (res.success) {

            document.querySelectorAll('.notification-item').forEach(el => {
                el.classList.remove('unread');
                el.dataset.read = 'read';
                const dot = el.querySelector('.unread-dot');
                if (dot) dot.style.display = 'none';
            });

            updateBadge();
        }
    });
}

function updateBadge() {
    const unread = document.querySelectorAll('.notification-item.unread').length;
    const badge = document.getElementById('badge');

    if (!badge) return;

    if (unread > 0) {
        badge.textContent = unread + " unread";
        badge.style.display = '';
    } else {
        badge.style.display = 'none';
    }
}
function filter(type, btn) {

    document.querySelectorAll('.notification-item').forEach(el => {

        const matchType = type === 'all' || el.dataset.type === type;
        const matchRead =
            type !== 'unread' ? true : el.dataset.read === 'unread';
        el.style.display = (matchType && matchRead) ? '' : 'none';
    });

    document.querySelectorAll('.notification-filters button')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');
}
</script>