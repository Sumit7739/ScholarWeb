<?php
include 'config.php';
session_start();

if (isset($_POST['reply_content']) && isset($_POST['post_id'])) {
    $reply_content = $_POST['reply_content'];
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id']; // Assuming user is logged in

    // Insert the reply
    $insert_reply = "INSERT INTO replies (post_id, user_id, reply_content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_reply);

    // Bind parameters and execute
    $stmt->bind_param("iis", $post_id, $user_id, $reply_content); // "iis" means integer, integer, string
    $stmt->execute();

    // Check if the reply was inserted successfully
    if ($stmt->affected_rows > 0) {
        // Redirect back to the feed page if successful
        header('Location: feed.php');
        exit; // It's a good practice to call exit after header redirect
    } else {
        // Handle error case (optional)
        echo "Error inserting reply: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection if necessary (optional, can be done at the end of the script)
$conn->close();
