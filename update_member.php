<?php
include 'DBConnection.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $tank_no = $_POST['tank_no'];
    $meter_no = $_POST['meter_no'];
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];

    // Check if the username already exists (excluding the current user)
    $sql_check = "SELECT COUNT(*) as count FROM users WHERE username = ? AND user_id != ?";
    $stmt_check = $connection->prepare($sql_check);
    $stmt_check->bind_param("si", $username, $user_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "Username is already taken. Please choose a different one.";
        exit;
    }

    // Prepare the SQL query to update user details
    $sql_update = "UPDATE members SET last_name = ?, first_name = ?, middle_name = ?, tank_no = ?, meter_no = ?, address = ?, mobile_number = ? WHERE member_id = ?";

    $stmt_update = $connection->prepare($sql_update);

    // Correcting bind_param types
    $stmt_update->bind_param("sssiissi", $last_name, $first_name, $middle_name, $tank_no, $meter_no, $address, $mobile_number, $user_id);


    if ($stmt_update->execute()) {
        echo "User updated successfully!";
        header('Location: manage_members.php?user_id=' . $user_id);
        exit; // Ensure no further code is executed after the redirect
    } else {
        echo "Error: " . $stmt_update->error;
    }
}
?>