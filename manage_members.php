<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

//Add members
function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    return ($row['count'] == 0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming you've already established a database connection as $connection

    // Get user data from the form
    $user_id = $_POST['user_id'];
    $member_id = $_POST['member_id'];
    $tank_no = $_POST['tank_no'];
    $meter_no = $_POST['meter_no'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Securely hash the password
    $user_type = $_POST['user_type'];
    $isDone = $_POST['isDone'];

    // Check if the address already exists in the 'members' table
    $check_address_sql = "SELECT COUNT(*) as count FROM members WHERE address = '$address'";
    $check_address_result = $connection->query($check_address_sql);
    $address_count = $check_address_result->fetch_assoc()['count'];

    // If the address doesn't exist, add it to the 'members' table (or a separate addresses table)
    if ($address_count == 0) {
        // You might want to insert into a separate addresses table if you have one.
        // Assuming you're using the same 'members' table for simplicity:
        // $insert_address_sql = "INSERT INTO addresses (address) VALUES ('$address')";
        // $connection->query($insert_address_sql);
    }

    // Insert into 'members' table
    $sql_member = "INSERT INTO members (user_id, member_id, tank_no, meter_no, first_name, middle_name, last_name, address, mobile_number, username, password, user_type, isDone) 
                   VALUES ('$user_id', '$member_id', '$tank_no', '$meter_no', '$first_name', '$middle_name', '$last_name', '$address', '$mobile_number', '$username', '$password', '$user_type', '$isDone')";

    // Insert into 'users' table
    $sql_user = "INSERT INTO users (user_id, member_id, username, password, user_type) 
                 VALUES ('$user_id', '$member_id', '$username', '$password', '$user_type')";

    // Execute both queries
    if ($connection->query($sql_member) && $connection->query($sql_user)) {
        // Redirect to manage members page
        header('Location: manage_members.php');
        exit();
    } else {
        // Handle the error if either query fails
        echo "Error: " . $connection->error;
    }
}


// Retrieve users
$select = mysqli_query($connection, "SELECT * FROM members");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
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

        td:nth-child(9),
        th:nth-child(9) {
            display: none;
        }

        .modal-lg-custom {
            max-width: 1000px;
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
                    <h1 class="mt-4 d-flex justify-content-between align-items-center">
                        Manage Members
                        <button class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addUserModal"
                            type="button">
                            <i class="fas fa-plus"></i> Add Members
                        </button>
                    </h1>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg-custom">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add Member</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addUserForm">
                                        <input type="hidden" id="member_id" name="member_id"
                                            value="<?php echo rand(100000000, 999999999); ?>" required
                                            autocomplete="off">
                                        <input type="hidden" id="user_id" name="user_id" readonly>

                                        <!-- Name fields in a single row -->
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="last_name" name="last_name"
                                                    placeholder="Last Name" required autocomplete="off">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="first_name" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="first_name"
                                                    name="first_name" placeholder="First Name" required
                                                    autocomplete="off">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="middle_name" class="form-label">Middle Name
                                                    (optional)</label>
                                                <input type="text" class="form-control" id="middle_name"
                                                    name="middle_name" placeholder="Middle Name" autocomplete="off">
                                            </div>
                                        </div>

                                        <!-- Tank Number and Meter Number in a single row with reduced width -->
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="tank_no" class="form-label">Tank Number</label>
                                                <input type="number" class="form-control" id="tank_no" name="tank_no"
                                                    placeholder="Tank No." required autocomplete="off">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="meter_no" class="form-label">Meter Number</label>
                                                <input type="number" class="form-control" id="meter_no" name="meter_no"
                                                    placeholder="Meter No." required autocomplete="off">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <select class="form-select" id="address" name="address">
                                                    <option selected disabled>Select Address</option>
                                                    <option value="Mainuswagon">Mainuswagon</option>
                                                    <option value="Riverside">Riverside</option>
                                                    <option value="Malipayon">Malipayon</option>
                                                    <option value="Malipayon Extension">Malipayon Extension</option>
                                                    <option value="Riverside Extension">Riverside Extension</option>
                                                    <option value="Mabuhay">Mabuhay</option>
                                                    <option value="Bibiana">Bibiana</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                                <input type="text" class="form-control" id="mobile_number"
                                                    name="mobile_number" required maxlength="13" autocomplete="off"
                                                    placeholder="Enter Mobile Number">

                                                <!-- <span id="errorMsg" style="color: red; display: none;">Please enter
                                                    numbers only</span> -->
                                            </div>
                                        </div>



                                        <!-- Username and Password fields in full width -->
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                placeholder="Enter Username" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Enter Password" required autocomplete="off">
                                        </div>

                                        <input type="hidden" id="user_type" name="user_type" value="Member">
                                        <input type="hidden" id="isDone" name="isDone" value="Not Done">

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                onclick="clearForm()">Clear</button>
                                            <button type="submit" id="submit" name="submit"
                                                class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>id</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Tank Number</th>
                                        <th>Meter Number</th>
                                        <th>Address</th>
                                        <th>Mobile Number</th>
                                        <th>Username</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tank_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['meter_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td><?php echo htmlspecialchars($row['mobile_number']); ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Action
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;"
                                                        aria-labelledby="actionDropdown">
                                                        <li><a class="dropdown-item" href="javascript:void(0)"
                                                                onclick="editUser('<?php echo $row['member_id']; ?>', '<?php echo $row['last_name']; ?>', '<?php echo $row['first_name']; ?>', '<?php echo $row['middle_name']; ?>', '<?php echo $row['tank_no']; ?>', '<?php echo $row['meter_no']; ?>', '<?php echo $row['address']; ?>', '<?php echo $row['mobile_number']; ?>' )">Edit</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)"
                                                                onclick="deleteUser(<?php echo $row['member_id']; ?>)">Delete</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal for Edit User Form -->
                    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg-custom">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editUserModalLabel">Edit Member Info</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editUserForm" method="post" action="update_member.php">
                                        <input type="hidden" name="user_id" id="edit_user_id">


                                        <!-- Name fields in a single row -->
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="edit_last_name" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="last_name"
                                                    id="edit_last_name" placeholder="Enter Last Name" autocomplete="off"
                                                    required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="edit_first_name" class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="first_name"
                                                    id="edit_first_name" placeholder="Enter First Name"
                                                    autocomplete="off" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="edit_middle_name" class="form-label">Middle
                                                    Name(optional)</label>
                                                <input type="text" class="form-control" name="middle_name"
                                                    id="edit_middle_name" placeholder="Enter Middle Name"
                                                    autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="edit_tank_no" class="form-label">Tank Number</label>
                                                <input type="number" class="form-control" name="tank_no"
                                                    id="edit_tank_no" placeholder="Enter Tank Number" autocomplete="off"
                                                    required>
                                            </div>
                                            <div class=" col-md-3 mb-3">
                                                <label for="edit_meter_no" class="form-label">Meter Number</label>
                                                <input type="number" class="form-control" name="meter_no"
                                                    id="edit_meter_no" placeholder="Enter Meter Number"
                                                    autocomplete="off" autocomplete="off" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="edit_address" class="form-label">Address</label>
                                                <select class="form-select" id="edit_address" name="address">
                                                    <option selected disabled>Select Address</option>
                                                    <option value="Mainuswagon">Mainuswagon</option>
                                                    <option value="Riverside">Riverside</option>
                                                    <option value="Malipayon">Malipayon</option>
                                                    <option value="Malipayon Extension">Malipayon Extension</option>
                                                    <option value="Riverside Extension">Riverside Extension</option>
                                                    <option value="Mabuhay">Mabuhay</option>
                                                    <option value="Bibiana">Bibiana</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="edit_mobile_number" class="form-label">Mobile Number</label>
                                                <input type="text" class="form-control" id="edit_mobile_number"
                                                    name="mobile_number" required maxlength="13" autocomplete="off"
                                                    placeholder="Enter Mobile Number">
                                                <span id="errorMsg" style="color: red; display: none;">Please enter
                                                    numbers
                                                    only</span>
                                            </div>
                                        </div>


                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" name="update_user">Save
                                                changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
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

    <!-- function -->

    <!-- clear -->
    <script>
        // Function to clear form
        function clearForm() {
            document.getElementById('addUserForm').reset();
        }
    </script>

    //delete
    <script>
        function deleteUser(memberId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = 'delete_member.php?member_id=' + memberId + '&confirm=yes';
            }
        }
    </script>

    <script>
        // Function to handle the Edit button click and populate the modal with user data
        function editUser(user_id, last_name, first_name, middle_name, tank_no, meter_no, address, mobile_number) {
            document.getElementById('edit_user_id').value = user_id;
            document.getElementById('edit_last_name').value = last_name;
            document.getElementById('edit_first_name').value = first_name;
            document.getElementById('edit_middle_name').value = middle_name;
            document.getElementById('edit_tank_no').value = tank_no;
            document.getElementById('edit_meter_no').value = meter_no;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_mobile_number').value = mobile_number;

            // Show the modal
            var editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editUserModal.show();
        }

        // Function to save changes
        function saveEdit() {
            // Retrieve form values
            var last_name = document.getElementById('edit_last_name').value;
            var first_name = document.getElementById('edit_first_name').value;
            var middle_name = document.getElementById('edit_middle_name').value;
            var tank_no = document.getElementById('edit_tank_no').value;
            var meter_no = document.getElementById('edit_meter_no').value;
            var address = document.getElementById('edit_address').value;
            var mobile_number = document.getElementById('edit_mobile_number').value;

            // Example code for sending data to the server
            // You should implement the actual save functionality, e.g., using AJAX

            console.log('last_name:', last_name);
            console.log('first_name:', first_name);
            console.log('middle_name:', middle_name);
            console.log('tank_no:', tank_no);
            console.log('meter_no:', meter_no);
            console.log('address:', address);
            console.log('mobile_number:', mobile_number);
            // Close the modal
            var editUserModal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            editUserModal.hide();
        }
    </script>

    <script>
        const mobile_numberInput = document.getElementById('mobile_number');
        const errorMessage = document.getElementById('errorMsg');

        mobile_numberInput.addEventListener('input', function (e) {
            let input = e.target.value;
            let numericInput = input.replace(/\D/g, '');
            if (numericInput.length > 11) {
                numericInput = numericInput.slice(0, 11);
            }
            e.target.value = numericInput;

            if (input !== numericInput) {
                errorMessage.style.display = 'block';
            } else {
                errorMessage.style.display = 'none';
            }
        });
    </script>

    <!-- same id on textfield -->
    <script>
        // When the document loads, set the values of the other fields to match the hidden item_id value
        document.addEventListener('DOMContentLoaded', function () {
            var memberIdValue = document.getElementById('member_id').value;

            // Set the values of the other two fields to match item_id
            document.getElementById('user_id').value = memberIdValue;
        });
    </script>

    <!-- mobile number -->
    <!-- <script>
        function formatMobileNumber() {
            var mobileNumber = document.getElementById("edit_mobile_number").value;
            if (mobileNumber.startsWith('+63')) {
                mobileNumber = '+63' + mobileNumber.slice(3).replace(/\D/g, '').slice(0, 9); // Ensure 9 digits after +63
            } else {
                mobileNumber = '+63' + mobileNumber.replace(/\D/g, '').slice(0, 9); // Add +63 and restrict to 9 digits
            }
            document.getElementById("edit_mobile_number").value = mobileNumber;
        }

    </script> -->

</body>

</html>