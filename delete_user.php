<?php
include 'DBConnection.php';

if (isset($_GET['user_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $user_id = intval($_GET['user_id']);

    // Prepare and execute the deletion query for 'members' table
    $deleteMemberSql = "DELETE FROM members WHERE user_id = ?";
    $stmtMember = $connection->prepare($deleteMemberSql);
    $stmtMember->bind_param("i", $user_id);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM users WHERE user_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $user_id);

    // Execute both deletion queries
    if ($stmtMember->execute() && $stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: manage_users.php');
        exit();
    } else {
        // Handle error if either query fails
        echo "Error deleting user: " . $connection->error;
    }
} else {
    // Handle invalid request
    echo "Invalid request.";
}
?>