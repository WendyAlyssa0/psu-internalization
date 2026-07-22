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

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        a.id AS application_id,
        a.status AS app_status,
        a.created_at AS date_applied,

        p.program_name,
        p.start_date,
        p.end_date,

        pr.institution_name AS partner_institution,
        pr.country

    FROM applications a

    LEFT JOIN programs p
        ON p.id = a.program_id

    LEFT JOIN partners pr
        ON pr.id = p.partner_id

    WHERE a.applicant_id = ?

    ORDER BY a.created_at DESC
");

$stmt->execute([$user_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<link rel="stylesheet" href="../asset/css/my_applications.css">

<div class="content-card">

    <div class="page-header">
        <div class="page-header-icon">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div>
            <h2>My Applications</h2>
            <p>Track the status of all your submitted mobility program applications.</p>
        </div>
    </div>

    <?php if (empty($applications)): ?>

        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <h3>No applications yet</h3>
            <p>You haven't applied to any mobility programs. Browse available programs and submit your first application.</p>
        </div>

    <?php else: ?>

        <!-- SUMMARY BADGES -->
            <?php
            $total = count($applications);

            $pending = count(array_filter(
                $applications,
                fn($a) => strtolower($a['app_status']) === 'pending'
            ));

            $approved = count(array_filter(
                $applications,
                fn($a) => strtolower($a['app_status']) === 'approved'
            ));

            $rejected = count(array_filter(
                $applications,
                fn($a) => strtolower($a['app_status']) === 'rejected'
            ));
            ?>

        <div class="summary-bar">
            <div class="summary-item active-filter" data-filter="all">
                <span class="summary-count"><?= $total ?></span>
                <span class="summary-label">Total</span>
            </div>
            <div class="summary-item" data-filter="Pending">
                <span class="summary-count pending"><?= $pending ?></span>
                <span class="summary-label">Pending</span>
            </div>
            <div class="summary-item" data-filter="Approved">
                <span class="summary-count approved"><?= $approved ?></span>
                <span class="summary-label">Approved</span>
            </div>
            <div class="summary-item" data-filter="Rejected">
                <span class="summary-count rejected"><?= $rejected ?></span>
                <span class="summary-label">Rejected</span>
            </div>
        </div>

        <!-- APPLICATIONS TABLE -->
        <div class="table-wrapper">
            <table class="applications-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Program</th>
                        <th>Partner Institution</th>
                        <th>Country</th>
                        <th>Duration</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="applicationsBody">
                    <?php foreach ($applications as $i => $app): ?>
                        <tr data-status="<?= ucfirst(strtolower($app['app_status'])) ?>">                            <td class="row-num"><?= $i + 1 ?></td>

                            <td class="program-name-cell">
                                <?= e($app['program_name']) ?>
                            </td>

                            <td>
                                <span class="meta-with-icon">
                                    <i class="fa-solid fa-building"></i>
                                    <?= e($app['partner_institution']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="meta-with-icon">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= e($app['country']) ?>
                                </span>
                            </td>

                            <td class="date-range">
                                <?php if (!empty($app['start_date'])): ?>
                                    <?= e(date('M j, Y', strtotime($app['start_date']))) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>

                                <span class="date-sep">–</span>

                                <?php if (!empty($app['end_date'])): ?>
                                    <?= e(date('M j, Y', strtotime($app['end_date']))) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td class="date-applied">
                                <?= e(date('M j, Y', strtotime($app['date_applied']))) ?>
                            </td>

                            <td>
                                <?php
                                $status = ucfirst(strtolower($app['app_status']));

                                $badge_class = match($status) {
                                    'Approved' => 'badge-approved',
                                    'Rejected' => 'badge-rejected',
                                    default    => 'badge-pending',
                                };

                                $icon = match($status) {
                                    'Approved' => 'fa-circle-check',
                                    'Rejected' => 'fa-circle-xmark',
                                    default    => 'fa-clock',
                                };
                                ?>

                                <span class="status-badge <?= $badge_class ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                    <?= e($status) ?>
                                </span>
                            </td>

                            <td>
                                <?php
                                $jsonApp = htmlspecialchars(
                                    json_encode($app, JSON_HEX_APOS | JSON_HEX_QUOT),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                                <button
                                    class="btn-view"
                                    onclick='openModal(<?= $jsonApp ?>)'
                                >
                                    <i class="fa-solid fa-eye"></i>
                                    View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="no-results" id="noResults" style="display:none;">
                <i class="fa-solid fa-filter-circle-xmark"></i>
                No applications match this filter.
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- ===================== MODAL ===================== -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()">
    <div class="modal" onclick="event.stopPropagation()">

        <div class="modal-header">
            <div class="modal-header-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h3 id="modalProgramName"></h3>
                <p id="modalPartner"></p>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">

            <div class="modal-grid">
                <div class="modal-field">
                    <span class="modal-field-label">
                        <i class="fa-solid fa-location-dot"></i> Country
                    </span>
                    <span class="modal-field-value" id="modalCountry"></span>
                </div>
                <div class="modal-field">
                    <span class="modal-field-label">
                        <i class="fa-solid fa-calendar-days"></i> Start Date
                    </span>
                    <span class="modal-field-value" id="modalStart"></span>
                </div>
                <div class="modal-field">
                    <span class="modal-field-label">
                        <i class="fa-solid fa-calendar-check"></i> End Date
                    </span>
                    <span class="modal-field-value" id="modalEnd"></span>
                </div>
                <div class="modal-field">
                    <span class="modal-field-label">
                        <i class="fa-solid fa-clock"></i> Date Applied
                    </span>
                    <span class="modal-field-value" id="modalDateApplied"></span>
                </div>
            </div>

            <div class="modal-field modal-field-full">
                <span class="modal-field-label">
                    <i class="fa-solid fa-align-left"></i> Program Description
                </span>
                <p class="modal-field-text" id="modalDescription"></p>
            </div>

            <div class="modal-status-row">
                <span class="modal-field-label">
                    <i class="fa-solid fa-circle-info"></i> Application Status
                </span>
                <span class="status-badge" id="modalStatus"></span>
            </div>

        </div>

    </div>

    
</div>

<script>
/* ---- FILTER ---- */
document.querySelectorAll('.summary-item').forEach(item => {
    item.addEventListener('click', function () {
        document.querySelectorAll('.summary-item').forEach(el => el.classList.remove('active-filter'));
        this.classList.add('active-filter');

        const filter = this.dataset.filter;
        const rows   = document.querySelectorAll('#applicationsBody tr');
        let visible  = 0;

        rows.forEach(row => {
            const show = filter === 'all' || row.dataset.status === filter;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('noResults').style.display = visible === 0 ? 'flex' : 'none';
    });
});

/* ---- MODAL ---- */
function openModal(app) {
    document.getElementById('modalProgramName').textContent  = app.program_name;
    document.getElementById('modalPartner').textContent      = app.partner_institution;
    document.getElementById('modalCountry').textContent      = app.country;
    document.getElementById('modalStart').textContent        = formatDate(app.start_date);
    document.getElementById('modalEnd').textContent          = formatDate(app.end_date);
    document.getElementById('modalDateApplied').textContent  = formatDate(app.date_applied);
    document.getElementById('modalDescription').textContent ='No description available.';
    const status =
        app.app_status.charAt(0).toUpperCase() +
        app.app_status.slice(1).toLowerCase();    const statusEl   = document.getElementById('modalStatus');
    const badgeClass = status === 'Approved' ? 'badge-approved'
                     : status === 'Rejected' ? 'badge-rejected'
                     : 'badge-pending';
    const iconClass  = status === 'Approved' ? 'fa-circle-check'
                     : status === 'Rejected' ? 'fa-circle-xmark'
                     : 'fa-clock';

    statusEl.className = 'status-badge ' + badgeClass;
    statusEl.innerHTML = `<i class="fa-solid ${iconClass}"></i> ${status}`;

    document.getElementById('modalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});
</script>