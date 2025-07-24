<?php
include 'DBConnection.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['paymentProof']) && $_FILES['paymentProof']['error'] == 0) {
        // Get form data
        $member_id = $_POST['member_id'];
        $reading_id = $_POST['reading_id'];
        $current_charges = $_POST['current_charges'];
        $arrears_amount = $_POST['arrears_amount'];
        $total_amount_due = $_POST['total_amount_due'];
        $billing_month = $_POST['billing_month'];

        // Define variables for file upload
        $fileTmpPath = $_FILES['paymentProof']['tmp_name'];
        $fileName = $_FILES['paymentProof']['name'];
        $uploadFileDir = 'uploads/';
        $dest_path = $uploadFileDir . basename($fileName);

        // Move the uploaded file to the designated folder
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Insert payment data including the file path into the database
            $payment_query = "INSERT INTO pending (member_id, reading_id, current_charges, arrears_amount, total_amount_due, billing_month, proof_image_path) 
                              VALUES ('$member_id', '$reading_id', '$current_charges', '$arrears_amount', '$total_amount_due', '$billing_month', '$dest_path')";

            if (mysqli_query($connection, $payment_query)) {
                // Update the status in the meter_reading table to 'Pending'
                $update_status_query = "UPDATE meter_reading SET status = 'Pending' WHERE reading_id = '$reading_id'";

                if (mysqli_query($connection, $update_status_query)) {
                    // Redirect to the dashboard after successful submission and status update
                    header("Location: dashboard_member.php?member_id=" . urlencode($member_id));
                    exit();
                } else {
                    echo "Error updating status: " . mysqli_error($connection);
                }
            } else {
                echo "Error saving to database: " . mysqli_error($connection);
            }
        } else {
            echo "There was an error moving the uploaded file.";
        }

    } else {
        echo "Error uploading file. Error Code: " . $_FILES['paymentProof']['error'];
    }
} elseif (isset($_GET['member_id'])) {
    // Retrieve and sanitize the member_id from GET
    $member_id = mysqli_real_escape_string($connection, $_GET['member_id']);

    // Fetch data for current charges display
    $select = mysqli_query($connection, "
        SELECT 
            meter_reading.reading_date, 
            CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname,
            meter_reading.current_charges,
            meter_reading.arrears_amount,
            meter_reading.total_amount_due,
            meter_reading.reading_id,
            meter_reading.member_id,
            meter_reading.due_date,
            meter_reading.disconnection_date,
            meter_reading.billing_month,
            meter_reading.status
        FROM 
            meter_reading
        JOIN 
            members ON meter_reading.member_id = members.member_id
        WHERE 
            members.member_id = '$member_id' AND status = 'Not Paid' 
        ORDER BY 
            reading_date DESC
    ");
} else {
    echo "Member ID is missing.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <link rel="stylesheet" href="bootstrap-5.2.3/css/bootstrap.min.css">
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #receiptModal * {
                visibility: visible;
            }

            #receiptModal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
            }

        }


        td:nth-child(8),
        th:nth-child(8) {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <a href="dashboard_member.php?member_id=<?php echo $member_id; ?>" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card">
            <div class="card-body">
                <!-- Display Current Charges -->
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Reading Date</th>
                            <th>Due Date</th>
                            <th>Disconnection Date</th>
                            <th>Current Charges</th>
                            <th>Arrears</th>
                            <th>Total Amount Due</th>
                            <th>Billing Month</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($select) > 0) {
                            while ($row = mysqli_fetch_assoc($select)) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['reading_date']) . "</td>
                                    <td>" . htmlspecialchars($row['due_date']) . "</td>
                                    <td>" . htmlspecialchars($row['disconnection_date']) . "</td>
                                    <td>" . htmlspecialchars($row['current_charges']) . "</td>
                                    <td>" . htmlspecialchars($row['arrears_amount']) . "</td>
                                    <td>" . htmlspecialchars($row['total_amount_due']) . "</td>
                                    <td>" . htmlspecialchars($row['billing_month']) . "</td>
                                    <td>" . htmlspecialchars($row['status']) . "</td>
                                </tr>";
                                // Hidden form fields for each row
                                echo '<form action="" method="POST" enctype="multipart/form-data" class="my-4">
                                    <input type="hidden" name="member_id" value="' . htmlspecialchars($member_id) . '">
                                    <input type="hidden" name="reading_id" value="' . htmlspecialchars($row['reading_id']) . '">
                                    <input type="hidden" name="current_charges" value="' . htmlspecialchars($row['current_charges']) . '">
                                    <input type="hidden" name="arrears_amount" value="' . htmlspecialchars($row['arrears_amount']) . '">
                                    <input type="hidden" name="total_amount_due" value="' . htmlspecialchars($row['total_amount_due']) . '">
                                    <input type="hidden" name="billing_month" value="' . htmlspecialchars($row['billing_month']) . '">
                                    <div class="mb-3">
                                        <label for="paymentProof" class="form-label">Upload Proof of Payment:</label>
                                        <input type="file" class="form-control" name="paymentProof" accept="image/*" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Submit Proof</button>
                                </form>';
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No current charges available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <div class="container text-center mt-5">
                    <!-- QR Code Section -->
                    <div class="text-center my-4">
                        <h2>Scan to Pay</h2>
                        <img src="img/qr.jpg" alt="QR Code for Payment" class="img-fluid" style="max-width: 250px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script src="start/js/scripts.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <script src="start/assets/demo/chart-area-demo.js"></script>
    <script src="start/assets/demo/chart-bar-demo.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>
</body>

</html>