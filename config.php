<?php
// Database connection details
$host = 'localhost'; // Change if your database host is different
$username = 'root'; // Your database username
$password = ''; // Your database password
$database = 'scholarweb'; // Your database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
