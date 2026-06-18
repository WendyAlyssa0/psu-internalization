<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/user_login.php');
    exit();
}

$pdo = db();

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* FETCH NOTIFICATIONS */
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.status,
        a.program,
        a.created_at,
        a.updated_at
    FROM applications a
    WHERE a.applicant_id = ?
      AND a.status IN ('approved', 'rejected')
    ORDER BY a.updated_at DESC
    LIMIT 20
");

$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* SESSION READ TRACKING */
if (!isset($_SESSION['read_notifications'])) {
    $_SESSION['read_notifications'] = [];
}

foreach ($notifications as $i => $n) {
    $notifications[$i]['is_read'] =
        in_array($n['id'], $_SESSION['read_notifications']);
}

$unreadCount = count(
    array_filter($notifications, fn($n) => !$n['is_read'])
);

$typeMap = [
    'approved' => ['ti-circle-check', 'APPLICATION APPROVED'],
    'rejected' => ['ti-circle-x', 'APPLICATION REJECTED']
];
?>

<link rel="stylesheet" href="../asset/css/user_notif.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="content">

    <div class="card-top-bar"></div>

    <div class="notification-card">

        <!-- HEADER -->
        <div class="notification-header">
            <div>
                <h3>
                    <i class="ti ti-bell"></i>
                    Notifications
                </h3>
                <p>
                    Updates on your application status and mobility requests.
                </p>
            </div>

            <?php if ($unreadCount > 0): ?>
                <span class="notification-badge" id="unread-badge">
                    <?= $unreadCount ?> Unread
                </span>
            <?php endif; ?>
        </div>

        <!-- FILTERS -->
        <div class="notification-filters">

            <button class="active"
                    onclick="filterNotifications('all', this)">
                All
            </button>

            <button onclick="filterNotifications('unread', this)">
                Unread
            </button>

            <button onclick="filterNotifications('approved', this)">
                Approved
            </button>

            <button onclick="filterNotifications('rejected', this)">
                Rejected
            </button>

        </div>

        <?php if (!empty($notifications)): ?>

            <div class="notification-list" id="notification-list">

                <?php foreach ($notifications as $n): ?>

                    <?php
                    $status = $n['status'];

                    [$icon, $label] =
                        $typeMap[$status]
                        ?? ['ti-bell', 'APPLICATION UPDATE'];

                    $isUnread = !$n['is_read'];

                    $program = $n['program'] ?? 'Unknown Program';

                    $message = match ($status) {

                        'approved' =>
                            "Your application for {$program} has been approved. You may now proceed with the next requirements.",

                        'rejected' =>
                            "Your application for {$program} was not approved. Please review the details provided by the Internationalization Office.",

                        default =>
                            "Your application status has been updated."
                    };

                    $date = !empty($n['updated_at'])
                        ? new DateTime($n['updated_at'])
                        : new DateTime();
                    ?>

                    <div
                        class="notification-item <?= $isUnread ? 'unread' : '' ?> <?= h($status) ?>"
                        data-id="<?= (int)$n['id'] ?>"
                        data-type="<?= h($status) ?>"
                        data-read="<?= $isUnread ? '0' : '1' ?>"
                        onclick="markRead(this)"
                    >

                        <div class="unread-dot"
                             <?= !$isUnread ? 'style="opacity:0"' : '' ?>>
                        </div>

                        <div class="notification-icon <?= h($status) ?>">
                            <i class="ti <?= h($icon) ?>"></i>
                        </div>

                        <div class="notification-content">

                            <div class="notification-label <?= h($status) ?>">
                                <?= h($label) ?>
                            </div>

                            <div class="notification-message">
                                <?= h($message) ?>
                            </div>

                            <div class="notification-time">
                                <i class="ti ti-clock"></i>
                                <?= $date->format('M d, Y · g:i A') ?>
                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- EMPTY FILTER RESULT -->
            <div class="notification-empty"
                 id="notification-empty"
                 style="display:none">

                <i class="ti ti-search"></i>

                <h3>No Notifications Found</h3>

                <p>
                    There are no notifications matching this filter.
                </p>

            </div>

            <!-- FOOTER -->
            <div class="notification-footer">

                <span id="notification-count">
                    Showing <?= count($notifications) ?> Application Updates
                </span>

                <button class="mark-read-btn"
                        onclick="markAllRead()">

                    <i class="ti ti-check"></i>
                    Mark All as Read

                </button>

            </div>

        <?php else: ?>

            <div class="notification-empty">

                <i class="ti ti-bell-off"></i>

                <h3>No Notifications Yet</h3>

                <p>
                    Updates regarding your applications and mobility programs
                    will appear here once available.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<div id="toast-container"></div>

<script>
function filterNotifications(type, btn)
{
    document.querySelectorAll('.notification-filters button')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');

    const items =
        document.querySelectorAll('.notification-item');

    let visible = 0;

    items.forEach(item => {

        const unread =
            item.getAttribute('data-read') === '0';

        const show =
            type === 'all' ||
            (type === 'unread' && unread) ||
            type === item.dataset.type;

        item.style.display = show ? 'flex' : 'none';

        if (show) visible++;
    });

    document.getElementById('notification-empty')
        .style.display = visible === 0 ? 'block' : 'none';

    document.getElementById('notification-count')
        .textContent =
            `Showing ${visible} Application Update${visible !== 1 ? 's' : ''}`;
}

function markRead(el)
{
    if (el.dataset.read === '1') return;

    fetch('../api/mark_read.php', {
        method:'POST',
        headers:{
            'Content-Type':'application/json'
        },
        body:JSON.stringify({
            id:parseInt(el.dataset.id)
        })
    })
    .then(r => r.json())
    .then(data => {

        if(data.success){

            el.classList.remove('unread');
            el.dataset.read = '1';

            const dot =
                el.querySelector('.unread-dot');

            if(dot){
                dot.style.opacity = '0';
            }

            updateBadge();
        }

    })
    .catch(() => {
        showToast('Network error.', 'error');
    });
}

function markAllRead()
{
    const btn =
        document.querySelector('.mark-read-btn');

    btn.disabled = true;
    btn.innerHTML =
        '<i class="ti ti-loader"></i> Marking...';

    fetch('../api/mark_all_read.php',{
        method:'POST'
    })
    .then(r => r.json())
    .then(data => {

        if(data.success){

            document
                .querySelectorAll('.notification-item.unread')
                .forEach(el => {

                    el.classList.remove('unread');
                    el.dataset.read = '1';

                    const dot =
                        el.querySelector('.unread-dot');

                    if(dot){
                        dot.style.opacity = '0';
                    }
                });

            updateBadge();

            showToast(
                `${data.updated} notification(s) marked as read.`
            );
        }

    })
    .finally(() => {

        btn.disabled = false;

        btn.innerHTML =
            '<i class="ti ti-check"></i> Mark All as Read';
    });
}

function updateBadge()
{
    const unread =
        document.querySelectorAll(
            '.notification-item[data-read="0"]'
        ).length;

    const badge =
        document.getElementById('unread-badge');

    if(badge){

        badge.textContent =
            unread + ' Unread';

        badge.style.display =
            unread > 0 ? '' : 'none';
    }
}

function showToast(message, type='info')
{
    const container =
        document.getElementById('toast-container');

    const toast =
        document.createElement('div');

    toast.className = `toast ${type}`;
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {

        toast.style.opacity = '0';

        setTimeout(() => {
            toast.remove();
        }, 300);

    }, 2500);
}
</script>