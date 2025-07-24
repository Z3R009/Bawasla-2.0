<?php
include 'DBConnection.php';

function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    return ($row['count'] == 0);
}

//retrieve
$select = mysqli_query($connection, "
        SELECT 
            pending.pending_id,
            pending.date_received,
            pending.proof_image_path,
            pending.current_charges AS pending_charges,
            pending.billing_month AS pending_billing_month,
            meter_reading.reading_date, 
            meter_reading.current_charges,
            meter_reading.due_date,
            meter_reading.disconnection_date,
            meter_reading.billing_month,
            meter_reading.reading_id,
            meter_reading.status,
            members.member_id,
            CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname,
            members.tank_no, 
            members.meter_no,    
            members.address,
            members.isDone
        FROM 
            pending
        JOIN 
            meter_reading ON pending.reading_id = meter_reading.reading_id
        JOIN 
            members ON meter_reading.member_id = members.member_id
        WHERE status = 'Pending'
    ");

// add

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming you've already established a database connection as $connection

    // Get user data from the form
    $transaction_id = $_POST['transaction_id'];
    $member_id = $_POST['member_id'];
    $reading_id = $_POST['reading_id'];
    $fullname = $_POST['fullname'];
    $reading_date = $_POST['reading_date'];
    $due_date = $_POST['due_date'];
    $disconnection_date = $_POST['disconnection_date'];
    $current_charges = $_POST['current_charges'];
    $amount_paid = $_POST['amount_paid'];
    $billing_month = $_POST['billing_month'];
    $payment_method = $_POST['payment_method'];

    // Insert into 'history' table
    $sql_history = "INSERT INTO history (transaction_id, member_id, reading_id, fullname, reading_date, due_date, disconnection_date, current_charges, amount_paid, billing_month, payment_method) 
                    VALUES ('$transaction_id', '$member_id', '$reading_id', '$fullname', '$reading_date', '$due_date', '$disconnection_date',  '$current_charges', '$amount_paid', '$billing_month', '$payment_method')";

    // Execute transaction and history queries
    if ($connection->query($sql_history)) {

        // Only insert into 'arrears' table if arrears_amount is not 0.00
        if ($arrears_amount != '0.00') {
            $sql_arrears = "INSERT INTO arrears (transaction_id, arrears_amount) 
                                VALUES ('$transaction_id', '$arrears_amount')";

            // Execute arrears query
            if (!$connection->query($sql_arrears)) {
                // Handle error for arrears insertion
                echo "Error: " . $connection->error;
            }
        }

        // Update the status to "Paid" in the meter_reading table
        $sql_update_status = "UPDATE meter_reading SET status = 'Paid' WHERE reading_id = '$reading_id'";
        if ($connection->query($sql_update_status)) {

            // Update isDone to "Done" in the members table
            $sql_update_isDone = "UPDATE members SET isDone = 'Not Done' WHERE member_id = '$member_id'";
            if ($connection->query($sql_update_isDone)) {
                // Redirect to manage members page if everything is successful
                header('Location: pending_admin.php');
                exit();
            } else {
                echo "Error updating isDone: " . $connection->error;
            }

        } else {
            echo "Error updating status: " . $connection->error;
        }

    } else {
        echo "Error: " . $connection->error;
    }
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

        td:nth-child(1),
        th:nth-child(1) {
            display: none;
        }

        td:nth-child(6),
        th:nth-child(6) {
            width: 100px;
        }

        td:nth-child(8),
        th:nth-child(8) {
            display: none;
        }

        td:nth-child(9),
        th:nth-child(9) {
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
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading"></div>
                        <a class="nav-link" href="dashboard_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="manage_users.php">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                            Manage Users
                        </a>
                        <a class="nav-link" href="manage_members.php">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                            Manage Members
                        </a>
                        <a class="nav-link" href="transaction_admin.php">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-transfer"></i></div>
                            Manage Transaction
                        </a>
                        <a class="nav-link" href="manage_invoice.php">
                            <div class="sb-nav-link-icon"><i class="fa-regular fa-file"></i></div>
                            Manage Invoice
                        </a>
                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-clock"></i></div>
                            Pending Payment
                        </a>
                        <a class="nav-link" href="reports_admin.php">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                            Reports
                        </a>
                        <!-- <a class="nav-link" href="index.html">
                                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                        Activity Logs
                                    </a> -->
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    President
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 d-flex justify-content-between align-items-center">
                        Pending Payment
                    </h1>

                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>id</th>
                                        <th>Member Id</th>
                                        <th>Reading ID</th>
                                        <th>Full Name</th>
                                        <th>Date Received</th>
                                        <th>Current Charges</th>
                                        <th>Billing Month</th>
                                        <th>Status</th>
                                        <th>isDone</th>
                                        <th>Proof of Payment</th>
                                        <th>View Image</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($select) > 0) {
                                        while ($row = mysqli_fetch_assoc($select)) {
                                            echo "<tr>
                                                    <td>" . htmlspecialchars($row['pending_id']) . "</td>
                                                    <td>" . htmlspecialchars($row['member_id']) . "</td>
                                                    <td>" . htmlspecialchars($row['reading_id']) . "</td>
                                                    <td>" . htmlspecialchars($row['fullname']) . "</td>
                                                    <td>" . htmlspecialchars($row['date_received']) . "</td>
                                                    <td>" . htmlspecialchars($row['current_charges']) . "</td>
                                                    <td>" . htmlspecialchars($row['billing_month']) . "</td>
                                                    <td>" . htmlspecialchars($row['status']) . "</td>
                                                    <td>" . htmlspecialchars($row['isDone']) . "</td>
                                                    <td>";
                                            if (!empty($row['proof_image_path'])) {
                                                echo "<img src='" . htmlspecialchars($row['proof_image_path']) . "' alt='Payment Proof' style='width: 100px; height: auto; cursor: pointer;' onclick=\"showImageModal('" . htmlspecialchars($row['proof_image_path']) . "')\">";
                                            } else {
                                                echo "No proof uploaded";
                                            }
                                            echo "</td>
                                                    <td>
                                                        <button class='btn btn-primary' onclick=\"showPaymentModal(
                                                            '" . htmlspecialchars($row['pending_id']) . "',
                                                            '" . htmlspecialchars($row['member_id']) . "',
                                                            '" . htmlspecialchars($row['reading_id']) . "',
                                                            '" . htmlspecialchars($row['fullname']) . "',
                                                            '" . htmlspecialchars($row['tank_no']) . "',
                                                            '" . htmlspecialchars($row['meter_no']) . "',
                                                            '" . htmlspecialchars($row['reading_date']) . "',
                                                            '" . htmlspecialchars($row['due_date']) . "',
                                                            '" . htmlspecialchars($row['disconnection_date']) . "',
                                                            '" . htmlspecialchars($row['current_charges']) . "',
                                                            '" . htmlspecialchars($row['billing_month']) . "',
                                                            '" . htmlspecialchars($row['proof_image_path']) . "',
                                                        )\">Process Payment</button>
                                                    </td>
                                                </tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Modal Structure -->
                <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <input type="hidden" id="transaction_id" name="transaction_id" value="<?php
                                        echo rand(100000000, 999999999);
                                        ?>" required autocomplete="off">
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" id="pending_id" name="pending_id" required
                                            autocomplete="off">
                                    </div>
                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="edit_fullname" name="fullname"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" id="edit_member_id" name="member_id"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" id="edit_reading_id" name="reading_id"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tank_no" class="form-label">Tank Number</label>
                                        <input type="text" class="form-control" id="edit_tank_no" name="tank_no"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meter_no" class="form-label">Meter Number</label>
                                        <input type="text" class="form-control" id="edit_meter_no" name="meter_no"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reading_date" class="form-label">Reading Date</label>
                                        <input type="text" class="form-control" id="edit_reading_date"
                                            name="reading_date" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">Due Date</label>
                                        <input type="text" class="form-control" id="edit_due_date" name="due_date"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="disconnection_date" class="form-label">Disconnection
                                            Date</label>
                                        <input type="text" class="form-control" id="edit_disconnection_date"
                                            name="disconnection_date" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="current_charges" class="form-label">Current Charges</label>
                                        <input type="text" class="form-control" id="edit_current_charges"
                                            name="current_charges" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" id="edit_billing_month"
                                            name="billing_month" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="amount_paid" class="form-label">Cash</label>
                                        <input type="number" class="form-control" id="amount_paid" name="amount_paid"
                                            placeholder="Input Cash" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="change" class="form-label">Change</label>
                                        <input type="number" class="form-control" id="change" name="change" value="0.00"
                                            readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arrears_amount" class="form-label">Arrears</label>
                                        <input type="number" class="form-control" id="arrears_amount"
                                            name="arrears_amount" value="0.00" readonly>
                                    </div>
                                    <input type="hidden" name="payment_method" value="G-Cash">
                                    <!-- Image proof section -->
                                    <div class="mb-3">
                                        <label for="proof_image" class="form-label">Proof of Payment</label>
                                        <img id="proof_image" src="" alt="Proof Image"
                                            style="width: 100%; height: auto; display: none;">
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="clearButton">Clear</button>
                                <button type="submit" name="submit" class="btn btn-primary">Pay</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
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
            </footer>
        </div>


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
    <script>
        function showPaymentModal(pendingId, memberId, readingId, fullname, tankNo, meterNo, readingDate, dueDate, disconnectionDate, currentCharges, billingMonth, proofImagePath) {
            // Set values for the modal
            document.getElementById('pending_id').value = pendingId;
            document.getElementById('edit_fullname').value = fullname;
            document.getElementById('edit_member_id').value = memberId;
            document.getElementById('edit_reading_id').value = readingId;
            document.getElementById('edit_tank_no').value = tankNo;
            document.getElementById('edit_meter_no').value = meterNo;
            document.getElementById('edit_reading_date').value = readingDate;
            document.getElementById('edit_due_date').value = dueDate;
            document.getElementById('edit_disconnection_date').value = disconnectionDate;
            document.getElementById('edit_current_charges').value = currentCharges;
            document.getElementById('edit_billing_month').value = billingMonth;

            // Set the proof image
            const proofImage = document.getElementById('proof_image');
            proofImage.src = proofImagePath; // Set the source of the image
            proofImage.style.display = proofImagePath ? 'block' : 'none'; // Show or hide based on the path

            // Show the modal
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();
        }
    </script>

    <!-- calculate pay -->
    <script>
        // Function to calculate change and arrears
        document.getElementById('amount_paid').addEventListener('input', function () {
            var totalBill = parseFloat(document.getElementById('edit_current_charges').value);
            var amountPaid = parseFloat(this.value);
            var changeField = document.getElementById('change');
            var arrearsField = document.getElementById('arrears_amount');

            if (isNaN(totalBill) || isNaN(amountPaid)) {
                // If the cash field is empty, default change and arrears to 0.00
                changeField.value = '0.00';
                arrearsField.value = '0.00';
                return;
            }

            if (amountPaid >= totalBill) {
                changeField.value = (amountPaid - totalBill).toFixed(2); // Change to be given back
                arrearsField.value = '0.00'; // No arrears if paid fully or overpaid
            } else {
                changeField.value = '0.00'; // No change if underpaid
                arrearsField.value = (totalBill - amountPaid).toFixed(2); // Amount still owed
            }
        });

        // Clear button functionality to reset the form
        document.getElementById('clearButton').addEventListener('click', function () {
            document.getElementById('amount_paid').value = ''; // Keep cash field unchanged
            document.getElementById('change').value = '0.00'; // Reset change field to 0.00
            document.getElementById('arrears_amount').value = '0.00'; // Reset arrears field to 0.00
        });
    </script>

</body>

</html>