<?php

require_once __DIR__ . '/../config/auth_middleware.php';
requireAdmin();

/*
|--------------------------------------------------------------------------
| PAGE ROUTING
|--------------------------------------------------------------------------
| Each key is a valid ?page= value, mapped to the file that renders it.
| Unknown or missing pages fall back to 'home'.
*/
$pages = [
    // Dashboard
    'home' => 'reports.php',

    // Program Management
    'programs'        => 'programs.php',
    'partners'        => 'partners.php',
    'agreement_types' => 'agreement_types.php',

    // Location Management
    'countries_addresses' => 'countries_addresses.php',

    // Application Management
    'applications'          => 'applications.php',
    'documents'             => 'documents.php',
    'requirements'          => 'requirements.php',
    'program_requirements' => 'program_requirements.php',

    // Form Management
    'forms' => 'forms.php',

    // Mobility Management
    'approved_students'  => 'approved_students.php',
    'travel_information' => 'travel_information.php',
    'activity_reports'   => 'activity_reports.php',

    // Communication
    'notifications'  => 'notifications.php',
    'announcements'  => 'announcements.php',

    // Administration
    'users'       => 'users.php',
    'reports'     => 'reports.php',
    'audit_trail' => 'audit_trail.php',
];

$page = $_GET['page'] ?? 'home';

if (!array_key_exists($page, $pages)) {
    $page = 'home';
}

$firstName = $_SESSION['first_name'] ?? 'Administrator';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PSU Internalization Management System</title>

<link rel="stylesheet" href="../asset/css/dashboard.css">
<link rel="stylesheet" href="../asset/css/report.css">

<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
</head>
<body>

<!-- SIDEBAR -->
<?php require_once 'sidebar.php'; ?>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <span class="topbar-title">PSU Internalization</span>
  </div>

  <div class="user">
    <span class="material-symbols-outlined">account_circle</span>
    <span><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?></span>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">
  <?php require $pages[$page]; ?>
</div>

<script src="../asset/js/dashboard.js"></script>
</body>
</html>