<?php
session_start();

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'college');
define('DB_PASSWORD', 'vinu123');
define('DB_NAME', 'complaint');

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if complaintId, status, and note are set
if(isset($_GET['complaintId']) && isset($_GET['status']) && isset($_GET['note'])) {
    $complaintId = $_GET['complaintId'];
    $status = $_GET['status'];
    $note = $_GET['note'];

    // Prepare update query
    $updateQuery = "UPDATE complaints SET status = ?, note = ? WHERE complaint_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $status, $note, $complaintId);

    // Execute update query
    if ($stmt->execute()) {
        // Success message
        echo "<script>alert('Status updated successfully.');</script>";
    } else {
        // Error message
        echo "<script>alert('Error updating status: " . $stmt->error . "');</script>";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
