<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();
$user_id = $_SESSION['user_id'] ?? null;

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* AVAILABLE PROGRAMS */
$stmt = $pdo->query("
    SELECT *
    FROM programs
    WHERE status = 'Upcoming'
    ORDER BY created_at DESC
");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ALREADY APPLIED PROGRAM IDs */
$appliedStmt = $pdo->prepare("
    SELECT program_id
    FROM applications
    WHERE applicant_id = ?
");
$appliedStmt->execute([$user_id]);
$appliedIds = $appliedStmt->fetchAll(PDO::FETCH_COLUMN);

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $program_id = (int)($_POST['program_id'] ?? 0);
    $reason     = trim($_POST['reason'] ?? '');

    if (!$program_id) {
        $errors[] = 'Please select a program.';
    }

    if (!$reason) {
        $errors[] = 'Statement of purpose is required.';
    }

    /* DUPLICATE CHECK */
    if ($program_id && in_array($program_id, $appliedIds)) {
        $errors[] = 'You have already applied to this program.';
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO applications
            (
                applicant_id,
                program_id,
                reason,
                status,
                created_at
            )
            VALUES (?, ?, ?, 'Pending', NOW())
        ");

        $stmt->execute([$user_id, $program_id, $reason]);

        /* refresh applied IDs after new submission */
        $appliedIds[] = $program_id;

        $success = 'Application submitted successfully.';
    }
}

$check = $pdo->prepare("
    SELECT id
    FROM applications
    WHERE applicant_id = ?
    AND program_id = ?
");

$check->execute([$user_id, $program_id]);

if ($check->fetch()) {
    $errors[] = 'You have already applied for this program.';
}
?>

<link rel="stylesheet" href="../asset/css/apply_addition.css">

<div class="content-card">

    <div class="page-header">
        <div class="page-header-icon">
            <i class="fa-solid fa-plane-departure"></i>
        </div>
        <div>
            <h2>Apply for a Mobility Program</h2>
            <p>Select an available program and submit your application.</p>
        </div>
    </div>

    <!-- AVAILABLE PROGRAMS -->
    <div class="program-section">

        <h3 class="section-title">
            <i class="fa-solid fa-graduation-cap"></i>
            Available Programs
        </h3>

        <div class="program-grid">

            <?php foreach ($programs as $program): ?>
                <?php $alreadyApplied = in_array($program['id'], $appliedIds); ?>

                <div class="program-card <?= $alreadyApplied ? 'program-card--applied' : '' ?>">

                    <div class="program-card-header">
                        <h4><?= e($program['program_name']) ?></h4>
                        <?php if ($alreadyApplied): ?>
                            <span class="applied-badge">
                                <i class="fa-solid fa-circle-check"></i>
                                Applied
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="program-card-body">
                        <p><?= e($program['description']) ?></p>
                        <div class="program-meta">
                            <span>
                                <i class="fa-solid fa-building"></i>
                                <?= e($program['partner_institution']) ?>
                            </span>
                            <span>
                                <i class="fa-solid fa-location-dot"></i>
                                <?= e($program['country']) ?>
                            </span>
                            <span>
                                <i class="fa-solid fa-calendar"></i>
                                <?= e($program['start_date']) ?> - <?= e($program['end_date']) ?>
                            </span>
                        </div>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <?= e($success) ?>
            <a href="home.php?page=my_applications" class="alert-link">
                View My Applications →
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- APPLICATION FORM -->
    <form method="POST" class="apply-form" id="applyForm">

        <div class="form-group">
            <label>Select Program</label>
            <select name="program_id" id="programSelect" required>
                <option value="">Choose a Program</option>
                <?php foreach ($programs as $program): ?>
                    <option
                        value="<?= $program['id'] ?>"
                        data-country="<?= e($program['country']) ?>"
                        data-partner="<?= e($program['partner_institution']) ?>"
                        data-start="<?= e($program['start_date']) ?>"
                        data-end="<?= e($program['end_date']) ?>"
                        data-applied="<?= in_array($program['id'], $appliedIds) ? '1' : '0' ?>"
                    >
                        <?= e($program['program_name']) ?>
                        <?= in_array($program['id'], $appliedIds) ? '(Already Applied)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="duplicate-warning" id="duplicateWarning" style="display:none;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                You have already applied to this program. Duplicate applications are not allowed.
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Partner Institution</label>
                <input type="text" id="partner" readonly>
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" id="country" readonly>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Start Date</label>
                <input type="text" id="startDate" readonly>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="text" id="endDate" readonly>
            </div>
        </div>

        <div class="form-group">
            <label>Statement of Purpose</label>
            <textarea
                name="reason"
                rows="5"
                maxlength="500"
                placeholder="Explain why you want to join this mobility program..."
                required
            ></textarea>
        </div>

        <div class="form-footer">
            <a href="home.php?page=my_applications" class="btn btn-outline">
                <i class="fa-solid fa-folder-open"></i>
                My Applications
            </a>
            <button type="submit" class="btn" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i>
                Submit Application
            </button>
        </div>

    </form>

</div>

<script>
const select        = document.getElementById('programSelect');
const duplicateWarn = document.getElementById('duplicateWarning');
const submitBtn     = document.getElementById('submitBtn');

select.addEventListener('change', function () {
    const option = this.options[this.selectedIndex];

    document.getElementById('partner').value   = option.dataset.partner || '';
    document.getElementById('country').value   = option.dataset.country || '';
    document.getElementById('startDate').value = option.dataset.start   || '';
    document.getElementById('endDate').value   = option.dataset.end     || '';

    const alreadyApplied = option.dataset.applied === '1';
    duplicateWarn.style.display = alreadyApplied ? 'flex' : 'none';
    submitBtn.disabled          = alreadyApplied;
    submitBtn.classList.toggle('btn--disabled', alreadyApplied);
});
</script>