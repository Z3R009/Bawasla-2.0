<?php
include 'DBConnection.php';

if (isset($_GET['member_id']) && isset($_GET['reading_id'])) {
  $member_id = mysqli_real_escape_string($connection, $_GET['member_id']);
  $reading_id = mysqli_real_escape_string($connection, $_GET['reading_id']);

  // SQL query to join members, meter_reading, and arrears tables
  $sql = "SELECT 
            CONCAT(m.last_name, ', ', m.first_name) AS fullname, 
            m.address,
            r.billing_month,
            r.due_date,
            r.disconnection_date,
            r.reading_date,
            r.previous_reading,
            r.current_reading,
            r.total_usage,
            r.current_charges,
            r.arrears_amount,
            r.total_amount_due
          FROM members m
          JOIN meter_reading r ON m.member_id = r.member_id
          WHERE m.member_id = '$member_id' AND r.reading_id = '$reading_id'";

  // Execute the query
  $result = mysqli_query($connection, $sql);

  // Check for SQL query errors
  if (!$result) {
    echo json_encode(['error' => 'SQL Query Failed: ' . mysqli_error($connection)]);
    exit();
  }

  // Check if the result contains data
  if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    // Return data as a JSON object
    echo json_encode($data);
  } else {
    echo json_encode(['error' => 'No data found']);
  }
}
?>