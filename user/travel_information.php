<?php
require_once __DIR__ . '/../config/db.php';
// ... (your existing session checks)

$pdo = db();
$user_id = $_SESSION['user_id'];

// 1. FETCH THE DATA
// Update this line with the correct table name found in your database
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.status,
        a.created_at,
        a.reviewed_at,
        a.mobility_type,
        a.institution,
        a.documents_status,

        p.program_name,
        p.program_type,
        p.country,

        u.first_name,
        u.last_name,
        u.email

    FROM applications a
    LEFT JOIN programs p ON a.program_id = p.id
    LEFT JOIN users u ON a.applicant_id = u.id
    WHERE a.applicant_id = ?
    ORDER BY a.created_at DESC
    LIMIT 1
");
$data = $stmt->fetch(PDO::FETCH_ASSOC); // This defines $data

// 2. Now the template code you copied will work because $data is set
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
<link rel="stylesheet" href="../asset/css/mobility.css">

<div class="mobility-wrapper">

    <div class="mobility-header">
        <h2>Mobility Information</h2>
        <p>Your application and travel details overview</p>
    </div>

    <?php if ($data): ?>

    <div class="mobility-card">

        <div class="mobility-top">

            <!-- APPLICANT -->
            <div class="mobility-section">
                <h3><i class="ti ti-user" aria-hidden="true"></i> Applicant</h3>
                <div class="mobility-avatar-row">
                    <div class="mobility-avatar">
                        <?= initials(h($data['first_name']), h($data['last_name'])) ?>
                    </div>
                    <div>
                        <div class="mobility-avatar-name">
                            <?= h($data['first_name'] . ' ' . $data['last_name']) ?>
                        </div>
                        <div class="mobility-avatar-email"><?= h($data['email']) ?></div>
                    </div>
                </div>
            </div>

            <!-- PROGRAM -->
            <div class="mobility-section">
                <h3><i class="ti ti-school" aria-hidden="true"></i> Program</h3>
                <div class="info">
                    <label>Program</label>
                    <span><?= h($data['program_name']) ?></span>
                </div>
                <div class="info">
                    <label>Type</label>
                    <span><?= h($data['program_type']) ?></span>
                </div>
                <div class="info">
                    <label>Department</label>
                    <span><?= h($data['mobility_type']) ?></span>
                </div>
            </div>

            <!-- DESTINATION -->
            <div class="mobility-section">
                <h3><i class="ti ti-map-pin" aria-hidden="true"></i> Destination</h3>
                <div class="info">
                    <label>Country</label>
                    <span><?= h($data['country']) ?></span>
                </div>
                <div class="info">
                    <label>Institution</label>
                    <span><?= h($data['institution']) ?></span>
                </div>
            </div>

        </div>

        <div class="mobility-footer">

            <div class="status-box">
                <label>Application status</label>
                <span class="status status-<?= strtolower(h($data['status'])) ?>">
                    <?= ucfirst(h($data['status'])) ?>
                </span>
            </div>

            <div class="status-box">
                <label>Documents</label>
                <span class="status status-<?= strtolower(h($data['documents_status'])) ?>">
                    <?= ucfirst(h($data['documents_status'])) ?>
                </span>
            </div>

            <div class="status-box">
                <label>Submitted</label>
                <span><?= date('M d, Y', strtotime($data['created_at'])) ?></span>
            </div>

            <div class="status-box">
                <label>Reviewed</label>
                <?php if ($data['reviewed_at']): ?>
                    <span><?= date('M d, Y', strtotime($data['reviewed_at'])) ?></span>
                <?php else: ?>
                    <span class="status-muted">Pending</span>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <?php else: ?>

    <div class="empty-card">
        <i class="ti ti-plane-off" aria-hidden="true"></i>
        <h3>No application found</h3>
        <p>You have not submitted a mobility application yet.</p>
    </div>

    <?php endif; ?>

</div>