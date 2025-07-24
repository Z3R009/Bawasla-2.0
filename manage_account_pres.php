<?php
include 'DBConnection.php';

$username = ""; // Initialize variable to avoid errors

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $user_id = mysqli_real_escape_string($connection, $user_id);

    // Fetch member information
    $sql = "SELECT * FROM users 
            WHERE user_id = '$user_id'";

    // Execute the query
    $result = mysqli_query($connection, $sql);

} else {
    echo "No User ID provided.";
}

// Change Password
if (isset($_POST['change'])) {
    $password_old = mysqli_real_escape_string($connection, $_POST['password_old']);
    $password_new = mysqli_real_escape_string($connection, $_POST['password_new']);
    $password_con = mysqli_real_escape_string($connection, $_POST['password_con']);

    // Fetch current password from the members table
    $sql = "SELECT password FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $current_password = $row['password'];

        // Verify if the old password matches
        if (password_verify($password_old, $current_password)) {
            // Check if the new password and confirm password match
            if ($password_new === $password_con) {
                // Hash the new password
                $hashed_password = password_hash($password_new, PASSWORD_DEFAULT);

                // Update password in both tables

                $update_users_sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";

                if (mysqli_query($connection, $update_users_sql)) {
                    // echo "Password updated successfully in both tables!";
                } else {
                    echo "Error updating password: " . mysqli_error($connection);
                }
            } else {
                // $errors[] = "New password and confirm password do not match.";
            }
        } else {
            $errors[] = "Current password is incorrect.";
        }
    } else {
        $errors[] = "Member not found.";
    }
}

// Change Username
if (isset($_POST['upd_name'])) {
    $new_username = mysqli_real_escape_string($connection, $_POST['new_username']);

    // Update username in both tables
    $update_users_sql = "UPDATE users SET username = '$new_username' WHERE user_id = '$user_id'";

    if (mysqli_query($connection, $update_users_sql)) {
        // echo "Username updated successfully in both tables!";
    } else {
        echo "Error updating username: " . mysqli_error($connection);
    }
}
?>

<!-- Display errors if any -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Display errors if any -->
<!-- <?php
if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?> -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        /* Center the cards, set width, and add top padding */
        body {
            padding-top: 50px;
            /* Adds space at the top of the page */
        }

        .card-container {
            max-width: 700px;
            margin: auto auto;
            /* Reduces spacing from the top */
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
            <img src="img/lg2.png" alt="Logo" style="height: 40px; width: auto;">
        </a>
        <!-- Sidebar Toggle-->
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
                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Account Settings</span></a></li>
                    <li><a class="dropdown-item" href="#!"><i class="fa-solid fa-file"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Activity Logs</span></a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Log Out</span></a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div class="container mt-4 card-container">
            <!-- Change Password Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm" method="POST">
                        <div class="mb-3">
                            <label for="password_old" class="form-label">Current Password</label>
                            <input type="password" name="password_old" class="form-control" id="password_old" required>
                            <small id="currentPasswordError" class="text-danger" style="display: none;">Incorrect
                                current password.</small>
                        </div>
                        <div class="mb-3">
                            <label for="password_new" class="form-label">New Password</label>
                            <input type="password" name="password_new" class="form-control" id="password_new" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_con" class="form-label">Re-type New Password</label>
                            <input type="password" name="password_con" class="form-control" id="password_con" required>
                            <small id="passwordMatchError" class="text-danger" style="display: none;">Passwords do not
                                match.</small>
                        </div>
                        <button type="submit" name="change" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>

            <!-- Change Username Card -->
            <div class="card">
                <div class="card-header">
                    <h5>Change Username</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 me-2">
                                <label for="new_username" class="form-label">New Username</label>
                                <input type="text" name="new_username" class="form-control" id="new_username"
                                    autocomplete="off" required>
                            </div>
                            <button type="submit" name="upd_name" class="btn btn-primary align-self-end"
                                style="height: fit-content; margin-top: 24px;">Change</button>
                        </div>
                    </form>
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

    <!-- password -->

    <script src="start/jquery/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            const currentPasswordInput = $('#password_old');
            const newPasswordInput = $('#password_new');
            const confirmPasswordInput = $('#password_con');
            const currentPasswordError = $('#currentPasswordError');
            const passwordMatchError = $('#passwordMatchError');

            // Check if current password is correct in real-time
            currentPasswordInput.on('input', function () {
                const currentPassword = $(this).val();
                if (currentPassword) {
                    $.ajax({
                        url: 'check_password.php', // Server-side script to verify current password
                        type: 'POST',
                        data: { password: currentPassword },
                        success: function (response) {
                            if (response === 'false') {
                                currentPasswordError.text("Incorrect current password.").show();
                            } else {
                                currentPasswordError.hide();
                            }
                        }
                    });
                } else {
                    currentPasswordError.hide();
                }
            });

            // Check if new password and confirm password match in real-time
            newPasswordInput.add(confirmPasswordInput).on('input', function () {
                if (newPasswordInput.val() !== confirmPasswordInput.val()) {
                    passwordMatchError.text("Passwords do not match.").show();
                } else {
                    passwordMatchError.hide();
                }
            });
        });
    </script>


</body>

</html>