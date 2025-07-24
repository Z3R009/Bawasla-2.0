<style>
    .nav-link.active {
        background-color: #0d6efd !important;
        /* Bootstrap primary */
        color: #fff !important;
    }

    .nav-link.active i {
        color: #fff !important;
    }
</style>


<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside>
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <!-- Navbar Brand with logo-->
                    <a class="navbar-brand ps-3" href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
                        <img src="img/lg2.png" alt="Logo"
                            style="height: 100px; width: auto; max-width: 100%; margin-left: 38px; ">
                    </a>
                    <div class="sb-sidenav-menu-heading"></div>

                    <a class="nav-link <?php echo $currentPage == 'dashboard_admin.php' ? 'active' : ''; ?>"
                        href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>

                    <a class="nav-link <?php echo $currentPage == 'manage_users.php' ? 'active' : ''; ?>"
                        href="manage_users.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                        Manage Users
                    </a>

                    <a class="nav-link <?php echo $currentPage == 'manage_members.php' ? 'active' : ''; ?>"
                        href="manage_members.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                        Manage Members
                    </a>

                    <a class="nav-link <?php echo $currentPage == 'reports_admin.php' ? 'active' : ''; ?>"
                        href="reports_admin.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                        Billing Reports
                    </a>

                    <a class="nav-link <?php echo $currentPage == 'payment_history_admin.php' ? 'active' : ''; ?>"
                        href="payment_history_admin.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                        Payment History
                    </a>

                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                President
            </div>
        </nav>
    </div>
</aside>