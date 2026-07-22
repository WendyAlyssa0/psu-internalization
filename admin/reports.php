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


$totalApplicants = countRows(
    $pdo,
    'users',
    "user_role = 'applicant'"
);

$totalPrograms = countRows(
    $pdo,
    'programs'
);

$activePrograms = countRows($pdo,'programs',"status='Active'");
$upcomingPrograms = countRows($pdo,'programs',"status='Upcoming'");
$completedPrograms = countRows($pdo,'programs',"status='Completed'");

$pending = countRows(
    $pdo,
    'applications',
    "status = 'pending'"
);

$approved = countRows(
    $pdo,
    'applications',
    "status = 'approved'"
);

$rejected = countRows(
    $pdo,
    'applications',
    "status = 'rejected'"
);

$totalUsers = countRows(
    $pdo,
    'users'
);

$approvalRate = ($approved + $rejected) > 0
    ? round(($approved / ($approved + $rejected)) * 100)
    : 0;
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
    </div>

    <div class="stat-card">
        <div class="stat-label">
            <i class="fa-solid fa-graduation-cap"></i>
            Total Programs
        </div>
        <div class="stat-value">
            <?= number_format($totalPrograms) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">
            <i class="fa-solid fa-book-open"></i>
            Active Programs
        </div>
        <div class="stat-value">
            <?= number_format($activePrograms) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">
            <i class="fa-solid fa-clock"></i>
            Pending Applications
        </div>
        <div class="stat-value pending">
            <?= number_format($pending) ?>
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
    </div>

    <div class="stat-card">
        <div class="stat-label">
            <i class="fa-solid fa-circle-xmark"></i>
            Rejected
        </div>
        <div class="stat-value rejected">
            <?= number_format($rejected) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">
            <i class="fa-solid fa-user-shield"></i>
            Total Users
        </div>
        <div class="stat-value">
            <?= number_format($totalUsers) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">
            <i class="fa-solid fa-chart-line"></i>
            Approval Rate
        </div>
        <div class="stat-value">
            <?= $approvalRate ?>%
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

    <div class="chart-card">
        <h3>Programs by Status</h3>
        <canvas id="programChart"></canvas>
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

new Chart(document.getElementById('programChart'), {
    type: 'bar',
    data: {
        labels: ['Active', 'Upcoming', 'Completed'],
        datasets: [{
            label: 'Programs',
            data: [
                <?= $activePrograms ?>,
                <?= $upcomingPrograms ?>,
                <?= $completedPrograms ?>
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

</script>