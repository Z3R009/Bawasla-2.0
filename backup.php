<?php
// Database connection settings
$host = "localhost";
$user = "root"; // change this if needed
$pass = "";     // change this if needed
$dbname = "bawasla 2.0"; // exact database name in XAMPP

// Folder to save backups
$backupFolder = __DIR__ . "/backup/";

// Create folder if not exists
if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0777, true);
}

// File name format (bawasla_MM-DD-YYYY.sql)
$date = date("m-d-Y");
$backupFile = $backupFolder . "bawasla_" . $date . ".sql";

// Escape db name safely (double quotes protect space and dot)
$escapedDbName = "\"$dbname\"";

// Run mysqldump command
$command = "mysqldump -h \"$host\" -u \"$user\" " . ($pass ? "-p\"$pass\" " : "") . $escapedDbName . " > \"$backupFile\"";
exec($command, $output, $result);

// Check if backup was successful
if ($result === 0 && file_exists($backupFile)) {
    $msg = "success";
} else {
    $msg = "fail";
}

// Redirect back with status
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";
header("Location: $redirect?backup=$msg");
exit;
?>