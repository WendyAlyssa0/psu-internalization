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

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* =========================
   FILTERS
========================= */
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

/* =========================
   FETCH TRAVEL INFO
========================= */
$sql = "
    SELECT
        t.*,
        CONCAT(u.first_name,' ',u.last_name) AS student_name
    FROM travel_info t
    LEFT JOIN users u ON t.student_id = u.id
    WHERE 1=1
";

$params = [];

/* SEARCH */
if ($search !== '') {
    $sql .= " AND (
        CONCAT(u.first_name,' ',u.last_name) LIKE ?
        OR t.passport_number LIKE ?
        OR t.flight_details LIKE ?
        OR t.accommodation LIKE ?
    )";

    $like = "%$search%";
    $params = array_merge($params, [$like,$like,$like,$like]);
}

/* STATUS FILTER */
if ($status !== '') {
    $sql .= " AND t.travel_status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$travel = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   STATS
========================= */
$stats = [
    'pending' => 0,
    'traveling' => 0,
    'completed' => 0
];

foreach ($travel as $t) {
    $s = strtolower($t['travel_status'] ?? '');
    if (isset($stats[$s])) $stats[$s]++;
}
?>

<link rel="stylesheet" href="../asset/css/applications.css">

<div class="content">

    <!-- =========================
         HEADER (APPLICATION STYLE)
    ========================== -->
    <div class="page-header">
        <h2>Travel Information</h2>
        <p>Manage travel requirements, logistics, and international movement tracking.</p>
    </div>

    <!-- =========================
         TOOLBAR (APPLICATION STYLE)
    ========================== -->
    <div class="toolbar">

        <form method="GET" class="search-wrap">
            <input type="hidden" name="page" value="travel_information">

            <i class="fa fa-search"></i>

            <input type="text"
                   name="search"
                   placeholder="Search travel records..."
                   value="<?= h($search) ?>">
        </form>

    </div>

    <!-- =========================
         FILTER BAR
    ========================== -->
    <form method="GET" class="filter-bar">

        <input type="hidden" name="page" value="travel_information">

        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="traveling">Traveling</option>
            <option value="completed">Completed</option>
        </select>

    </form>

    <!-- =========================
         TABLE
    ========================== -->
    <div class="table-card">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Passport</th>
                    <th>Visa</th>
                    <th>Flight</th>
                    <th>Accommodation</th>
                    <th>Clearance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!empty($travel)): ?>

                <?php foreach ($travel as $t): ?>

                <?php
                    $visaClass = match($t['visa_status'] ?? '') {
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                        default => 'status-pending'
                    };

                    $clearClass = match($t['clearance_status'] ?? '') {
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                        default => 'status-pending'
                    };

                    $travelClass = match($t['travel_status'] ?? '') {
                        'completed' => 'status-approved',
                        'traveling' => 'status-evaluation',
                        default => 'status-pending'
                    };
                ?>

                <tr>

                    <td>#<?= h($t['id']) ?></td>

                    <td><?= h($t['student_name'] ?? 'Unknown') ?></td>

                    <td><?= h($t['passport_number'] ?? '—') ?></td>

                    <td>
                        <span class="status-badge <?= $visaClass ?>">
                            <?= ucfirst($t['visa_status'] ?? 'pending') ?>
                        </span>
                    </td>

                    <td><?= h($t['flight_details'] ?? '—') ?></td>

                    <td><?= h($t['accommodation'] ?? '—') ?></td>

                    <td>
                        <span class="status-badge <?= $clearClass ?>">
                            <?= ucfirst($t['clearance_status'] ?? 'pending') ?>
                        </span>
                    </td>

                    <td>
                        <span class="status-badge <?= $travelClass ?>">
                            <?= ucfirst($t['travel_status'] ?? 'pending') ?>
                        </span>
                    </td>

                    <td>
                        <div class="actions">

                            <button class="action-btn evaluate-btn"
                                onclick="updateTravel(<?= $t['id'] ?>,'traveling')">
                                <span class="material-symbols-outlined">flight_takeoff</span>
                            </button>

                            <button class="action-btn approve-btn"
                                onclick="updateTravel(<?= $t['id'] ?>,'completed')">
                                <span class="material-symbols-outlined">check_circle</span>
                            </button>

                            <button class="action-btn reject-btn"
                                onclick="updateTravel(<?= $t['id'] ?>,'rejected')">
                                <span class="material-symbols-outlined">cancel</span>
                            </button>

                        </div>
                    </td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr class="empty-row">
                    <td colspan="9">No travel records found.</td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<script>
function updateTravel(id, status)
{
    fetch('update_travel.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed');
    })
    .catch(() => alert('Network error'));
}
</script>