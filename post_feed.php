<?php
session_start();
include 'config.php'; // Database connection

// Create a new MySQLi connection
// $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['post_content'])) {
        $post_content = $_POST['post_content'];
        $user_id = $_SESSION['user_id']; // Assuming user is logged in

        // Prepare the INSERT statement
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $post_content);

        if ($stmt->execute()) {
            header("Location: feed.php"); // Redirect back to the feed
            exit;
        } else {
            echo "Error posting your doubt: " . $conn->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>