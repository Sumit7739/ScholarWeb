<?php
// Database connection details
$host = 'localhost'; // Change if your database host is different
$username = 'root'; // Your database username
$password = ''; // Your database password
$database = 'scholarweb'; // Your database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

//secret key for encryption
$encryption_key = 'c3A@5gT1!kL7mZp8#Q3rB9fS0vD2jJ6x';
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
