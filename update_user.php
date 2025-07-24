<?php
include 'DBConnection.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $user_type = $_POST['user_type'];

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

    // Prepare the SQL query to update only username and user_type
    $sql_update = "UPDATE users SET username = ?, user_type = ? WHERE user_id = ?";
    $stmt_update = $connection->prepare($sql_update);
    $stmt_update->bind_param("ssi", $username, $user_type, $user_id);

    if ($stmt_update->execute()) {
        echo "User updated successfully!";
        header('Location: manage_users.php?user_id=' . $user_id);
    } else {
        echo "Error: " . $stmt_update->error;
    }
}
?>