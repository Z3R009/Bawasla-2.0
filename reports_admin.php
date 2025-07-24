<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

// Retrieve the selected month from the URL parameters, if any
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : null;

// Base query
$query = "
    SELECT 
        meter_reading.reading_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS full_name, 
        members.meter_no, 
        members.tank_no, 
        members.address,
        meter_reading.total_usage, 
        meter_reading.current_charges,
        meter_reading.reading_date
    FROM 
        meter_reading
    JOIN 
        members ON meter_reading.member_id = members.member_id
";

// Add filtering condition if a specific month is selected
if ($selectedMonth !== null && $selectedMonth !== '') {
    $query .= " WHERE MONTH(meter_reading.reading_date) = $selectedMonth";
}

// Always order by latest date
$query .= " ORDER BY meter_reading.reading_date DESC";


// Execute the query
$select = mysqli_query($connection, $query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Reports</title>
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

        td:nth-child(1),
        th:nth-child(1) {
            display: none;
        }

        td:nth-child(6),
        th:nth-child(6) {
            width: 100px;
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
                        Billing Reports
                        <div class="ms-3">
                            <form id="monthForm" method="GET" action="">
                                <select class="form-select" name="month" aria-label="Select month"
                                    onchange="document.getElementById('monthForm').submit();">
                                    <option value="" <?php if (!$selectedMonth)
                                        echo 'selected'; ?>>Select Month
                                    </option>
                                    <option value="1" <?php if ($selectedMonth == 1)
                                        echo 'selected'; ?>>January</option>
                                    <option value="2" <?php if ($selectedMonth == 2)
                                        echo 'selected'; ?>>February</option>
                                    <option value="3" <?php if ($selectedMonth == 3)
                                        echo 'selected'; ?>>March</option>
                                    <option value="4" <?php if ($selectedMonth == 4)
                                        echo 'selected'; ?>>April</option>
                                    <option value="5" <?php if ($selectedMonth == 5)
                                        echo 'selected'; ?>>May</option>
                                    <option value="6" <?php if ($selectedMonth == 6)
                                        echo 'selected'; ?>>June</option>
                                    <option value="7" <?php if ($selectedMonth == 7)
                                        echo 'selected'; ?>>July</option>
                                    <option value="8" <?php if ($selectedMonth == 8)
                                        echo 'selected'; ?>>August</option>
                                    <option value="9" <?php if ($selectedMonth == 9)
                                        echo 'selected'; ?>>September
                                    </option>
                                    <option value="10" <?php if ($selectedMonth == 10)
                                        echo 'selected'; ?>>October
                                    </option>
                                    <option value="11" <?php if ($selectedMonth == 11)
                                        echo 'selected'; ?>>November
                                    </option>
                                    <option value="12" <?php if ($selectedMonth == 12)
                                        echo 'selected'; ?>>December
                                    </option>
                                </select>
                            </form>

                        </div>
                    </h1>

                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Reading ID</th>
                                        <th>Reading Date</th>
                                        <th>Full Name</th>
                                        <th>Meter No.</th>
                                        <th>Tank No.</th>
                                        <th>Address</th>
                                        <th>Usage</th>
                                        <th>Total Charges</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['reading_id']); ?></td>
                                            <td>
                                                <?php
                                                echo date('F d, Y', strtotime($row['reading_date']));
                                                ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['meter_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tank_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td><?php echo htmlspecialchars($row['total_usage']); ?></td>
                                            <td><?php echo htmlspecialchars($row['current_charges']); ?></td>
                                        </tr>
                                    <?php } ?>
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
    <script src="start/assets/demo/chart-area-demo.js"></script>
    <script src="start/assets/demo/chart-bar-demo.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>

    <!-- function -->

    <!-- select month -->
    <script>
        document.querySelector('.form-select').addEventListener('change', function () {
            var selectedMonth = this.value;
            // You can handle the selected month here, like submitting a form or making an AJAX request
            console.log('Selected Month:', selectedMonth);
            // Redirect or perform actions based on the selected month
            // Example: window.location.href = 'your-url.php?month=' + selectedMonth;
        });
    </script>
</body>

</html>