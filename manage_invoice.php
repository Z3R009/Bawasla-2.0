<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

// Retrieve users ordered alphabetically by fullname
$select = mysqli_query($connection, "
    SELECT 
        member_id, 
        CONCAT(last_name, ', ', first_name, ' ', middle_name) AS fullname, 
        tank_no, 
        meter_no, 
        address, 
        mobile_number 
    FROM members 
    ORDER BY fullname ASC
");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Invoice</title>
    <!-- Link to your local Bootstrap CSS file -->
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f1f1f1;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .action-dropdown:hover .dropdown-content {
            display: block;
        }

        td:nth-child(2),
        th:nth-child(2) {
            width: 140px;
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
                    <li><a class="dropdown-item" href="manage_account_treas.php?user_id=<?php echo $user_id; ?>"><i
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
        <?php include "Includes/header_admin.php"; ?>
        <?php include "Includes/sidebar_admin.php"; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 d-flex justify-content-between align-items-center">
                        Billing Statement
                    </h1>

                    <br>
                    <button class="btn btn-primary ms-3" type="button"
                        onclick="window.location.href='monthly_invoice.php?user_id=<?php echo $user_id; ?>'">
                        View Monthly Invoice
                    </button>

                    <br>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td>
                                                <a href="all_invoice.php?member_id=<?php echo $row['member_id']; ?>"
                                                    class="btn btn-primary">
                                                    View Invoice
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
            <?php include "Includes/footer.php"; ?>
        </div>
    </div>
    <!-- startbootstrap -->
    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script src="start/js/scripts.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <script src="start/assets/demo/chart-area-demo.js"></script>
    <script src="start/assets/demo/chart-bar-demo.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>

</body>

</html>