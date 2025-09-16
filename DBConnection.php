<?php
session_start();

$connection = new mysqli("localhost", "root", "", "bawasla 2.0");


// $connection = new mysqli("192.168.169.5", "host_pc", "", "bawasla 2.0");

// Check if the connection was successful
if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}
?>