<?php
session_start();

// Initialize the database connection
// $connection = new mysqli("localhost", "rtgqrctaru", "Bk4W3b3S34", "rtgqrctaru");


$connection = new mysqli("localhost", "root", "", "bawasla 2.0");

// Check if the connection was successful
if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}
?>