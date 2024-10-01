<?php
include 'config.php';
session_start();

// Create a new MySQLi connection
// $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    // Increment the like count
    $update_likes = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?";
    $stmt = $conn->prepare($update_likes);
    $stmt->bind_param("i", $post_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>