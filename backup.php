<?php
// Database connection settings
$host = "localhost";
$user = "root"; // change if needed
$pass = "";     // change if needed
$dbname = "bawasla 2.0"; // exact database name in XAMPP

// Connect to database
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

// Folder to save backups
$backupFolder = __DIR__ . "/backup/";
if (!is_dir($backupFolder)) {
	mkdir($backupFolder, 0777, true);
}

// File name
$date = date("m-d-Y");
$backupFile = $backupFolder . "bawasla_" . $date . ".sql";

// Open file
$fp = fopen($backupFile, "w");
if (!$fp) {
	die("Cannot write to file: $backupFile");
}

// Write header
fwrite($fp, "-- Backup for $dbname on $date\n\n");

// Get all tables
$tables = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
	$tables[] = $row[0];
}

foreach ($tables as $table) {
	// Drop table statement
	fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");

	// Create table statement
	$res = $conn->query("SHOW CREATE TABLE `$table`");
	$row = $res->fetch_assoc();
	fwrite($fp, $row['Create Table'] . ";\n\n");

	// Insert data
	$res = $conn->query("SELECT * FROM `$table`");
	while ($row = $res->fetch_assoc()) {
		$vals = array_map(function ($val) use ($conn) {
			if ($val === null)
				return "NULL";
			return "'" . $conn->real_escape_string($val) . "'";
		}, array_values($row));
		$vals = implode(", ", $vals);
		fwrite($fp, "INSERT INTO `$table` VALUES ($vals);\n");
	}
	fwrite($fp, "\n\n");
}

fclose($fp);
$conn->close();

// Redirect back with success
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";
header("Location: $redirect?backup=success");
exit;
?>