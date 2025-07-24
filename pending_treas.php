<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    return ($row['count'] == 0);
}

$select = mysqli_query($connection, "
    SELECT 
        meter_reading.reading_date, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname,
        members.tank_no, 
        members.meter_no,    
        members.address, 
        meter_reading.reading_id,
        meter_reading.member_id,
        meter_reading.due_date,
        meter_reading.disconnection_date,
        meter_reading.status,
        meter_reading.billing_month,
        pending.pending_id,
        pending.date_received,
        pending.proof_image_path,
        pending.current_charges,
        pending.arrears_amount,
        pending.total_amount_due,
        pending.billing_month,
        members.isDone
    FROM 
        meter_reading
    JOIN 
        members ON meter_reading.member_id = members.member_id
    LEFT JOIN 
        pending ON meter_reading.reading_id = pending.reading_id
    WHERE 
        meter_reading.status = 'Pending' 
");


// add

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Reject payment logic
    if (isset($_POST['reject_payment'])) {
        $reading_id = $_POST['reading_id'] ?? null;

        if ($reading_id) {
            // Update the status in meter_reading table
            $stmt = $connection->prepare("UPDATE meter_reading SET status = 'Not Paid' WHERE reading_id = ?");
            $stmt->bind_param("s", $reading_id);

            if ($stmt->execute()) {
                // Delete the corresponding record from pending table
                $delete_stmt = $connection->prepare("DELETE FROM pending WHERE reading_id = ?");
                $delete_stmt->bind_param("s", $reading_id);

                if ($delete_stmt->execute()) {
                    // Redirect after success
                    header("Location: pending_treas.php?reading_id=$reading_id&status=rejected");
                    exit();
                } else {
                    error_log("Error deleting pending record: " . $delete_stmt->error);
                    echo "Error deleting pending record.";
                }
                $delete_stmt->close();
            } else {
                error_log("Error updating meter reading status: " . $stmt->error);
                echo "Error updating meter reading status.";
            }
            $stmt->close();
        } else {
            echo "No reading ID provided. Status not updated.";
        }
    }
    // Accept payment logic
    else if (isset($_POST['submit'])) {
        // Collect form data
        $transaction_id = $_POST['transaction_id'];
        $member_id = $_POST['member_id'];
        $reading_id = $_POST['reading_id'];
        $fullname = $_POST['fullname'];
        $reading_date = $_POST['reading_date'];
        $due_date = $_POST['due_date'];
        $disconnection_date = $_POST['disconnection_date'];
        $current_charges = $_POST['current_charges'];
        $arrears_amount = $_POST['arrears_amount'];
        $total_amount_due = $_POST['total_amount_due'];
        $amount_paid = $_POST['amount_paid'];
        $billing_month = $_POST['billing_month'];
        $payment_method = $_POST['payment_method'];
        // Insert into 'history' table
        $sql_history = "INSERT INTO history (transaction_id, member_id, reading_id, fullname, reading_date, due_date, disconnection_date, current_charges, total_amount_due, amount_paid, billing_month, payment_method) 
    VALUES ('$transaction_id', '$member_id', '$reading_id', '$fullname', '$reading_date', '$due_date', '$disconnection_date',  '$current_charges', '$total_amount_due', '$amount_paid', '$billing_month', '$payment_method')";

        if ($connection->query($sql_history)) {

            // Always delete any existing arrears record for this member when the payment button is clicked
            $delete_arrears = "DELETE FROM arrears WHERE member_id = '$member_id'";
            if ($connection->query($delete_arrears)) {
                echo "Arrears deleted for member_id: $member_id";
            } else {
                echo "Error deleting arrears: " . $connection->error;
            }

            // Insert/update arrears if still due (optional: if needed to re-add it)
            if ($arrears_amount > 0) {
                $sql_arrears = "INSERT INTO arrears (transaction_id, member_id, arrears_amount) 
                        VALUES ('$transaction_id', '$member_id', '$arrears_amount')
                        ON DUPLICATE KEY UPDATE arrears_amount = '$arrears_amount'";

                if (!$connection->query($sql_arrears)) {
                    echo "Error inserting/updating arrears: " . $connection->error;
                }
            }


            // Update meter_reading status to "Paid"
            $sql_update_status = "UPDATE meter_reading SET status = 'Paid' WHERE reading_id = '$reading_id'";
            if ($connection->query($sql_update_status)) {

                // Update members table isDone field to "Not Done"
                $sql_update_isDone = "UPDATE members SET isDone = 'Not Done' WHERE member_id = '$member_id'";
                if ($connection->query($sql_update_isDone)) {
                    header("Location: receipt.php?transaction_id=$transaction_id");
                    exit();
                } else {
                    echo "Error updating isDone: " . $connection->error;
                }

            } else {
                echo "Error updating meter reading status: " . $connection->error;
            }

        } else {
            echo "Error: " . $connection->error;
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payment</title>
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

        td:nth-child(2),
        th:nth-child(2) {
            display: none;
        }

        td:nth-child(3),
        th:nth-child(3) {
            display: none;
        }

        td:nth-child(6),
        th:nth-child(6) {
            width: 100px;
        }

        td:nth-child(10),
        th:nth-child(10) {
            display: none;
        }

        td:nth-child(11),
        th:nth-child(11) {
            display: none;
        }

        td:nth-child(12),
        th:nth-child(12) {
            display: none;
        }

        .modal-lg-custom {
            max-width: 80%;
            /* Adjust as needed */
        }

        #proof_image {
            width: 60%;
            /* Ensures it fits within its column */
            height: 420px;
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
                                        <th>Arrears</th>
                                        <th>Total Amount Due</th>
                                        <th>Billing Month</th>
                                        <th>Status</th>
                                        <th>isDone</th>
                                        <th>Proof of Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($select) > 0) {
                                        while ($row = mysqli_fetch_assoc($select)) {

                                            // Output the row data
                                            echo "<tr>
                                                    <td>" . htmlspecialchars($row['pending_id']) . "</td>
                                                    <td>" . htmlspecialchars($row['member_id']) . "</td>
                                                    <td>" . htmlspecialchars($row['reading_id']) . "</td>
                                                    <td>" . htmlspecialchars($row['fullname']) . "</td>
                                                    <td>" . htmlspecialchars($row['date_received']) . "</td>
                                                    <td>" . htmlspecialchars($row['current_charges']) . "</td>
                                                    <td>" . htmlspecialchars($row['arrears_amount']) . "</td>
                                                    <td>" . htmlspecialchars($row['total_amount_due']) . "</td>
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
                                                            '" . htmlspecialchars($row['address']) . "',
                                                            '" . htmlspecialchars($row['tank_no']) . "',
                                                            '" . htmlspecialchars($row['meter_no']) . "',
                                                            '" . htmlspecialchars($row['reading_date']) . "',
                                                            '" . htmlspecialchars($row['due_date']) . "',
                                                            '" . htmlspecialchars($row['disconnection_date']) . "',
                                                            '" . htmlspecialchars($row['current_charges']) . "',
                                                            '" . htmlspecialchars($row['arrears_amount']) . "',
                                                            '" . htmlspecialchars($row['total_amount_due']) . "',
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
                    <div class="modal-dialog modal-lg-custom">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post">
                                    <div class="row">
                                        <!-- left -->
                                        <div class="col-md-8">
                                            <!-- info hidden -->
                                            <input type="hidden" id="transaction_id" name="transaction_id" value="<?php
                                            echo rand(100000000, 999999999);
                                            ?>" required autocomplete="off">
                                            <input type="hidden" id="pending_id" name="pending_id" required
                                                autocomplete="off">
                                            <input type="hidden" class="form-control" id="edit_member_id"
                                                name="member_id" readonly>
                                            <input type="hidden" class="form-control" id="edit_reading_id"
                                                name="reading_id" readonly>
                                            <div class="mb-3">
                                                <label for="fullname" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="edit_fullname"
                                                    name="fullname" readonly>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="tank_no" class="form-label">Tank Number</label>
                                                    <input type="text" class="form-control" id="edit_tank_no"
                                                        name="tank_no" readonly>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="meter_no" class="form-label">Meter Number</label>
                                                    <input type="text" class="form-control" id="edit_meter_no"
                                                        name="meter_no" readonly>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="address" class="form-label">Address</label>
                                                    <input type="text" class="form-control" id="edit_address"
                                                        name="address" readonly>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="reading_date" class="form-label">Reading Date</label>
                                                    <input type="text" class="form-control" id="edit_reading_date"
                                                        name="reading_date" readonly>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="due_date" class="form-label">Due Date</label>
                                                    <input type="text" class="form-control" id="edit_due_date"
                                                        name="due_date" readonly>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="disconnection_date" class="form-label">Disconnection
                                                        Date</label>
                                                    <input type="text" class="form-control" id="edit_disconnection_date"
                                                        name="disconnection_date" readonly>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="amount_paid" class="form-label">Cash</label>
                                                    <input type="number" style="border: 2px solid #000;"
                                                        class="form-control" id="amount_paid" name="amount_paid"
                                                        placeholder="Input Cash" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="total_amount_due" class="form-label">Total Amount
                                                        Due</label>
                                                    <input type="text" class="form-control" id="edit_total_amount_due"
                                                        name="total_amount_due" readonly>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <input type="hidden" class="form-control" id="edit_billing_month"
                                                    name="billing_month" readonly>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="change" class="form-label">Change</label>
                                                    <input type="number" class="form-control" id="change" name="change"
                                                        value="0.00" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="arrears_amount" class="form-label">Arrears</label>
                                                    <input type="number" class="form-control" id="arrears_amount"
                                                        name="arrears_amount" value="0.00" readonly>
                                                </div>
                                            </div>
                                            <input type="hidden" class="form-control" id="edit_current_charges"
                                                name="current_charges" readonly>
                                            <input type="hidden" name="payment_method" value="G-Cash">
                                        </div>
                                        <!-- Right side: Proof of payment image -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="proof_image" class="form-label">Proof of
                                                    Payment</label>
                                                <img id="proof_image" src="" alt="Proof Image" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" id="clearButton">Clear</button>
                                        <button type="submit" class="btn btn-danger" name="reject_payment"
                                            onclick="removeRequired()">Reject Payment</button>
                                        <button type="submit" name="submit" class="btn btn-primary">Pay</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include "Includes/footer.php"; ?>
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
        function showPaymentModal(pendingId, memberId, readingId, fullname, address, tankNo, meterNo, readingDate, dueDate, disconnectionDate, currentCharges, arrearsAmount, totalAmountDue, billingMonth, proofImagePath) {
            // Set values for the modal
            document.getElementById('pending_id').value = pendingId;
            document.getElementById('edit_fullname').value = fullname;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_member_id').value = memberId;
            document.getElementById('edit_reading_id').value = readingId;
            document.getElementById('edit_tank_no').value = tankNo;
            document.getElementById('edit_meter_no').value = meterNo;
            document.getElementById('edit_reading_date').value = readingDate;
            document.getElementById('edit_due_date').value = dueDate;
            document.getElementById('edit_disconnection_date').value = disconnectionDate;
            document.getElementById('edit_current_charges').value = currentCharges;
            document.getElementById('edit_total_amount_due').value = totalAmountDue;
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
            var totalAmountDue = parseFloat(document.getElementById('edit_total_amount_due').value); // Total due
            var amountPaid = parseFloat(this.value);
            var changeField = document.getElementById('change');
            var arrearsField = document.getElementById('arrears_amount');

            if (isNaN(totalAmountDue) || isNaN(amountPaid)) {
                // Default change and arrears to 0.00 if inputs are invalid
                changeField.value = '0.00';
                arrearsField.value = '0.00';
                return;
            }

            if (amountPaid >= totalAmountDue) {
                changeField.value = (amountPaid - totalAmountDue).toFixed(2); // Change to be given back
                arrearsField.value = '0.00'; // No arrears if paid fully or overpaid
            } else {
                changeField.value = '0.00'; // No change if underpaid
                arrearsField.value = (totalAmountDue - amountPaid).toFixed(2); // Amount still owed
            }
        });

        // Clear button functionality to reset the form
        document.getElementById('clearButton').addEventListener('click', function () {
            document.getElementById('amount_paid').value = ''; // Clear payment input
            document.getElementById('change').value = '0.00'; // Reset change field to 0.00
            document.getElementById('arrears_amount').value = '0.00'; // Reset arrears field to 0.00
        });
    </script>

    <!-- reject_payment -->

    <script>
        function removeRequired() {
            // Select all input fields inside the form
            const inputs = document.querySelectorAll('form input[required]');

            // Remove the 'required' attribute
            inputs.forEach(input => input.removeAttribute('required'));
        }

    </script>

</body>

</html>