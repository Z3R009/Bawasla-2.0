<?php
include 'DBConnection.php';

$member_id = $_SESSION['member_id']; // Assuming the user ID is stored in the session
$password = mysqli_real_escape_string($connection, $_POST['password']);

$sql = "SELECT password FROM members WHERE member_id = '$member_id'";
$result = mysqli_query($connection, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if (password_verify($password, $row['password'])) {
        echo 'true';
    } else {
        echo 'false';
    }
} else {
    echo 'false';
}
?>