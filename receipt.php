<?php
include 'DBConnection.php';

if (isset($_GET['transaction_id'])) {
    $transaction_id = $_GET['transaction_id'];

    // Retrieve payment details based on transaction_id
    $query = "
    SELECT history.*, members.address 
    FROM history 
    JOIN members ON history.member_id = members.member_id 
    WHERE history.transaction_id = '$transaction_id'
";
    $result = mysqli_query($connection, $query);
    $payment_details = mysqli_fetch_assoc($result);

    if (!$payment_details) {
        echo "Payment details not found.";
        exit();
    }
} else {
    echo "No transaction ID provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            width: 600px;
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .receipt-header img {
            height: 50px;
            margin-bottom: 10px;
        }

        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .receipt-section {
            margin-bottom: 15px;
        }

        .receipt-section p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }

        .receipt-section p strong {
            color: #333;
        }

        .print-button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            text-align: center;
        }

        .print-button:hover {
            background-color: #218838;
        }

        @media print {

            .modal-footer {
                display: none;
            }
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 16px;
            background-color: gray;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: none;
            color: #fff;
        }
    </style>
</head>

<body>


    <div class="receipt-container">
        <div class="header-section d-flex justify-content-center align-items-center mb-4">
            <img src="img/lg1.jpg" alt="Logo" style="width: 80px; height: auto; margin-right: 15px;">
            <div>
                <h6 class="mb-0 text-center">BARANGAY WATER SYSTEM & LIVELIHOOD
                    ASSOCIATION</h6>
                <p class="mb-0 text-center">Silway-7, Polomolok, South Cotabato</p>
            </div>
        </div>

        <div class="receipt-section">
            <div class="row">
                <div class="col-6">
                    <p><strong>Receipt No.:</strong> <?php echo $payment_details['transaction_id']; ?></p>
                </div>
                <div class="col-6 text-end">
                    <p><strong>Payment Date:</strong> <?php echo $payment_details['payment_date']; ?></p>
                </div>
            </div>

            <p><strong>Full Name:</strong> <?php echo $payment_details['fullname']; ?></p>
            <p><strong>Address:</strong> <?php echo $payment_details['address']; ?></p>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 33%;">Reading Date</th>
                    <th style="width: 33%;">Amount Paid</th>
                    <th style="width: 33%;">Billing Month</th>
                </tr>
                <tr>
                    <td>
                        <p><?php echo $payment_details['reading_date']; ?></p>
                    </td>
                    <td>
                        <p><?php echo $payment_details['amount_paid']; ?></p>
                    </td>
                    <td>
                        <p><?php echo $payment_details['billing_month']; ?></p>
                    </td>
                </tr>
            </table>
        </div>


        <div class="modal-footer">
            <a class="back-button" href="transaction_treas.php">

                Back
            </a>
            <button class="print-button" onclick="window.print()">Print Receipt</button>
        </div>
    </div>

</body>

</html>