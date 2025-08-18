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
$month_filter = "";
if (isset($_GET['month']) && $_GET['month'] !== "") {
    $selected_month = $_GET['month'];
    $month_filter = "WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$selected_month'";
}

$select = mysqli_query($connection, "SELECT * FROM history $month_filter ORDER BY payment_date DESC");


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

        /* Hide on screen */
        @media screen {

            td:nth-child(1),
            th:nth-child(1),
            td:nth-child(2),
            th:nth-child(2) {
                display: none;
            }
        }

        /* Show when printing */
        @media print {

            td:nth-child(1),
            th:nth-child(1),
            td:nth-child(2),
            th:nth-child(2) {
                display: table-cell;
            }
        }

        /* Force Long Bond Paper Layout and Add Margins */
        @media print {
            @page {
                size: 8.5in 13in;
                /* Long bond paper size */
                margin: 20mm;
                /* Adjust margin as needed */
            }

            body {
                margin: 0;
                padding: 0;
            }

            #print-section {
                display: block !important;
                /* Ensure printable div is visible */
            }
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

                    <!-- <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Active</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                        </li>
                    </ul> -->

                    <br>

                    <?php
                    // Re-run query to calculate total amount paid for the selected month
                    $total_query = mysqli_query($connection, "SELECT SUM(amount_paid) AS total_paid FROM history $month_filter");
                    $total_row = mysqli_fetch_assoc($total_query);
                    $total_paid = $total_row['total_paid'] ?? 0;
                    ?>

                    <!-- Container to hold month select and total amount -->
                    <div class="d-flex justify-content-between align-items-center mb-3"
                        style="gap: 20px; flex-wrap: wrap;">
                        <!-- Month Select Form -->
                        <form method="GET" id="monthForm" class="d-flex align-items-center">
                            <select name="month" id="month" class="form-select" style="width: 250px;">
                                <option value="">-- All Months --</option>
                                <?php
                                $month_sql = "SELECT DISTINCT DATE_FORMAT(payment_date, '%Y-%m') AS month FROM history ORDER BY month DESC";
                                $month_result = mysqli_query($connection, $month_sql);

                                while ($row = mysqli_fetch_assoc($month_result)) {
                                    $month_value = $row['month'];
                                    $selected = (isset($_GET['month']) && $_GET['month'] === $month_value) ? 'selected' : '';
                                    $formatted_month = date('F Y', strtotime($month_value));
                                    echo "<option value='$month_value' $selected>$formatted_month</option>";
                                }
                                ?>
                            </select>
                        </form>


                        <!-- Total Amount Paid Display -->
                        <div class="fw-bold text-end" style="min-width: 250px;">
                            Total Amount Paid: ₱<?php echo number_format($total_paid, 2); ?>
                        </div>

                        <!-- Print Button -->
                        <!-- Print Button -->
                        <div class="d-flex justify-content-end mb-3">
                            <button onclick="printDiv('print-section')" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print Summary
                            </button>
                        </div>


                    </div>



                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Member ID</th>
                                        <th>Reading ID</th>
                                        <th>Fullname</th>
                                        <th>OR Number</th>
                                        <th>Date</th>
                                        <th>Current Charges</th>
                                        <th>Discount</th>
                                        <th>Total Amount Due</th>
                                        <th>Amount Paid</th>
                                        <th>Billing Month</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['member_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['reading_id']); ?></td>

                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['or_number']); ?></td>
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
                                            <td><?php echo htmlspecialchars($row['current_charges']); ?></td>
                                            <td><?php echo htmlspecialchars($row['discount']); ?></td>
                                            <td><?php echo htmlspecialchars($row['total_amount_due']); ?></td>
                                            <td><?php echo htmlspecialchars($row['amount_paid']); ?></td>

                                            <td><?php echo htmlspecialchars($row['billing_month']); ?></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>

                            </table>
                        </div>

                    </div>

                    <!-- Hidden Printable Version -->
                    <div id="print-section" style="display: none;">
                        <h2>Payment Summary</h2>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fullname</th>
                                    <th>OR Number</th>
                                    <th>Payment Date</th>
                                    <th>Current Charges</th>
                                    <th>Total Amount Due</th>
                                    <th>Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Re-run the query to fetch data again
                                $print_query = mysqli_query($connection, "SELECT * FROM history ORDER BY payment_date DESC");
                                while ($row = mysqli_fetch_assoc($print_query)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['or_number']) . "</td>";
                                    echo "<td>" . date('F d, Y', strtotime($row['payment_date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['current_charges']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['total_amount_due']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['amount_paid']) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
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

    <!-- select month filter -->
    <script>
        document.getElementById('month').addEventListener('change', function () {
            document.getElementById('monthForm').submit();
        });
    </script>

    <script>
        function printDiv(divId) {
            // Save original page
            var originalContents = document.body.innerHTML;

            // Get only the div
            var printContents = document.getElementById(divId).innerHTML;

            // Replace body with div content
            document.body.innerHTML = `
        <style>
            @page {
                size: 8.5in 13in; /* Long bond paper */
                margin: 20mm;     /* Adjust margin */
            }
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 10mm;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid black;
                padding: 5px;
                text-align: left;
            }
            h2 {
                text-align: center;
                margin-bottom: 20px;
            }
        </style>
        ${printContents}
    `;

            // Print
            window.print();

            // Restore original page
            document.body.innerHTML = originalContents;

            // Re-run scripts/styles if needed
            location.reload();
        }
    </script>




</body>

</html>