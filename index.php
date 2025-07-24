<?php
include 'DBConnection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_type'] = $row['user_type'];

            // Log the login activity
            // $currentDateTime = date("Y-m-d H:i:s");
            // $action_user_id = $_SESSION['user_id'];
            // $details = "User logged in";
            // $insertLogSql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
            // $stmt = $connection->prepare($insertLogSql);

            // if ($stmt) {
            //     $stmt->bind_param("sss", $action_user_id, $_SESSION['user_id'], $details);
            //     $stmt->execute();
            //     $stmt->close();
            // } else {
            //     echo "Error inserting login log: " . $connection->error;
            // }

            // Redirect based on user type
            if ($_SESSION['user_type'] == 'President') {
                header('Location: dashboard_admin.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['user_type'] == 'Treasurer') {
                header('Location: dashboard_treasurer.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['user_type'] == 'Member') {
                header('Location: dashboard_member.php?member_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['user_type'] == 'Meter Reader') {
                header('Location: dashboard_meter_reader.php?user_id=' . $_SESSION['user_id']);
            } else {
                echo "<script>alert('Invalid user type value');</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect Username');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Link to your local Bootstrap CSS file -->
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        body {
            overflow: hidden;
        }


        .logo-container {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
        }

        .logo {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }

        .lname {
            width: 150px;
            height: auto;
        }

        .container {
            margin-top: 80px;
            /* Adjust to make space for the logo */
        }

        .login-logo {
            display: block;
            margin: 0 auto;
            /* Centers the image */
        }

        .login-title {
            text-align: center;
            margin-top: 10px;
            font-size: 1.25rem;
            font-weight: bold;
        }

        @keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeout {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body background="white">

    <div class="logo-container">
    </div>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header text-center">
                                    <!-- Centered Image -->
                                    <img src="img/lg2.png" alt="Logo" class="login-logo"
                                        style="height: 100px; width: auto; max-width: 100%;">

                                    <!-- Text below the logo -->
                                    <div class="login-title">BARANGAY WATER SYSTEM & LIVELIHOOD ASSOCIATION</div>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="username" id="username" type="text"
                                                placeholder="Input Username" autocomplete="off" required />
                                            <label for="username">Username</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" id="password" type="password"
                                                placeholder="password" autocomplete="off" required />
                                            <label for="password">Password</label>
                                        </div>
                                        <div class="form-floating">
                                            <input type="submit" class="btn btn-primary w-100 btn-lg" value="Login" />
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <!-- <div id="layoutAuthentication_footer">
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
        </div> -->
    </div>

    <!-- Conditional Cloud Tip -->
    <div id="loginTip" style="
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.4); /* Lower opacity */
    color: rgba(0, 0, 0, 0.6);             /* Softer text color */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Softer shadow */
    padding: 15px 20px;
    border-radius: 15px;
    font-size: 14px;
    z-index: 9999;
    animation: fadein 1s, fadeout 1s 5s;
">
        💡 Tip: Try typing <b>RICK</b> to discover a surprise!
    </div>



    <!-- Hidden Info Icon -->
    <div id="easterEggIcon" style="
    display: none;
    position: fixed;
    bottom: 20px;
    right: 20px;
    font-size: 32px;
    cursor: pointer;
    z-index: 9999;
">
        ℹ️
    </div>

    <!-- Modal Structure -->
    <div class="modal fade" id="easterEggModal" tabindex="-1" aria-labelledby="easterEggLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="easterEggContent">
                <!-- Content loaded dynamically via AJAX -->
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

    <script>
        let sequence = [];
        const secretCode = ['KeyR', 'KeyI', 'KeyC', 'KeyK'];

        document.addEventListener('keydown', (e) => {
            sequence.push(e.code); // use .code to match 'KeyR', etc.
            if (sequence.length > 4) sequence.shift();

            if (sequence.toString() === secretCode.toString()) {
                // Automatically show the modal and load the PHP content
                fetch('assets/flags/EE/cr3tor.php')
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('easterEggContent').innerHTML = html;
                        const modal = new bootstrap.Modal(document.getElementById('easterEggModal'));
                        modal.show();
                    });
            }
        });

    </script>

    <!-- time -->
    <script>
        // Show tip message to 4 out of every 10 users
        window.addEventListener('load', function () {
            const chance = Math.random(); // 0.0 to 1.0
            if (chance < 0.2) { // 40% chance
                const tip = document.getElementById('loginTip');
                tip.style.display = 'block';
                setTimeout(() => {
                    tip.style.display = 'none';
                }, 4000); // stays visible for 7 seconds
            }
        });
    </script>


</body>

</html>