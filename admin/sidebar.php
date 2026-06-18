<?php
$page = $_GET['page'] ?? 'home';
?>

<div class="main-sidebar" id="mainSidebar">

        <div class="sidebar-header">
            <div class="brand">
                <img src="../asset/img/psu_logo.png" alt="PSU Logo" class="brand-logo">

                <div class="brand-text">
                    <h4>PSU</h4>
                    <small>Internalization System</small>
                </div>
            </div>
        </div>

    <!-- MENU -->
<ul class="sidebar-menu">

    <!-- DASHBOARD -->
    <li>
        <a href="dashboard.php?page=home"
           class="<?= $page === 'home' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
    </li>

    <!-- MASTER DATA -->
    <li class="sidebar-dropdown <?= in_array($page, [
        'countries',
        'partners',
        'agreement_types'
    ]) ? 'open' : '' ?>">

        <div class="dropdown-toggle">
            <span>
                <i class="fa-solid fa-database"></i>
                Master Data
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="dropdown-menu">

            <a href="dashboard.php?page=countries">
                <i class="fa-solid fa-earth-asia"></i>
                Country Management
            </a>

            <a href="dashboard.php?page=partners">
                <i class="fa-solid fa-building"></i>
                Partner Institutions
            </a>

            <a href="dashboard.php?page=agreement_types">
                <i class="fa-solid fa-file-signature"></i>
                Agreement Types
            </a>

        </div>
    </li>

    <!-- PROGRAM MANAGEMENT -->
    <li class="sidebar-dropdown <?= in_array($page, [
        'programs',
        'requirements',
        'forms'
    ]) ? 'open' : '' ?>">

        <div class="dropdown-toggle">
            <span>
                <i class="fa-solid fa-graduation-cap"></i>
                Program Management
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="dropdown-menu">

            <a href="dashboard.php?page=programs">
                <i class="fa-solid fa-book"></i>
                Programs
            </a>

            <a href="dashboard.php?page=requirements">
                <i class="fa-solid fa-list-check"></i>
                Requirements
            </a>

            <a href="dashboard.php?page=forms">
                <i class="fa-solid fa-file-pdf"></i>
                Forms
            </a>

        </div>
    </li>

    <!-- APPLICATION MANAGEMENT -->
    <li class="sidebar-dropdown <?= in_array($page, [
        'applications',
        'documents'
    ]) ? 'open' : '' ?>">

        <div class="dropdown-toggle">
            <span>
                <i class="fa-solid fa-file-circle-check"></i>
                Applications
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="dropdown-menu">

            <a href="dashboard.php?page=applications">
                <i class="fa-solid fa-file-lines"></i>
                Applications
            </a>

            <a href="dashboard.php?page=documents">
                <i class="fa-solid fa-folder-open"></i>
                Documents
            </a>

        </div>
    </li>

    <!-- STUDENT MOBILITY -->
    <li class="sidebar-dropdown <?= in_array($page, [
        'student_management',
        'travel_information',
        'activity_monitoring'
    ]) ? 'open' : '' ?>">

        <div class="dropdown-toggle">
            <span>
                <i class="fa-solid fa-plane-departure"></i>
                Student Mobility
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="dropdown-menu">

            <a href="dashboard.php?page=student_management">
                <i class="fa-solid fa-user-graduate"></i>
                Student Management
            </a>

            <a href="dashboard.php?page=travel_information">
                <i class="fa-solid fa-passport"></i>
                Travel Information
            </a>

            <a href="dashboard.php?page=activity_monitoring">
                <i class="fa-solid fa-chart-line"></i>
                Activity Monitoring
            </a>

        </div>
    </li>

    <!-- COMMUNICATION -->
    <li class="menu-label">Communication</li>

    <li>
        <a href="dashboard.php?page=notifications">
            <i class="fa-solid fa-bell"></i>
            <span>Notifications</span>
        </a>
    </li>

    <li>
        <a href="dashboard.php?page=messages">
            <i class="fa-solid fa-envelope"></i>
            <span>Messages</span>
        </a>
    </li>

    <!-- ADMINISTRATION -->
    <li class="menu-label">Administration</li>

    <li>
        <a href="dashboard.php?page=users">
            <i class="fa-solid fa-users"></i>
            <span>User Management</span>
        </a>
    </li>

    <li>
        <a href="dashboard.php?page=audit_trail">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Audit Trail</span>
        </a>
    </li>

</ul>

    <!-- FOOTER -->
    <div class="sidebar-footer">
        <a href="../public/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdowns = document.querySelectorAll('.sidebar-dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (!toggle) return;
        toggle.addEventListener('click', function () {
            dropdowns.forEach(item => {
                if(item !== dropdown){
                    item.classList.remove('open');
                }
            });
            dropdown.classList.toggle('open');
        });
    });
});
</script>

