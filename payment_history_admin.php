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


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

// retrieve
$select = mysqli_query($connection, "SELECT * FROM history ORDER BY payment_date DESC");

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
    <title>Payment History</title>
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

        td:nth-child(9),
        th:nth-child(9) {
            display: none;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <div id="layoutSidenav">
        <?php include "Includes/sidebar_admin.php"; ?>
        <?php include "Includes/header_admin.php"; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 d-flex justify-content-between align-items-center">
                        Payment History
                    </h1>


                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Member ID</th>
                                        <th>Reading ID</th>
                                        <th>Date</th>
                                        <th>Fullname</th>
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
                                                    Reading Date: <?php
                                                    echo date('F d, Y', strtotime($row['reading_date']));
                                                    ?>
                                                </span>
                                                <br>
                                                <span id="due_date_<?php echo $member_id; ?>">
                                                    Due Date: <?php
                                                    echo date('F d, Y', strtotime($row['due_date']));
                                                    ?>
                                                </span>
                                                <br>
                                                <span id="disconnection_date_<?php echo $member_id; ?>">
                                                    Disconnection Date:
                                                    <?php
                                                    echo date('F d, Y', strtotime($row['disconnection_date']));
                                                    ?>
                                                </span>
                                                <br>
                                                <span id="payment_date_<?php echo $member_id; ?>">
                                                    Payment Date:
                                                    <?php
                                                    echo date('F d, Y', strtotime($row['payment_date']));
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
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
            <?php include "Includes/footer.php"; ?>
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