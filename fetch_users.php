<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include("config.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['search'])) {
    $search = $conn->real_escape_string($_POST['search']);

    // Adjust the SQL query to also select the user ID
    $sql = "SELECT id, name FROM users WHERE name LIKE '%$search%' LIMIT 5";
    $result = $conn->query($sql);

    $users = []; // Create an array to hold user data

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Push user data into the array
            $users[] = [
                'id' => $row['id'], // Include user ID
                'name' => $row['name']
            ];
        }
    }

    // Return the users array as JSON
    echo json_encode($users);
}

$conn->close();
