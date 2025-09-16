<?php
include 'DBConnection.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $tank_no = $_POST['tank_no'];

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
    $sql_update = "UPDATE members SET last_name = ?, first_name = ?, middle_name = ?, gender = ?, address = ?, tank_no = ? WHERE member_id = ?";

    $stmt_update = $connection->prepare($sql_update);

    // Correcting bind_param types
    $stmt_update->bind_param("sssssii", $last_name, $first_name, $middle_name, $gender, $address, $tank_no, $user_id);


    if ($stmt_update->execute()) {
        echo "User updated successfully!";
        header('Location: manage_members.php?user_id=' . $user_id);
        exit; // Ensure no further code is executed after the redirect
    } else {
        echo "Error: " . $stmt_update->error;
    }
}
?>