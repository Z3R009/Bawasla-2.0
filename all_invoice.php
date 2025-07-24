<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

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

        .modal {
            z-index: 1055;
            /* Ensure it's higher than other elements */
        }

        .modal-backdrop {
            z-index: 1050;
            /* Backdrop should be just below the modal */
        }

        .modal-lg-custom {
            max-width: 600px;
        }

        .bill-summary-table {
            width: 100%;
            margin-top: 10px;
        }

        .bill-summary-table td {
            padding: 8px;
        }

        .total-amount-row {
            background-color: #ffd700;
            font-weight: bold;
        }

        .due-date-box,
        .disconnection-date-box {
            border: 2px solid;
            padding: 10px;
            width: 45%;
            border-radius: 5px;
        }

        .due-date-box {
            border-color: green;
        }

        .disconnection-date-box {
            border-color: red;
        }

        .meter-info-table th,
        .meter-info-table td {
            width: 33.33%;
            /* Divides each column equally */
            text-align: center;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <div id="layoutSidenav">
        <?php include "Includes/header_treasurer.php"; ?>
        <?php include "Includes/sidebar_treasurer.php"; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 d-flex justify-content-between align-items-center">
                        All Invoces for <?php echo $fullname; ?>
                    </h1>


                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th scope="col">Member ID</th>
                                        <th scope="col">Reading ID</th>
                                        <th scope="col">Reading Date</th>
                                        <th scope="col">Total Bill</th>
                                        <th scope="col">Billing Month</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($_GET['member_id'])) {
                                        $member_id = $_GET['member_id'];
                                        $member_id = mysqli_real_escape_string($connection, $member_id);

                                        // Fetch member information and required fields
                                        $sql = "SELECT members.member_id, 
                                            CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname, 
                                            meter_reading.current_charges,
                                            meter_reading.arrears_amount,
                                            meter_reading.total_amount_due,
                                            meter_reading.reading_id,
                                            meter_reading.reading_date,
                                            meter_reading.billing_month, 
                                            meter_reading.total_usage
                                         FROM meter_reading
                                         JOIN members ON meter_reading.member_id = members.member_id
                                         WHERE members.member_id = '$member_id' ORDER BY reading_date DESC ";

                                        // Execute the query
                                        $result = mysqli_query($connection, $sql);

                                        if ($result) {
                                            // Loop through the result set
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $member_id = $row['member_id'];
                                                $reading_id = $row['reading_id'];
                                                $reading_date = $row['reading_date'];
                                                $total_amount_due = $row['total_amount_due'];
                                                $billing_month = $row['billing_month'];

                                                // Calculate the billing period
                                                // $billing_month = date("F Y", strtotime($reading_date));
                                                // $month_start = date("F 1", strtotime($reading_date));
                                                // $month_end = date("t, Y", strtotime($reading_date));
                                                // $billing_period = $month_start . '-' . $month_end;
                                    
                                                // Output the table row
                                                echo '<tr> 
                                                    <td>' . $member_id . '</td>
                                                    <td>' . $reading_id . '</td>
                                                    <td>' . $reading_date . '</td>
                                                    <td>' . $total_amount_due . '</td>
                                                    <td>' . $billing_month . '</td>
                                                    <td>
                                                        <button class="btn btn-primary" 
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#calculateBillModal"
                                                            onclick="openModal(' . $member_id . ', ' . $reading_id . ')">
                                                            View Invoice
                                                        </button>
                                                    </td>
                                                </tr>';
                                            }
                                        } else {
                                            echo "Error in SQL query: " . mysqli_error($connection);  // Handle SQL query error
                                        }
                                    } else {
                                        echo "No member ID provided.";  // Handle the case where member_id is not set
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal for Viewing Invoice -->
                    <div class="modal fade" id="calculateBillModal" tabindex="-1"
                        aria-labelledby="calculateBillModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg-custom">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-body">

                                        <div class="invoice-container">
                                            <div
                                                class="header-section d-flex justify-content-center align-items-center mb-4">
                                                <img src="img/lg1.jpg" alt="Logo"
                                                    style="width: 80px; height: auto; margin-right: 15px;">
                                                <div>
                                                    <h6 class="mb-0 text-center">BARANGAY WATER SYSTEM & LIVELIHOOD
                                                        ASSOCIATION</h6>
                                                    <p class="mb-0 text-center">Silway-7, Polomolok, South Cotabato</p>
                                                </div>
                                            </div>


                                            <h3 style="text-align: center;">Billing Statement</h3>


                                            <p><strong>Name:</strong> <span id="customer_name"></span></p>
                                            <p><strong>Address:</strong> <span id="customer_address"></span></p>
                                            <!-- <p><strong>Billing Period:</strong> <span id="billing_month"></span></p> -->
                                            <h6>Date of Bill Information</h6>
                                            <table class="table table-bordered">


                                                <tr>
                                                    <th style="width: 50%;">Billing Month</th>
                                                    <th style="width: 50%;">Reading Date</th>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p id="billing_month"></p>
                                                    </td>
                                                    <td>
                                                        <span id="reading_date"></span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <h6>Meter Information</h6>

                                            <table class="table table-bordered">
                                                <tr>
                                                    <th style="width: 33%;">Present Reading</th>
                                                    <th style="width: 33%;">Previous Reading</th>
                                                    <th style="width: 33%;">Usage</th>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <span id="present_reading"></span>
                                                    </td>
                                                    <td><span id="previous_reading"></span></td>
                                                    <td><span id="total_usage"></span></td>
                                                </tr>
                                            </table>

                                            <h6>Bill Summary</h6>
                                            <table class="table table-bordered bill-summary-table">
                                                <tr>
                                                    <td style="width: 50%;">Current Charges</td>
                                                    <td style="width: 50%;"><span id="current_charges"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 50%;">Arrears</td>
                                                    <td style="width: 50%;"><span id="arrears_amount"></span>
                                                    </td>
                                                </tr>
                                                <tr class="total-amount-row">
                                                    <td style="width: 50%;"><strong>Total Amount Due</strong></td>
                                                    <td style="width: 50%;"><strong><span
                                                                id="total_amount_due"></span></strong></td>
                                                </tr>
                                            </table>

                                            <div class="d-flex justify-content-between mt-3">
                                                <div class="due-date-box">
                                                    <p class="text-center text-success"><strong>DUE DATE</strong></p>
                                                    <p class="text-center" id="due_date"></p>
                                                </div>
                                                <div class="disconnection-date-box">
                                                    <p class="text-center text-danger"><strong>DISCONNECTION
                                                            DATE</strong></p>
                                                    <p class="text-center" id="disconnection_date"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary"
                                            onclick="printInvoice()">Print</button>
                                    </div>
                                </div>
                            </div>
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
    <script src="start/assets/demo/chart-area-demo.js"></script>
    <script src="start/assets/demo/chart-bar-demo.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>

    <!-- function -->

    <!-- clear -->
    <script>
        // Function to clear form
        function clearForm() {
            document.getElementById('addUserForm').reset();
        }
    </script>


    <!-- invoice -->
    <script>
        function openModal(memberId, readingId) {
            console.log("Opening modal for member ID:", memberId, "Reading ID:", readingId);
            // Fetch the billing data from the server using AJAX
            fetch(`fetch_invoice.php?member_id=${memberId}&reading_id=${readingId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate modal fields with the fetched data
                    document.getElementById('customer_name').innerText = data.fullname;
                    document.getElementById('customer_address').innerText = data.address;
                    document.getElementById('billing_month').innerText = data.billing_month;
                    document.getElementById('reading_date').innerText = data.reading_date;
                    document.getElementById('due_date').innerText = data.due_date;
                    document.getElementById('disconnection_date').innerText = data.disconnection_date;
                    document.getElementById('previous_reading').innerText = data.previous_reading;
                    document.getElementById('present_reading').innerText = data.current_reading;
                    document.getElementById('total_usage').innerText = data.total_usage;
                    document.getElementById('current_charges').innerText = data.current_charges;
                    document.getElementById('arrears_amount').innerText = data.arrears_amount;
                    document.getElementById('total_amount_due').innerText = data.total_amount_due;
                })
                .catch(error => {
                    console.error('Error fetching invoice data:', error);
                });
        }
    </script>


    <script>
        function printInvoice() {
            var printContents = document.querySelector('.invoice-container').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            // After printing, restore the original page content
            document.body.innerHTML = originalContents;
            window.location.reload();  // Reload to ensure the modal closes properly after printing
        }

    </script>


</body>

</html>