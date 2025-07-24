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
                    <a class="navbar-brand ps-3" href="dashboard_treasurer.php?user_id=<?php echo $user_id; ?>">
                        <img src="img/lg2.png" alt="Logo"
                            style="height: 100px; width: auto; max-width: 100%; margin-left: 38px; ">
                        <!-- The height is increased to 80px for a larger logo -->
                    </a>
                    <div class="sb-sidenav-menu-heading"></div>

                    <a class="nav-link <?php echo $currentPage == 'dashboard_treasurer.php' ? 'active' : ''; ?>"
                        href="dashboard_treasurer.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                    <a class="nav-link <?php echo $currentPage == 'transaction_treas.php' ? 'active' : ''; ?>"
                        href="transaction_treas.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-transfer"></i></div>
                        Manage Transaction
                    </a>
                    <a class="nav-link <?php echo ($currentPage == 'invoice_treas.php' || $currentPage == 'all_invoice.php') ? 'active' : ''; ?>"
                        href="invoice_treas.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-regular fa-file"></i></div>
                        Manage Invoice
                    </a>

                    <a class="nav-link <?php echo $currentPage == 'pending_treas.php' ? 'active' : ''; ?>"
                        href="pending_treas.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-clock"></i></div>
                        Pending Payment
                    </a>
                    <a class="nav-link <?php echo $currentPage == 'reports_treas.php' ? 'active' : ''; ?>"
                        href="reports_treas.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                        Billing Reports
                    </a>
                    <!-- <a class="nav-link" href="index.html">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Activity Logs
                                </a> -->
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                Treasurer
            </div>
        </nav>
    </div>
</aside>