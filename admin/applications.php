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
$search     = trim($_GET['search'] ?? '');
$program    = trim($_GET['program'] ?? '');
$department = trim($_GET['department'] ?? '');
$view       = $_GET['view'] ?? 'all';

/* =========================
   PAGINATION
========================= */
$limit  = 10;
$page   = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE BUILDER
========================= */
$where  = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (
        a.id LIKE ? OR
        a.program LIKE ? OR
        a.department LIKE ? OR
        CONCAT(u.first_name,' ',u.last_name) LIKE ?
    )";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($program !== '') {
    $where .= " AND a.program = ?";
    $params[] = $program;
}

if ($department !== '') {
    $where .= " AND a.department = ?";
    $params[] = $department;
}

if ($view === 'new') {
    $where .= " AND a.status = 'pending'";
} elseif ($view === 'review') {
    $where .= " AND a.status = 'under evaluation'";
}

/* =========================
   COUNT QUERY (SAFE FIX)
========================= */
$countSql = "
    SELECT COUNT(*)
    FROM applications a
    LEFT JOIN users u ON a.applicant_id = u.id
    $where
";

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalApplications = (int)$countStmt->fetchColumn();

/* =========================
   DATA QUERY
========================= */
$sql = "
SELECT
    a.id,
    a.mobility_type,
    a.institution,
    a.documents_status,
    a.created_at,
    a.reviewed_at,

    p.program_name AS program,
    p.program_type AS department,
    p.country_id AS country,
    p.status,

    CONCAT(u.first_name,' ',u.last_name) AS applicant_name,
    u.email AS applicant_email

FROM applications a

LEFT JOIN users u
    ON a.applicant_id = u.id

LEFT JOIN programs p
    ON a.program_id = p.id

$where

ORDER BY a.created_at DESC
LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($totalApplications / $limit);
?>

<link rel="stylesheet" href="../asset/css/applications.css">

<div class="content">

    <!-- HEADER -->
    <div class="page-header">
        <h2>Applications Management</h2>
        <p>Handles all submitted mobility applications.</p>
    </div>

    <!-- SEARCH + TABS -->
    <div class="toolbar">
        <div class="search-wrap">
            <i class="fa fa-search"></i>
            <input type="text"
                   id="searchInput"
                   placeholder="Search applications..."
                   value="<?= h($search) ?>">
        </div>

        <div class="tabs">
            <a href="?view=all"    class="<?= $view === 'all'    ? 'active' : '' ?>">All</a>
            <a href="?view=new"    class="<?= $view === 'new'    ? 'active' : '' ?>">New</a>
            <a href="?view=review" class="<?= $view === 'review' ? 'active' : '' ?>">Review</a>
        </div>
    </div>

    <!-- FILTERS -->
    <form method="GET" class="filter-bar">
        <input type="hidden" name="view" value="<?= h($view) ?>">

        <select name="program">
            <option value="">All Programs</option>
            <option value="BSIT" <?= $program === 'BSIT' ? 'selected' : '' ?>>BSIT</option>
            <option value="BSCS" <?= $program === 'BSCS' ? 'selected' : '' ?>>BSCS</option>
        </select>

        <select name="department">
            <option value="">All Departments</option>
            <option value="IT"          <?= $department === 'IT'          ? 'selected' : '' ?>>IT</option>
            <option value="Engineering" <?= $department === 'Engineering' ? 'selected' : '' ?>>Engineering</option>
            <option value="Business"    <?= $department === 'Business'    ? 'selected' : '' ?>>Business</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <!-- TABLE -->
    <div class="table-section">
        <table class="table-card">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Applicant</th>
                    <th>Program</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Country</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($applications): ?>
                <?php foreach ($applications as $a): ?>
                <tr>
                    <td>#<?= h($a['id']) ?></td>
                    <td><?= h($a['applicant_name']) ?></td>
                    <td><?= h($a['program']) ?></td>
                    <td><?= h($a['department']) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $a['status'])) ?>">
                            <?= ucfirst($a['status']) ?>
                        </span>
                    </td>
                    <td><?= h($a['country']) ?></td>
                    <td><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                    <td class="action-btns">
                        <button class="btn-view"
                                onclick="openModal(<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>)"
                                title="View">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn-approve"
                                onclick="confirmAction('approve', <?= h($a['id']) ?>)"
                                title="Approve">
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn-reject"
                                onclick="confirmAction('reject', <?= h($a['id']) ?>)"
                                title="Reject">
                            <i class="fa fa-close"></i>
                        </button>
                        <button class="btn-delete"
                                onclick="confirmAction('delete', <?= h($a['id']) ?>)"
                                title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="empty-row">No applications found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="pagination">
        <span>
            Showing <?= count($applications) ?> of <?= $totalApplications ?> applications
        </span>
        <div class="pages">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?p=<?= $i ?>&view=<?= h($view) ?>&search=<?= urlencode($search) ?>&program=<?= urlencode($program) ?>&department=<?= urlencode($department) ?>"
                   class="<?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

</div><!-- end .content -->

<!-- ================================
     VIEW MODAL
================================ -->
<div id="appModal" class="modal-overlay" onclick="closeModalOutside(event)">
    <div class="modal-box">

        <div class="modal-header">
            <h3>Application Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <div class="modal-body">

            <div class="modal-section">
                <h4>Applicant</h4>
                <div class="modal-grid">
                    <div class="modal-field">
                        <label>Name</label>
                        <span id="m-name"></span>
                    </div>
                    <div class="modal-field">
                        <label>Email</label>
                        <span id="m-email"></span>
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <h4>Application</h4>
                <div class="modal-grid">
                    <div class="modal-field">
                        <label>ID</label>
                        <span id="m-id"></span>
                    </div>
                    <div class="modal-field">
                        <label>Program</label>
                        <span id="m-program"></span>
                    </div>
                    <div class="modal-field">
                        <label>Department</label>
                        <span id="m-department"></span>
                    </div>
                    <div class="modal-field">
                        <label>Mobility Type</label>
                        <span id="m-mobility"></span>
                    </div>
                    <div class="modal-field">
                        <label>Institution</label>
                        <span id="m-institution"></span>
                    </div>
                    <div class="modal-field">
                        <label>Country</label>
                        <span id="m-country"></span>
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <h4>Status</h4>
                <div class="modal-grid">
                    <div class="modal-field">
                        <label>Application Status</label>
                        <span id="m-status"></span>
                    </div>
                    <div class="modal-field">
                        <label>Documents Status</label>
                        <span id="m-docs-status"></span>
                    </div>
                    <div class="modal-field">
                        <label>Submitted</label>
                        <span id="m-created"></span>
                    </div>
                    <div class="modal-field">
                        <label>Reviewed</label>
                        <span id="m-reviewed"></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Close</button>
        </div>

    </div>
</div>
<!-- END #appModal -->

<!-- ================================
     CONFIRM ACTION MODAL
================================ -->
<div id="confirmModal" class="modal-overlay" onclick="closeConfirmOutside(event)">
    <div class="modal-box modal-box--sm">

        <div class="modal-header">
            <h3 id="confirm-title">Confirm Action</h3>
            <button class="modal-close" onclick="closeConfirm()">&times;</button>
        </div>

        <div class="modal-body">
            <div class="confirm-icon" id="confirm-icon"></div>
            <p id="confirm-message" class="confirm-message"></p>
        </div>

        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeConfirm()">Cancel</button>
            <button id="confirm-btn" class="btn-confirm" onclick="executeAction()">Confirm</button>
        </div>

    </div>
</div>
<!-- END #confirmModal -->

<script>
/* === DEBOUNCED SEARCH === */
/* =====================================
   SEARCH (DEBOUNCED)
===================================== */
let searchTimer;

const searchInput = document.getElementById('searchInput');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value);
            url.searchParams.set('p', '1');
            window.location.href = url.toString();
        }, 400);
    });
}

/* =====================================
   MODAL: VIEW APPLICATION
===================================== */
function openModal(data) {
    try {
        if (!data) return;

        data = (typeof data === "string") ? JSON.parse(data) : data;

        const fmt = (val) =>
            (val !== null && val !== undefined && val !== '') ? val : '—';

        const formatDate = (val) => {
            if (!val) return '—';
            return new Date(val).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        };

        const statusHtml = (status) => {
            if (!status) return '—';
            const cls = status.toLowerCase().replace(/\s+/g, '-');
            return `<span class="status-badge status-${cls}">${status}</span>`;
        };

        document.getElementById('m-id').textContent          = '#' + fmt(data.id);
        document.getElementById('m-name').textContent        = fmt(data.applicant_name);
        document.getElementById('m-email').textContent       = fmt(data.applicant_email);
        document.getElementById('m-program').textContent     = fmt(data.program);
        document.getElementById('m-department').textContent  = fmt(data.department);
        document.getElementById('m-mobility').textContent    = fmt(data.mobility_type);
        document.getElementById('m-institution').textContent = fmt(data.institution);
        document.getElementById('m-country').textContent     = fmt(data.country);
        document.getElementById('m-status').innerHTML        = statusHtml(data.status);
        document.getElementById('m-docs-status').innerHTML   = statusHtml(data.documents_status);
        document.getElementById('m-created').textContent     = formatDate(data.created_at);
        document.getElementById('m-reviewed').textContent    = formatDate(data.reviewed_at);

        document.getElementById('appModal').classList.add('open');
        document.body.style.overflow = 'hidden';

    } catch (err) {
        console.error('Modal error:', err);
    }
}

function closeModal() {
    document.getElementById('appModal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeModalOutside(e) {
    if (e.target.id === 'appModal') closeModal();
}

/* =====================================
   CONFIRM ACTION MODAL
===================================== */
let pendingAction = null;
let pendingId = null;

const actionConfig = {
    approve: {
        title: "Approve Application",
        message: "Are you sure you want to approve this application?",
        icon: '<i class="fa fa-check-circle" style="color:#16a34a;font-size:2.2rem;"></i>',
        btnClass: "btn-confirm--approve",
        btnLabel: "Yes, Approve"
    },
    reject: {
        title: "Reject Application",
        message: "Are you sure you want to reject this application?",
        icon: '<i class="fa fa-times-circle" style="color:#dc2626;font-size:2.2rem;"></i>',
        btnClass: "btn-confirm--reject",
        btnLabel: "Yes, Reject"
    },
    delete: {
        title: "Delete Application",
        message: "This action cannot be undone.",
        icon: '<i class="fa fa-trash" style="color:#f59e0b;font-size:2.2rem;"></i>',
        btnClass: "btn-confirm--delete",
        btnLabel: "Yes, Delete"
    }
};

function confirmAction(action, id) {
    const cfg = actionConfig[action];
    if (!cfg) return;

    pendingAction = action;
    pendingId = id;

    document.getElementById('confirm-title').textContent = cfg.title;
    document.getElementById('confirm-message').textContent = cfg.message;
    document.getElementById('confirm-icon').innerHTML = cfg.icon;

    const btn = document.getElementById('confirm-btn');
    btn.textContent = cfg.btnLabel;
    btn.className = 'btn-confirm ' + cfg.btnClass;

    document.getElementById('confirmModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeConfirm() {
    document.getElementById('confirmModal').classList.remove('open');
    document.body.style.overflow = '';
    pendingAction = null;
    pendingId = null;
}

function closeConfirmOutside(e) {
    if (e.target.id === 'confirmModal') closeConfirm();
}

/* =====================================
   EXECUTE ACTION (AJAX)
===================================== */
function executeAction() {
    if (!pendingAction || !pendingId) return;

    const btn = document.getElementById('confirm-btn');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    const routeMap = {
        approve: 'application_approve.php',
        reject:  'application_reject.php',
        delete:  'application_delete.php'
    };

    const formData = new FormData();
    formData.append('id', pendingId);

    fetch(routeMap[pendingAction], {
        method: 'POST',
        body: formData
    })
    .then(async (res) => {
        const text = await res.text(); // IMPORTANT DEBUG STEP
        console.log("RAW RESPONSE:", text);

        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error("Invalid JSON: " + text);
        }
    })
    .then(data => {
        closeConfirm();

        showToast(
            data.success ? 'success' : 'error',
            data.message || 'Unknown response'
        );

        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(err => {
        console.error(err);
        closeConfirm();
        showToast('error', 'Server error. Check console.');
    });
}

/* =====================================
   TOAST NOTIFICATION
===================================== */
function showToast(type, message) {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;

    toast.innerHTML = `
        <i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('toast--show'), 50);

    setTimeout(() => {
        toast.classList.remove('toast--show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* =====================================
   ESC KEY HANDLER
===================================== */
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal();
        closeConfirm();
    }
});

document.getElementById('editProgramName').value =
    data.programs_name || '';

document.getElementById('editProgramType').value =
    data.program_type || '';

document.getElementById('editCountry').value =
    data.country || '';

document.getElementById('editStatus').value =
    data.status || '';

document.getElementById('editPartner').value =
    data.partner_institution || '';

document.getElementById('editStartDate').value =
    data.start_date || '';

document.getElementById('editEndDate').value =
    data.end_date || '';

document.getElementById('editDescription').value =
    data.description || '';
</script>