<?php
include 'DBConnection.php';

// Check if user is logged in
// if (isset($_SESSION['member_id'])) {
//     $member_id = $_SESSION['member_id'];
// } else {
//     // Handle the case when the member_id is not set, e.g., redirect to login
//     header('Location: index.php');
//     exit();
// }

$fullname = ""; // Initialize variable to avoid errors

if (isset($_GET['member_id'])) {
    $member_id = $_GET['member_id'];
    $member_id = mysqli_real_escape_string($connection, $member_id);

    // Fetch member information
    $sql = "SELECT 
                CONCAT(members.last_name, ', ', members.first_name) AS fullname 
            FROM members 
            WHERE members.member_id = '$member_id'";

    // Execute the query
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // Fetch the full name of the member
        $row = mysqli_fetch_assoc($result);
        $fullname = $row['fullname'];
    } else {
        echo "Error fetching member: " . mysqli_error($connection);
    }
} else {
    echo "No member ID provided.";
}


// retrieve
$select = mysqli_query($connection, "SELECT * FROM history WHERE member_id = '$member_id'");

// Check for errors in the SQL query
if (!$select) {
    echo "Error in query: " . mysqli_error($connection);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Offline Example</title>
    <!-- Link to your local Bootstrap CSS file -->
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        td:nth-child(1),
        th:nth-child(1) {
            display: none;
        }

        td:nth-child(2),
        th:nth-child(2) {
            display: none;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3"></a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i
                class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="manage_account_member.php?member_id=<?php echo $member_id; ?>"><i
                                class="fa-solid fa-gear"></i><span style="margin-left: 20px; font-size: large; ">
                                Account Settings</span></a></li>
                    <li><a class="dropdown-item" href="#!"><i class="fa-solid fa-file"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Activity Logs</span></a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Log Out</span></a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- Navbar Brand with logo-->
                        <a class="navbar-brand ps-3" href="dashboard_member.php?member_id=<?php echo $member_id; ?>">
                            <img src="img/lg2.png" alt="Logo"
                                style="height: 100px; width: auto; max-width: 100%; margin-left: 38px; ">
                            <!-- The height is increased to 80px for a larger logo -->
                        </a>
                        <div class="sb-sidenav-menu-heading"></div>
                        <a class="nav-link" href="dashboard_member.php?member_id=<?php echo $member_id; ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Manage Transaction
                        </a>
                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-book"></i></div>
                            View Payment History
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    User
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h3 class="mt-4 d-flex justify-content-between align-items-center">
                        Payment History of <?php echo $fullname; ?>
                    </h3>


                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Member ID</th>
                                        <th>Reading ID</th>
                                        <th>Date</th>
                                        <th>Current Charges</th>
                                        <th>Total Amount Due</th>
                                        <th>Amount Paid</th>
                                        <th>Billing Month</th>
                                        <th>Payment Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['member_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['reading_id']); ?></td>

                                            <td>
                                                <span id="reading_date_<?php echo $member_id; ?>">
                                                    Reading Date: <?php echo htmlspecialchars($row['reading_date']); ?>
                                                </span>
                                                <br>
                                                <span id="due_date_<?php echo $member_id; ?>">
                                                    Due Date: <?php echo htmlspecialchars($row['due_date']); ?>
                                                </span>
                                                <br>
                                                <span id="disconnection_date_<?php echo $member_id; ?>">
                                                    Disconnection Date:
                                                    <?php echo htmlspecialchars($row['disconnection_date']); ?>
                                                </span>
                                                <br>
                                                <span id="payment_date_<?php echo $member_id; ?>">
                                                    Payment Date:
                                                    <?php echo htmlspecialchars($row['payment_date']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['current_charges']); ?></td>
                                            <td><?php echo htmlspecialchars($row['total_amount_due']); ?></td>
                                            <td><?php echo htmlspecialchars($row['amount_paid']); ?></td>

                                            <td><?php echo htmlspecialchars($row['billing_month']); ?></td>
                                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
            <!-- <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer> -->
        </div>
    </div>
    <!-- startbootstrap -->
    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script src="start/js/scripts.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <script src="chart/chart.js"></script>
    <script src="start/assets/demo/chart-bar-demo.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>
</body>

</html>