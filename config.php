<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'college');
define('DB_PASSWORD', 'vinu123');
define('DB_NAME', 'complaint');

// Attempt to establish a connection to the database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
