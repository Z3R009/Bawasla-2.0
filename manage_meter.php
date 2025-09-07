<?php
include 'DBConnection.php';

// Ensure the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Function to check and update overdue payments as arrears
function updateOverdueToArrears($connection)
{
    $current_date = date('Y-m-d');

    // Find all meter readings where due_date has passed and status is 'Not Paid'
    $overdue_query = "
        SELECT 
            mr.reading_id,
            mr.member_id, 
            mr.total_amount_due, 
            mr.due_date,
            mr.billing_month
        FROM meter_reading mr
        WHERE mr.due_date < '$current_date' 
        AND mr.status = 'Not Paid'
        AND mr.reading_id NOT IN (
            SELECT COALESCE(transaction_id, 0) FROM arrears WHERE transaction_id = mr.reading_id
        )
    ";

    $overdue_result = mysqli_query($connection, $overdue_query);

    if ($overdue_result && mysqli_num_rows($overdue_result) > 0) {
        while ($overdue_row = mysqli_fetch_assoc($overdue_result)) {
            $member_id = $overdue_row['member_id'];
            $reading_id = $overdue_row['reading_id'];
            $overdue_amount = $overdue_row['total_amount_due'];

            // Check if member already has arrears
            $check_arrears = "SELECT * FROM arrears WHERE member_id = '$member_id'";
            $check_result = mysqli_query($connection, $check_arrears);

            if (mysqli_num_rows($check_result) > 0) {
                // Update existing arrears
                $update_arrears = "
                    UPDATE arrears 
                    SET arrears_amount = arrears_amount + $overdue_amount,
                        transaction_id = '$reading_id'
                    WHERE member_id = '$member_id'
                ";
                mysqli_query($connection, $update_arrears);
            } else {
                // Insert new arrears record
                $insert_arrears = "
                    INSERT INTO arrears (transaction_id, member_id, arrears_amount) 
                    VALUES ('$reading_id', '$member_id', '$overdue_amount')
                ";
                mysqli_query($connection, $insert_arrears);
            }

            // Optional: Update the meter reading status to indicate it's been processed as arrears
            // You can add a field like 'arrears_processed' or keep status as 'Not Paid'
        }
    }
}

// Call the function to update overdue amounts to arrears
updateOverdueToArrears($connection);

// Retrieve members with arrears and their current status
$address_filter = '';
if (isset($_GET['address']) && $_GET['address'] !== 'all') {
    $address = mysqli_real_escape_string($connection, $_GET['address']);
    $address_filter = "WHERE members.address = '$address'";
}

$select_query = "
    SELECT 
        members.member_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname, 
        members.tank_no, 
        members.address, 
        COALESCE(arrears.arrears_amount, 0) AS arrears_amount
    FROM 
        members
    LEFT JOIN 
        arrears 
        ON members.member_id = arrears.member_id
    $address_filter
    ORDER BY members.last_name, members.first_name
";

$select = mysqli_query($connection, $select_query);


// Handle POST requests for meter reading
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve posted data
    $member_id = $_POST['member_id'];
    $reading_id = $_POST['reading_id'];
    $total_usage = $_POST['total_usage'];
    $current_charges = $_POST['current_charges'];
    $arrears_amount = $_POST['arrears_amount'];
    $total_amount_due = $_POST['total_amount_due'];
    $due_date = $_POST['due_date'];
    $disconnection_date = $_POST['disconnection_date'];
    $billing_month = $_POST['billing_month'];

    // Retrieve the latest previous reading
    $sql_previous_reading = "SELECT current_reading FROM meter_reading WHERE member_id = '$member_id' ORDER BY reading_date DESC LIMIT 1";
    $result_previous = mysqli_query($connection, $sql_previous_reading);
    $previous_reading = ($result_previous && mysqli_num_rows($result_previous) > 0)
        ? mysqli_fetch_assoc($result_previous)['current_reading']
        : 0;

    $current_reading = $previous_reading + $total_usage;

    // Gather all existing unpaid readings (IDs and sum of totals)
    $sum_unpaid_amount = 0.00;
    $unpaid_ids = [];
    $sql_unpaid = "SELECT reading_id, total_amount_due FROM meter_reading WHERE member_id = '$member_id' AND status = 'Not Paid'";
    $result_unpaid = mysqli_query($connection, $sql_unpaid);
    if ($result_unpaid && mysqli_num_rows($result_unpaid) > 0) {
        while ($r = mysqli_fetch_assoc($result_unpaid)) {
            $sum_unpaid_amount += (float)$r['total_amount_due'];
            $unpaid_ids[] = $r['reading_id'];
        }
    }

    // Compute additional arrears from arrears table that are NOT represented by unpaid readings
    $arrears_extra = 0.00;
    if (!empty($unpaid_ids)) {
        // If there are unpaid readings, ignore arrears table entirely to prevent double counting
        $arrears_extra = 0.00;
    } else {
        $sql_arrears_extra = "SELECT COALESCE(SUM(arrears_amount),0) AS sum_arrears FROM arrears WHERE member_id = '$member_id'";
        $result_arrears_extra = mysqli_query($connection, $sql_arrears_extra);
        if ($result_arrears_extra && mysqli_num_rows($result_arrears_extra) > 0) {
            $a = mysqli_fetch_assoc($result_arrears_extra);
            $arrears_extra = (float)$a['sum_arrears'];
        }
    }

    // Final arrears to carry forward: unpaid totals + any distinct arrears rows
    $arrears_amount_total = $sum_unpaid_amount + $arrears_extra;

    $current_charges_total = (float)$current_charges;
    $total_amount_due_total = $arrears_amount_total + $current_charges_total;

    // Insert meter reading (use recomputed values)
    $sql_reading = "INSERT INTO meter_reading (reading_id, user_id, member_id, previous_reading, 
            current_reading, total_usage, current_charges, arrears_amount, total_amount_due, due_date, disconnection_date, billing_month, status) 
            VALUES ('$reading_id', '$user_id', '$member_id', '$previous_reading', '$current_reading', 
            '$total_usage', '$current_charges_total', '$arrears_amount_total', '$total_amount_due_total', '$due_date', '$disconnection_date', '$billing_month', 'Not Paid')";

    $sql_invoice = "INSERT INTO invoice (reading_id, user_id, member_id, previous_reading, 
            current_reading, total_usage, current_charges, arrears_amount, total_amount_due, due_date, disconnection_date, billing_month, status) 
            VALUES ('$reading_id', '$user_id', '$member_id', '$previous_reading', '$current_reading', 
            '$total_usage', '$current_charges_total', '$arrears_amount_total', '$total_amount_due_total', '$due_date', '$disconnection_date', '$billing_month', 'Not Paid')";

    if ($connection->query($sql_reading) && $connection->query($sql_invoice)) {
        // Remove all prior unpaid meter readings for this member (avoid redundancy)
        $sql_delete_old_unpaid = "DELETE FROM meter_reading WHERE member_id = '$member_id' AND status = 'Not Paid' AND reading_id <> '$reading_id'";
        if (!$connection->query($sql_delete_old_unpaid)) {
            echo "Error deleting old unpaid readings: " . $connection->error;
        }

        // Clear arrears since they are included in this new bill
        $sql_delete_arrears = "DELETE FROM arrears WHERE member_id = '$member_id'";
        $sql_update_isDone = "UPDATE members SET isDone = 'Done' WHERE member_id = '$member_id'";

        if ($connection->query($sql_delete_arrears) && $connection->query($sql_update_isDone)) {
            header("Location: manage_meter.php");
            exit();
        } else {
            echo "Error updating member data: " . $connection->error;
        }
    } else {
        echo "Error inserting meter reading: " . $connection->error;
    }
}

?>

<?php
if (isset($_GET['address']) && $_GET['address'] !== 'all') {
    // echo "<h5>Showing results for: <strong>" . htmlspecialchars($_GET['address']) . "</strong></h5>";
} else {
    // echo "<h5>Showing results for: <strong>All Addresses</strong></h5>";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meter</title>
    <!-- Link to your local Bootstrap CSS file -->
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>

    <link href="img/lg2.png" rel="icon">

    <style>
        .modal-lg-custom {
            max-width: 90%;
        }

        .overdue {
            background-color: #ffebee !important;
            color: #c62828;
        }

        .has-arrears {
            background-color: #fff3e0 !important;
            color: #ef6c00;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <div id="layoutSidenav">
        <?php include "Includes/header_admin.php"; ?>
        <?php include "Includes/sidebar_admin.php"; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Manage Meter</h1>

                    <br>
                    <form method="GET" action="">
                        <div class="col-md-3 mb-3">
                            <select class="form-select" id="address" name="address" onchange="this.form.submit()">
                                <option disabled <?= !isset($_GET['address']) ? 'selected' : '' ?>>Select Address
                                </option>
                                <option value="all" <?= ($_GET['address'] ?? '') == 'all' ? 'selected' : '' ?>>Show All
                                </option>
                                <option value="Mainuswagon" <?= ($_GET['address'] ?? '') == 'Mainuswagon' ? 'selected' : '' ?>>Mainuswagon</option>
                                <option value="Riverside" <?= ($_GET['address'] ?? '') == 'Riverside' ? 'selected' : '' ?>>
                                    Riverside</option>
                                <option value="Malipayon" <?= ($_GET['address'] ?? '') == 'Malipayon' ? 'selected' : '' ?>>
                                    Malipayon</option>
                                <option value="Malipayon Extension" <?= ($_GET['address'] ?? '') == 'Malipayon Extension' ? 'selected' : '' ?>>Malipayon Extension</option>
                                <option value="Riverside Extension" <?= ($_GET['address'] ?? '') == 'Riverside Extension' ? 'selected' : '' ?>>Riverside Extension</option>
                                <option value="Mabuhay" <?= ($_GET['address'] ?? '') == 'Mabuhay' ? 'selected' : '' ?>>
                                    Mabuhay</option>
                                <option value="Bibiana" <?= ($_GET['address'] ?? '') == 'Bibiana' ? 'selected' : '' ?>>
                                    Bibiana</option>
                            </select>
                        </div>
                    </form>
                    <div class="card mb-4">
                        <div class="card-body">
                            <!-- Legend -->
                            <div class="mb-3">
                                <small class="text-muted">
                                    <span class="badge bg-danger me-2">●</span> Has overdue payments
                                    <span class="badge bg-warning me-2">●</span> Has arrears
                                    <span class="badge bg-success me-2">●</span> Current
                                </small>
                            </div>

                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fullname</th>
                                        <th>Tank Number</th>
                                        <th>Address</th>
                                        <th>Arrears</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) {
                                        // Fetch the latest previous reading for the member
                                        $member_id = $row['member_id'];
                                        $sql_readings = "SELECT current_reading FROM meter_reading WHERE member_id = '$member_id' ORDER BY reading_date DESC LIMIT 1";
                                        $result_readings = mysqli_query($connection, $sql_readings);
                                        if ($result_readings && mysqli_num_rows($result_readings) > 0) {
                                            $reading_row = mysqli_fetch_assoc($result_readings);
                                            $previous_reading = $reading_row['current_reading'];
                                        } else {
                                            $previous_reading = 0;
                                        }

                                        // Check if member has overdue payments
                                        $current_date = date('Y-m-d');
                                        $overdue_check = "
                                            SELECT COUNT(*) as overdue_count 
                                            FROM meter_reading 
                                            WHERE member_id = '$member_id' 
                                            AND due_date < '$current_date' 
                                            AND status = 'Not Paid'
                                        ";
                                        $overdue_result = mysqli_query($connection, $overdue_check);
                                        $overdue_row = mysqli_fetch_assoc($overdue_result);
                                        $has_overdue = $overdue_row['overdue_count'] > 0;

                                        // Check if member has arrears
                                        $has_arrears = $row['arrears_amount'] > 0;

                                        // Determine row class and status
                                        $row_class = '';
                                        $status = 'Current';
                                        $badge_class = 'bg-success';

                                        if ($has_overdue) {
                                            $row_class = 'overdue';
                                            $status = 'Overdue';
                                            $badge_class = 'bg-danger';
                                        } elseif ($has_arrears) {
                                            $row_class = 'has-arrears';
                                            $status = 'Has Arrears';
                                            $badge_class = 'bg-warning';
                                        }
                                        ?>
                                        <tr class="<?php echo $row_class; ?>">
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tank_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td>₱<?php echo number_format($row['arrears_amount'], 2); ?></td>
                                            <td>
                                                <span
                                                    class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#calculateBillModal"
                                                    onclick="openModal(<?php echo $row['member_id']; ?>, '<?php echo htmlspecialchars($row['fullname']); ?>',  '<?php echo htmlspecialchars($row['tank_no']); ?>' , '<?php echo htmlspecialchars($row['address']); ?>' , '<?php echo $user_id; ?>', '<?php echo htmlspecialchars($row['arrears_amount']); ?>' , '<?php echo $user_id; ?>', '<?php echo $previous_reading; ?>')">
                                                    Read Meter
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Modal -->
            <div class="modal fade" id="calculateBillModal" tabindex="-1" aria-labelledby="calculateBillModalLabel"
                aria-text="true">
                <div class="modal-dialog modal-lg-custom">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="calculateBillModalLabel">Reading for <span
                                        id="userFullname"></span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <input type="hidden" name="user_id" id="user_id">
                                    <input type="hidden" name="member_id" id="member_id">
                                    <input type="hidden" id="reading_id" name="reading_id" value="<?php
                                    echo rand(100000000, 999999999);
                                    ?>" required autocomplete="off">
                                    <input type="hidden" id="invoice_id" name="invoice_id" readonly>
                                    <input type="hidden" class="form-control" id="edit_tank_no" name="edit_tank_no"
                                        required readonly>
                                    <input type="hidden" class="form-control" id="edit_address" name="edit_address"
                                        required readonly>
                                    <input type="hidden" class="form-control" id="previous_reading"
                                        name="previous_reading" placeholder="Enter previous_reading" required>
                                    <input type="hidden" class="form-control" id="current_reading"
                                        name="current_reading" placeholder="0" required readonly>
                                    <div class="mb-3">
                                        <label for="total_usage" class="form-label">Total Usage (Cubic Meters)</label>
                                        <input type="number" style="border: 2px solid #000;" class="form-control"
                                            id="total_usage" name="total_usage" placeholder="Enter Total Usage"
                                            oninput="calculateTotalBill()" autocomplete="off" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="current_charges" class="form-label">Current Charges</label>
                                        <input type="number" step="0.01" class="form-control" id="current_charges"
                                            name="current_charges" value="0.00" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_arrears_amount" class="form-label">Arrears (From Previous
                                            Bills)</label>
                                        <input type="number" step="0.01" class="form-control" id="edit_arrears_amount"
                                            name="arrears_amount" value="0.00" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="total_amount_due" class="form-label">Total Amount Due</label>
                                        <input type="number" step="0.01" class="form-control" id="total_amount_due"
                                            name="total_amount_due" value="0.00" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" id="dueDate" name="due_date" readonly
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" id="disconnectionDate"
                                            name="disconnection_date" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="billing_month" class="form-label">Billing Month</label>
                                        <input type="text" class="form-control" id="billing_month" name="billing_month"
                                            readonly required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Submit Reading</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

    <script>
        function openModal(member_id, fullname, tank_no, address, previous_reading, arrears_amount, user_id) {
            document.getElementById('member_id').value = member_id;
            document.getElementById('userFullname').innerText = fullname;
            document.getElementById('edit_tank_no').value = tank_no;
            document.getElementById('edit_address').value = address;
            document.getElementById('previous_reading').value = previous_reading;
            document.getElementById('edit_arrears_amount').value = arrears_amount;
            document.getElementById('user_id').value = user_id;

            // Clear the total usage field
            document.getElementById('total_usage').value = '';
            document.getElementById('current_charges').value = '0.00';
            document.getElementById('total_amount_due').value = arrears_amount;

            // Set due date (15 days from today)
            const currentDate = new Date();
            const dueDate = new Date(currentDate);
            dueDate.setDate(currentDate.getDate() + 15);

            const formattedDueDate = dueDate.toISOString().split('T')[0];
            document.getElementById('dueDate').value = formattedDueDate;

            // Set disconnection date (2 days after due date)
            const disconnectionDate = new Date(dueDate);
            disconnectionDate.setDate(dueDate.getDate() + 2);

            const formattedDisconnectionDate = disconnectionDate.toISOString().split('T')[0];
            document.getElementById('disconnectionDate').value = formattedDisconnectionDate;
        }

        // Set same ID values
        document.addEventListener('DOMContentLoaded', function () {
            var readingIdValue = document.getElementById('reading_id').value;
            document.getElementById('invoice_id').value = readingIdValue;

            // Set billing month
            const today = new Date();
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            const currentMonth = monthNames[today.getMonth()];
            const year = today.getFullYear();
            document.getElementById('billing_month').value = `${currentMonth} ${year}`;
        });
    </script>

    <!-- calculate bill -->
    <script>
        function calculateTotalBill() {
            const totalUsageField = document.getElementById('total_usage');
            const currentChargesField = document.getElementById('current_charges');
            const arrearsAmountField = document.getElementById('edit_arrears_amount');
            const totalAmountDueField = document.getElementById('total_amount_due');
            const addressField = document.getElementById('edit_address');

            const totalUsage = parseFloat(totalUsageField.value) || 0;
            const arrearsAmount = parseFloat(arrearsAmountField.value) || 0;
            const address = addressField.value.trim();

            let currentCharges = 0;

            if (address === "Malipayon Extension") {
                // Always charge ₱18 per unit, starting from 1
                currentCharges = totalUsage * 18;
            } else {
                if (totalUsage <= 5 && totalUsage > 0) {
                    // Flat rate for 1 to 5 usage
                    currentCharges = 75;
                } else if (totalUsage >= 6) {
                    // Start charging at ₱15 per unit from usage 6+
                    currentCharges = totalUsage * 15;
                }
            }

            currentChargesField.value = currentCharges.toFixed(2);
            const totalAmountDue = currentCharges + arrearsAmount;
            totalAmountDueField.value = totalAmountDue.toFixed(2);
        }

    </script>

</body>

</html>