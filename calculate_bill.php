<?php
include 'DBConnection.php';

// Check if user_id is provided
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Retrieve user details from the database based on the user_id
    $query = "SELECT * FROM members WHERE user_id = '$user_id'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        echo "No user found with the given ID.";
        exit;
    }
} else {
    echo "No user ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculate Bill</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
</head>

<body>
    <div class="container mb-3 mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 col-sm-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h2 class="card-title">Calculate Bill for
                            <?php echo $user['last_name'] . ', ' . $user['first_name']; ?></h2>
                    </div>
                    <div class="card-body">
                        <form action="process_bill.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

                            <div class="mb-3">
                                <label for="bill_amount" class="form-label">Bill Amount</label>
                                <input type="number" class="form-control" id="bill_amount" name="bill_amount"
                                    placeholder="Enter bill amount" required>
                            </div>

                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </form>
                    </div>
                </div>
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
</body>

</html>