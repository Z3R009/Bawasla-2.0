<?php
// Include necessary files and start session
include 'DBConnection.php';

// Ensure the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Retrieve members with arrears
$select = mysqli_query($connection, "
    SELECT 
        members.member_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname, 
        members.tank_no, 
        members.address, 
        arrears.arrears_amount
    FROM 
        members
    LEFT JOIN 
        arrears 
        ON members.member_id = arrears.member_id
    WHERE 
        members.isDone = 'Not Done'
");

// Handle POST requests for meter reading and SMS
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

    // Check for existing billing month
    $sql_check_billing_month = "SELECT * FROM meter_reading WHERE member_id = '$member_id' AND billing_month = '$billing_month'";
    $result_check = mysqli_query($connection, $sql_check_billing_month);

    if (mysqli_num_rows($result_check) > 0) {
        echo "<script type='text/javascript'>
    alert('A reading for this billing month already exists for the selected member.');
    window.location = 'manage_meter.php';
</script>";
    } else {
        // Insert meter reading
        $sql_reading = "INSERT INTO meter_reading (reading_id, user_id, member_id, previous_reading, 
            current_reading, total_usage, current_charges, arrears_amount, total_amount_due, due_date, disconnection_date, billing_month) 
            VALUES ('$reading_id', '$user_id', '$member_id', '$previous_reading', '$current_reading', 
            '$total_usage', '$current_charges', '$arrears_amount', '$total_amount_due', '$due_date', '$disconnection_date', '$billing_month')";

        if ($connection->query($sql_reading)) {
            // Delete arrears and update member status
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
        td:nth-child(6),
        th:nth-child(6) {
            display: none;
        }

        .modal-lg-custom {
            max-width: 90%;
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
                    <h1 class="mt-4">Manage Meter</h1>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fullname</th>
                                        <th>Address</th>
                                        <th>Tank Number</th>
                                        <th>Arrears</th>
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
                                            $previous_reading = $reading_row['current_reading']; // Last billing's current reading
                                        } else {
                                            $previous_reading = 0; // Default if no previous records
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tank_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['arrears_amount']); ?></td>
                                            <td>
                                                <button class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#calculateBillModal"
                                                    onclick="openModal(<?php echo $row['member_id']; ?>, '<?php echo htmlspecialchars($row['fullname']); ?>', '<?php echo htmlspecialchars($row['address']); ?>' , '<?php echo htmlspecialchars($row['tank_no']); ?>' , '<?php echo $user_id; ?>', '<?php echo htmlspecialchars($row['arrears_amount']); ?>' , '<?php echo $user_id; ?>', '<?php echo $previous_reading; ?>')">

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
                aria-hidden="true">
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
                                    <!-- info hidden -->
                                    <!-- <label for="edit_tank_no" class="form-label">Tank Number</label> -->
                                    <input type="hidden" class="form-control" id="edit_tank_no" name="edit_tank_no"
                                        required readonly>
                                    <!-- <label for="edit_meter_no" class="form-label">Meter Number</label> -->
                                    <input type="hidden" class="form-control" id="edit_meter_no" name="edit_meter_no"
                                        required readonly>
                                    <!-- <label for="edit_address" class="form-label">Address</label> -->
                                    <input type="hidden" class="form-control" id="edit_address" name="edit_address"
                                        required readonly>
                                    <!-- <label for="edit_mobile_number" class="form-label">Mobile Number</label> -->
                                    <input type="hidden" class="form-control" id="edit_mobile_number"
                                        name="edit_mobile_number" required readonly>
                                    <!-- <label for="previous_reading" class="form-label">Previous Reading</label> -->
                                    <input type="hidden" class="form-control" id="previous_reading"
                                        name="previous_reading" placeholder="Enter previous_reading" required readonly>
                                    <!-- <label for="current_reading" class="form-label">Current Reading</label> -->
                                    <input type="hidden" class="form-control" id="current_reading"
                                        name="current_reading" placeholder="0" required readonly>
                                    <div class="mb-3">
                                        <label for="total_usage" class="form-label">Total Usage</label>
                                        <input type="number" style="border: 2px solid #000;" class="form-control"
                                            id="total_usage" name="total_usage" placeholder="Enter Total Usage"
                                            oninput="calculateTotalBill()" autocomplete="off" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="current_charges" class="form-label">Current Charges</label>
                                        <input type="number" class="form-control" id="current_charges"
                                            name="current_charges" value="0.00" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="current_charges" class="form-label">Arrears (From Last
                                            Month)</label>
                                        <input type="number" class="form-control" id="edit_arrears_amount"
                                            name="arrears_amount" value="0.00" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="total_amount_due" class="form-label">Total Amount Due</label>
                                        <input type="number" class="form-control" id="total_amount_due"
                                            name="total_amount_due" value="0.00" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label for="dueDate" class="form-label">Due Date</label> -->
                                        <input type="hidden" class="form-control" id="dueDate" name="due_date" readonly
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label for="disconnectionDate" class="form-label">Disconnection Date</label> -->
                                        <input type="hidden" class="form-control" id="disconnectionDate"
                                            name="disconnection_date" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="billing_month" class="form-label">Billing Month</label>
                                        <input type="text" class="form-control" id="billing_month" name="billing_month"
                                            readonly required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Submit</button>
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
        function openModal(memberId, fullname) {
            document.getElementById('userFullname').textContent = fullname;
            document.getElementById('member_id').value = member_id;
        }
    </script>

    <script>
        function openModal(member_id, fullname, tank_no, meter_no, address, mobile_number, previous_reading, arrears_amount, user_id) {
            document.getElementById('member_id').value = member_id;
            document.getElementById('userFullname').innerText = fullname;
            document.getElementById('edit_tank_no').value = tank_no;
            document.getElementById('edit_meter_no').value = meter_no;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_mobile_number').value = mobile_number;
            document.getElementById('previous_reading').value = previous_reading;
            document.getElementById('edit_arrears_amount').value = arrears_amount;
            document.getElementById('user_id').value = user_id;

            // Clear the current reading field initially
            document.getElementById('current_reading').value = '';

            function updateCurrentReading() {
                const previousReading = parseFloat(document.getElementById('previous_reading').value) || 0;
                const total_usage = parseFloat(document.getElementById('total_usage').value) || 0;
                const currentReading = previousReading + total_usage;
                document.getElementById('current_reading').value = currentReading;
            }

            const currentDate = new Date();
            const dueDate = new Date(currentDate);
            dueDate.setDate(currentDate.getDate() + 15);  // Add 15 days for the due date

            // Format the due date to YYYY-MM-DD
            const formattedDueDate = dueDate.toISOString().split('T')[0];
            document.getElementById('dueDate').value = formattedDueDate;

            // Calculate the disconnection date (2 days after the due date)
            const disconnectionDate = new Date(dueDate);
            disconnectionDate.setDate(dueDate.getDate() + 2);  // Add 2 days for disconnection

            // Format the disconnection date to YYYY-MM-DD
            const formattedDisconnectionDate = disconnectionDate.toISOString().split('T')[0];
            document.getElementById('disconnectionDate').value = formattedDisconnectionDate;
        }
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


    <!-- same id on textfield -->
    <script>
        // When the document loads, set the values of the other fields to match the hidden item_id value
        document.addEventListener('DOMContentLoaded', function () {
            var readingIdValue = document.getElementById('reading_id').value;

            // Set the values of the other two fields to match item_id
            document.getElementById('invoice_id').value = readingIdValue;
        });
    </script>

    <!-- billing month -->
    <script>
        // Get the current date
        const today = new Date();

        // Define an array for month names
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Get the current month name and year
        const currentMonth = monthNames[today.getMonth()];
        const year = today.getFullYear();

        // Set the value of the billing month input
        document.getElementById('billing_month').value = `${currentMonth} ${year}`;
    </script>




</body>

</html>