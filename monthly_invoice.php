<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

$selected_month = isset($_GET['month']) ? $_GET['month'] : '';
$month_filter_sql = "";

// Fetch unique months from reading_date
$months_sql = "SELECT DISTINCT DATE_FORMAT(reading_date, '%Y-%m') AS month FROM meter_reading ORDER BY month DESC";
$months_result = mysqli_query($connection, $months_sql);

// Prepare filter if month is selected
if (!empty($selected_month)) {
    $month_filter_sql = "WHERE DATE_FORMAT(mr.reading_date, '%Y-%m') = '$selected_month'";
}

// Modified query: remove hardcoded $today
$sql = "SELECT 
            mr.*, 
            CONCAT(m.last_name, ', ', m.first_name, ' ', m.middle_name) AS fullname,
            m.address 
        FROM meter_reading mr
        JOIN members m ON mr.member_id = m.member_id
        $month_filter_sql
        ORDER BY mr.reading_date DESC";

$result = mysqli_query($connection, $sql);


// Calculate total amount due for selected month
$total_due_sql = "SELECT SUM(mr.total_amount_due) AS total_due
                  FROM meter_reading mr
                  $month_filter_sql";
$total_due_result = mysqli_query($connection, $total_due_sql);
$total_due_row = mysqli_fetch_assoc($total_due_result);
$total_amount_due_sum = $total_due_row['total_due'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Invoices - Current Month</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        .invoice-card {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            background-color: white;
            page-break-inside: avoid;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .printable-area {
                display: flex;
                flex-wrap: wrap;
                flex-direction: row;
            }

            .invoice-card {
                width: 48%;
                margin: 1%;
            }
        }


        /* old design */
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

        /* total amount text */
        .fs-5 {
            font-size: 1.25rem;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <div id="layoutSidenav">
        <?php include "Includes/header_admin.php"; ?>
        <?php include "Includes/sidebar_admin.php"; ?>
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4 mt-4">
                <h2 class="mb-4 d-flex justify-content-between no-print">
                    All Billing Statement
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-end">
                            <label class="fw-bold">Total Amount Due:</label><br>
                            <span class="text-danger fs-5">₱<?= number_format($total_amount_due_sum, 2) ?></span>
                        </div>
                        <button class="btn btn-success ms-2" onclick="printAllInvoices()">Print All</button>
                    </div>
                </h2>


                <!-- select month -->
                <div class="mb-3 no-print">
                    <form method="GET" action="" id="monthForm">
                        <label for="month">Filter by Month:</label>
                        <select name="month" id="month" class="form-select d-inline w-auto">
                            <option value="">All</option>
                            <?php while ($month_row = mysqli_fetch_assoc($months_result)):
                                $month_val = $month_row['month'];
                                $selected = ($month_val == $selected_month) ? 'selected' : '';
                                $formatted_month = date('F Y', strtotime($month_val));
                                ?>
                                <option value="<?= $month_val ?>" <?= $selected ?>><?= $formatted_month ?></option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>



                <div class="row printable-area">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="col-md-6 invoice-card mb-4">
                                <div class="invoice-container p-3 border rounded shadow-sm">

                                    <div class="header-section d-flex justify-content-center align-items-center mb-3">
                                        <img src="img/lg1.jpg" alt="Logo"
                                            style="width: 60px; height: auto; margin-right: 10px;">
                                        <div>
                                            <h6 class="mb-0 text-center">BARANGAY WATER SYSTEM & LIVELIHOOD ASSOCIATION</h6>
                                            <p class="mb-0 text-center">Silway-7, Polomolok, South Cotabato</p>
                                        </div>
                                    </div>

                                    <h5 class="text-center mb-3">Billing Statement</h5>

                                    <p><strong>Name:</strong> <?= htmlspecialchars($row['fullname']) ?></p>
                                    <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>

                                    <h6>Date of Bill Information</h6>
                                    <table class="table table-bordered mb-3">
                                        <tr>
                                            <th style="width: 50%;">Billing Month</th>
                                            <th style="width: 50%;">Reading Date</th>
                                        </tr>
                                        <tr>
                                            <td><?= htmlspecialchars($row['billing_month']) ?></td>
                                            <td><?= htmlspecialchars($row['reading_date']) ?></td>
                                        </tr>
                                    </table>

                                    <h6>Meter Information</h6>
                                    <table class="table table-bordered mb-3">
                                        <tr>
                                            <th style="width: 33%;">Present Reading</th>
                                            <th style="width: 33%;">Previous Reading</th>
                                            <th style="width: 33%;">Usage</th>
                                        </tr>
                                        <tr>
                                            <td><?= htmlspecialchars($row['current_reading']) ?></td>
                                            <td><?= htmlspecialchars($row['previous_reading']) ?></td>
                                            <td><?= htmlspecialchars($row['total_usage']) ?></td>
                                        </tr>
                                    </table>

                                    <h6>Bill Summary</h6>
                                    <table class="table table-bordered mb-3">
                                        <tr>
                                            <td style="width: 50%;">Current Charges</td>
                                            <td style="width: 50%;">₱<?= number_format($row['current_charges'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Arrears</td>
                                            <td>₱<?= number_format($row['arrears_amount'], 2) ?></td>
                                        </tr>
                                        <tr class="total-amount-row">
                                            <td><strong>Total Amount Due</strong></td>
                                            <td><strong>₱<?= number_format($row['total_amount_due'], 2) ?></strong></td>
                                        </tr>
                                    </table>

                                    <div class="d-flex justify-content-between mt-3">
                                        <div class="due-date-box text-center">
                                            <p class="text-success mb-1"><strong>DUE DATE</strong></p>
                                            <p><?= htmlspecialchars($row['due_date']) ?></p>
                                        </div>
                                        <div class="disconnection-date-box text-center">
                                            <p class="text-danger mb-1"><strong>DISCONNECTION DATE</strong></p>
                                            <p><?= htmlspecialchars($row['disconnection_date']) ?></p>
                                        </div>
                                    </div>

                                    <div class="text-center mt-3">
                                        <button class="btn btn-primary btn-sm no-print" data-bs-toggle="modal"
                                            data-bs-target="#calculateBillModal"
                                            onclick="openModal(<?= $row['member_id'] ?>, <?= $row['reading_id'] ?>)">
                                            View Invoice
                                        </button>

                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No invoices found for the current month.</p>
                    <?php endif; ?>
                </div>

            </main>

            <!-- Invoice Modal -->
            <div class="modal fade" id="calculateBillModal" tabindex="-1" aria-labelledby="calculateBillModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg-custom">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">

                                <div class="invoice-container">
                                    <div class="header-section d-flex justify-content-center align-items-center mb-4">
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
                                            <td style="width: 50%;"><strong><span id="total_amount_due"></span></strong>
                                            </td>
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
                                <button type="button" class="btn btn-primary" onclick="printInvoice()">Print</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(memberId, readingId) {
            fetch(`fetch_invoice.php?member_id=${memberId}&reading_id=${readingId}`)
                .then(response => response.json())
                .then(data => {
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
                    console.error('Error:', error);
                });
        }

        function printInvoice() {
            const printContents = document.querySelector('.invoice-container').innerHTML;
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload();
        }

        function printAllInvoices() {
            window.print();
        }
    </script>

    <!-- select month filter-->
    <script>
        document.getElementById('month').addEventListener('change', function () {
            document.getElementById('monthForm').submit();
        });
    </script>


</body>

</html>