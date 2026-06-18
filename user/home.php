<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';

requireApplicant();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/user_login.php');
    exit();
}

/* PAGE ROUTING */
$page = trim((string)($_GET['page'] ?? 'home'));
$validPages = [
    'home', 'profile',
    'apply_program', 'my_applications', 'application_status',
    'upload_documents', 'download_forms',
    'partners', 'travel_info', 'schedule', 'activity_reports',
    'notifications', 'messages',
    'change_password', 'delete_account'
];
if (!in_array($page, $validPages, true)) { $page = 'home'; }

$firstName = $_SESSION['first_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applicant Portal — PSUxIZN</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../asset/css/home.css">
</head>
<body>

<div class="layout">

  <!-- ===================== SIDEBAR ===================== -->
<aside class="sidebar">

  <div class="sidebar-header">
    <img src="../asset/img/psu_logo.png" width="44" alt="PSU Logo">
    <div>
      <div class="logo-title">PSUxIZN</div>
      <div class="logo-subtitle">Applicant Portal</div>
    </div>
  </div>

  <nav class="sidebar-nav">

    <!-- HOME -->
    <a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">
      <i class="fa-solid fa-house"></i> Home
    </a>

    <!-- ================= MOBILITY APPLICATIONS ================= -->
    <div class="sidebar-dropdown <?= in_array($page, ['apply_program','my_applications','application_status']) ? 'open' : '' ?>">

      <div class="dropdown-toggle">
        <span><i class="fa-solid fa-plane-departure"></i> Mobility Applications</span>
        <i class="fa-solid fa-chevron-down"></i>
      </div>

      <div class="dropdown-menu">
        <a href="?page=apply_program" class="<?= $page === 'apply_program' ? 'active' : '' ?>">
          <i class="fa-solid fa-file-circle-plus"></i> Apply Program
        </a>

        <a href="?page=my_applications" class="<?= $page === 'my_applications' ? 'active' : '' ?>">
          <i class="fa-solid fa-folder-open"></i> My Applications
        </a>

        <a href="?page=application_status" class="<?= $page === 'application_status' ? 'active' : '' ?>">
          <i class="fa-solid fa-list-check"></i> Application Status
        </a>
      </div>
    </div>

    <!-- ================= DOCUMENTS ================= -->
    <div class="sidebar-dropdown <?= in_array($page, ['upload_documents','download_forms']) ? 'open' : '' ?>">

      <div class="dropdown-toggle">
        <span><i class="fa-solid fa-folder"></i> Documents</span>
        <i class="fa-solid fa-chevron-down"></i>
      </div>

      <div class="dropdown-menu">
        <a href="?page=upload_documents" class="<?= $page === 'upload_documents' ? 'active' : '' ?>">
          <i class="fa-solid fa-upload"></i> Upload Documents
        </a>

        <a href="?page=download_forms" class="<?= $page === 'download_forms' ? 'active' : '' ?>">
          <i class="fa-solid fa-download"></i> Download Forms
        </a>
      </div>
    </div>

    <!-- ================= MOBILITY CORE ================= -->
    <div class="sidebar-dropdown <?= in_array($page, ['partners','travel_info','schedule']) ? 'open' : '' ?>">

      <div class="dropdown-toggle">
        <span><i class="fa-solid fa-globe"></i> Mobility Core</span>
        <i class="fa-solid fa-chevron-down"></i>
      </div>

      <div class="dropdown-menu">
        <a href="?page=partners" class="<?= $page === 'partners' ? 'active' : '' ?>">
          <i class="fa-solid fa-handshake"></i> Partner Institutions
        </a>

        <a href="?page=travel_info" class="<?= $page === 'travel_info' ? 'active' : '' ?>">
          <i class="fa-solid fa-plane"></i> Travel Information
        </a>

        <a href="?page=schedule" class="<?= $page === 'schedule' ? 'active' : '' ?>">
          <i class="fa-solid fa-calendar-days"></i> Mobility Schedule
        </a>
      </div>
    </div>

    <!-- ================= ACTIVITY ================= -->
    <a href="?page=activity_reports" class="<?= $page === 'activity_reports' ? 'active' : '' ?>">
      <i class="fa-solid fa-clipboard-list"></i> Activity Reports
    </a>

    <!-- ================= COMMUNICATION ================= -->
    <a href="?page=notifications" class="<?= $page === 'notifications' ? 'active' : '' ?>">
      <i class="fa-solid fa-bell"></i> Notifications
    </a>

    <a href="?page=messages" class="<?= $page === 'messages' ? 'active' : '' ?>">
      <i class="fa-solid fa-envelope"></i> Messages
    </a>

  </nav>
</aside>

  <!-- ===================== MAIN ===================== -->
  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <div class="topbar-title">Applicant Portal</div>
        <div class="topbar-sub">PSUxIZN</div>
      </div>

      <div class="topbar-right">
        <div class="account-menu">
          <div class="account-trigger">
            <div class="account-avatar"><?= strtoupper(substr($firstName, 0, 1)) ?></div>
            <div>
              <div class="account-name"><?= htmlspecialchars($firstName) ?></div>
              <div class="account-role">Applicant</div>
            </div>
            <i class="fa-solid fa-chevron-down account-chevron"></i>
          </div>

          <div class="dropdown">
            <div class="dropdown-header">
              <div class="dh-name"><?= htmlspecialchars($firstName) ?></div>
              <div class="dh-email">Applicant Account</div>
            </div>
            <a href="?page=change_password">
              <i class="fa-solid fa-key"></i> Change Password
            </a>
            <div class="dropdown-divider"></div>
            <a href="?page=delete_account" class="danger"
               onclick="return confirm('Permanently delete your account?')">
              <i class="fa-solid fa-trash"></i> Delete Account
            </a>
            <div class="dropdown-divider"></div>
            <a href="../user/user_logout.php" class="danger">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <?php if ($page === 'home'): ?>

    <div class="home-center">
      <div class="welcome-card">

        <div class="welcome-eyebrow">
          <i class="fa-solid fa-globe"></i> Applicant Portal
        </div>

        <div class="welcome-title">
          Welcome back,<br><?= htmlspecialchars($firstName) ?>
        </div>

        <div class="welcome-text">
          Manage your international mobility applications, upload requirements, and track your status — all in one place.
        </div>

        <div class="welcome-divider"></div>

        <div class="quick-links">
          <a href="?page=my_applications" class="quick-link">
            <i class="fa-solid fa-file-lines"></i>
            <div>
              <div class="quick-link-label">My Applications</div>
              <div class="quick-link-sub">View &amp; track progress</div>
            </div>
          </a>
          <a href="?page=upload_documents" class="quick-link">
            <i class="fa-solid fa-folder-open"></i>
            <div>
              <div class="quick-link-label">Documents</div>
              <div class="quick-link-sub">Upload requirements</div>
            </div>
          </a>
          <a href="?page=apply_program" class="quick-link">
            <i class="fa-solid fa-graduation-cap"></i>
            <div>
              <div class="quick-link-label">Apply for a Program</div>
              <div class="quick-link-sub">Start a new application</div>
            </div>
          </a>
          <a href="?page=partners" class="quick-link">
            <i class="fa-solid fa-handshake"></i>
            <div>
              <div class="quick-link-label">Partner Institutions</div>
              <div class="quick-link-sub">Explore opportunities</div>
            </div>
          </a>
        </div>

      </div>
    </div>

    <?php elseif ($page === 'apply_program'): ?>
      <div class="box"><?php include __DIR__ . '/apply_program.php'; ?></div>
    <?php elseif ($page === 'my_applications'): ?>
      <div class="box"><?php include __DIR__ . '/my_applications.php'; ?></div>
    <?php elseif ($page === 'application_status'): ?>
      <div class="box"><?php include __DIR__ . '/application_status.php'; ?></div>
    <?php elseif ($page === 'upload_documents'): ?>
      <div class="box"><?php include __DIR__ . '/upload_documents.php'; ?></div>
    <?php elseif ($page === 'download_forms'): ?>
      <div class="box"><?php include __DIR__ . '/download_forms.php'; ?></div>
    <?php elseif ($page === 'partners'): ?>
      <div class="box"><?php include __DIR__ . '/user_partners.php'; ?></div>
    <?php elseif ($page === 'travel_info'): ?>
      <div class="box"><?php include __DIR__ . '/travel_information.php'; ?></div>
    <?php elseif ($page === 'schedule'): ?>
      <div class="box"><?php include __DIR__ . '/mobility_sched.php'; ?></div>
    <?php elseif ($page === 'activity_reports'): ?>
      <div class="box"><?php include __DIR__ . '/activity_reports.php'; ?></div>
    <?php elseif ($page === 'notifications'): ?>
      <div class="box"><?php include __DIR__ . '/user_notifications.php'; ?></div>
    <?php elseif ($page === 'messages'): ?>
      <div class="box"><?php include __DIR__ . '/messages.php'; ?></div>
    <?php elseif ($page === 'change_password'): ?>
      <div class="box"><?php include __DIR__ . '/change_password.php'; ?></div>
    <?php elseif ($page === 'delete_account'): ?>
      <div class="box"><?php include __DIR__ . '/delete_account.php'; ?></div>
    <?php endif; ?>
  </div><!-- /main -->
</div><!-- /layout -->

<script>
// Account dropdown
const accountMenu = document.querySelector('.account-menu');
document.querySelector('.account-trigger').addEventListener('click', e => {
  e.stopPropagation();
  accountMenu.classList.toggle('active');
});
document.addEventListener('click', () => accountMenu.classList.remove('active'));

// Sidebar dropdowns
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', () => {
    toggle.parentElement.classList.toggle('open');
  });
});
</script>
</body>
</html>