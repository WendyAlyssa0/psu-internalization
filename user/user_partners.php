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

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/* GET PARTNERS (SAFE VERSION) */
$stmt = $pdo->query("
    SELECT id, institution_name, country
    FROM partners
    ORDER BY institution_name ASC
");

$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($partners);
?>

<link rel="stylesheet" href="../asset/css/user_partner.css">

<div class="container-fluid">
    <div class="card">

        <div class="card-top-bar"></div>

        <!-- HEADER -->
        <div class="card-header">
            <div>
                <div class="card-title">Partner Institutions</div>
                <div class="card-sub">Global academic network directory</div>
            </div>

            <span class="count-pill" id="countBadge">
                <?= $total ?> Institutions
            </span>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SEARCH -->
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search by institution name or country...">
            </div>

            <!-- GRID -->
            <div class="partner-grid" id="partnerGrid">

                <?php if ($total > 0): ?>

                    <?php foreach ($partners as $p): ?>

                        <?php
                            $name = $p['institution_name'] ?? '';
                            $country = $p['country'] ?? '';
                            $search = strtolower($name . ' ' . $country);
                        ?>

                        <div class="partner-card" data-search="<?= h($search) ?>">

                            <div class="institution-name">
                                <?= h($name) ?>
                            </div>

                            <div class="country-badge">
                                <i class="fa-solid fa-location-dot"></i>
                                <?= h($country) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-table-view" style="padding: 4rem 0;">
                        <h3>No partner institutions yet</h3>
                        <p>Data will appear once admin adds institutions.</p>
                    </div>

                <?php endif; ?>

            </div>

            <!-- NO RESULTS -->
            <div id="noResults" class="empty-table-view" style="display:none; padding: 4rem 0;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No matches found</h3>
                <p>Try a different name or country.</p>
            </div>

        </div>
    </div>
</div>

<!-- SEARCH SCRIPT -->
<script>
document.getElementById('searchInput').addEventListener('input', function(e) {
    const val = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.partner-card');
    const noResults = document.getElementById('noResults');

    let visibleCount = 0;

    cards.forEach(card => {
        const matches = card.dataset.search.includes(val);

        if (val === "") {
            card.style.display = "block";
            visibleCount++;
        } else {
            card.style.display = matches ? "block" : "none";
            if (matches) visibleCount++;
        }
    });

    document.getElementById('countBadge').innerText =
        `${visibleCount} Institutions`;

    noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
});

const partners = <?= json_encode(array_map(fn($p) => [
    'name'    => $p['institution_name'],
    'country' => $p['country'],
], $partners)) ?>;

</script>