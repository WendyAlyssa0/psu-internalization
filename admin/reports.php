<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

$monthlyStmt = $pdo->query("
    SELECT
        MONTH(created_at) AS month_num,
        COUNT(*) AS total
    FROM applications
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");

$monthLabels = [];
$monthCounts = [];

while ($row = $monthlyStmt->fetch(PDO::FETCH_ASSOC)) {
    $monthLabels[] = date('M', mktime(0, 0, 0, $row['month_num'], 1));
    $monthCounts[] = (int)$row['total'];
}
/* DASHBOARD COUNTS */

function countRows(PDO $pdo, string $table, ?string $where = null): int {
    try {
        $sql = "SELECT COUNT(*) FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }

        return (int) $pdo->query($sql)->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}


$totalApplicants = countRows($pdo, 'applications');
$submitted = countRows($pdo, 'applications', "status = 'submitted'");
$underReview = countRows($pdo, 'applications', "status = 'under_review'");
$approved = countRows($pdo, 'applications', "status = 'approved'");
$rejected = countRows($pdo, 'applications', "status = 'rejected'");
$inbound = countRows($pdo, 'mobility_students', "mobility_type = 'inbound'");
$outbound = countRows($pdo, 'mobility_students', "mobility_type = 'outbound'");
$pending = countRows($pdo, 'applications', "status = 'submitted' OR status = 'under_review'");
$travelRecords = countRows($pdo, 'travel_info');
$activityLogs = countRows($pdo, 'activity_logs');
$totalUsers = countRows($pdo, 'users');

?>

<div class="db-wrap">

    <p class="db-section-label">Applications</p>

    <div class="stat-grid">

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-users"></i>
                Total Applicants
            </div>
            <div class="stat-value">
                <?= number_format($totalApplicants) ?>
            </div>
            <div class="stat-sub">
                This academic year
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-arrow-down"></i>
                Inbound
            </div>
            <div class="stat-value">
                <?= number_format($inbound) ?>
            </div>
            <div class="stat-sub">
                Students incoming
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-arrow-up"></i>
                Outbound
            </div>
            <div class="stat-value">
                <?= number_format($outbound) ?>
            </div>
            <div class="stat-sub">
                Students outgoing
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-clock"></i>
                Pending
            </div>
            <div class="stat-value pending">
                <?= number_format($pending) ?>
            </div>
            <div class="stat-sub">
                Awaiting review
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-circle-check"></i>
                Approved
            </div>
            <div class="stat-value approved">
                <?= number_format($approved) ?>
            </div>
            <div class="stat-sub">
                Confirmed placements
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-plane"></i>
                Travel Records
            </div>
            <div class="stat-value">
                <?= number_format($travelRecords) ?>
            </div>
            <div class="stat-sub">
                Itineraries filed
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-file-lines"></i>
                Activity Logs
            </div>
            <div class="stat-value">
                <?= number_format($activityLogs) ?>
            </div>
            <div class="stat-sub">
                System events
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fa-solid fa-users"></i>
                Total Users
            </div>
            <div class="stat-value">
                <?= number_format($totalUsers) ?>
            </div>
            <div class="stat-sub">
                Registered accounts
            </div>
        </div>

    </div>

    <div class="chart-row">

    <div class="chart-card">
        <h3>Applications Per Month</h3>
        <canvas id="lineChart"></canvas>
    </div>

    <div class="chart-card">
        <h3>Application Status Breakdown</h3>
        <canvas id="doughnutChart"></canvas>
    </div>

</div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const statusData = {
    labels: ['Approved', 'Pending', 'Rejected'],
    datasets: [{
        data: [
            <?= $approved ?>,
            <?= $pending ?>,
            <?= $rejected ?>
        ],
        backgroundColor: [
            '#16a34a',
            '#f59e0b',
            '#dc2626'
        ]
    }]
};

new Chart(document.getElementById('doughnutChart'), {
    type: 'doughnut',
    data: statusData,
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($monthLabels) ?>,
        datasets: [{
            label: 'Applications',
            data: <?= json_encode($monthCounts) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,.1)',
            fill: true,
            tension: .4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

</script>