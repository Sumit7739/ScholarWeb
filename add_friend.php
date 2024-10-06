<?php
session_start();
include("config.php");

// Assuming the current user is stored in session
$current_user_id = $_SESSION['user_id'];

// Check if friend_id is provided
if (isset($_POST['friend_id'])) {
    $friend_id = intval($_POST['friend_id']);

    // Check if the users are already friends
    $check_friends_sql = "SELECT * FROM friendships WHERE (user_id = $current_user_id AND friend_id = $friend_id) OR (user_id = $friend_id AND friend_id = $current_user_id)";
    $check_friends_result = $conn->query($check_friends_sql);

    if ($check_friends_result->num_rows > 0) {
        // They are already friends or a request is pending
        echo json_encode(['status' => 'error', 'message' => 'You are already friends or have a pending request.']);
        exit();
    }

    // Add friend request to the database
    $insert_friend_sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES ($current_user_id, $friend_id, 'pending')";

    if ($conn->query($insert_friend_sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Friend request sent successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error sending friend request.']);
    }
} else {
    // No friend ID provided
    echo json_encode(['status' => 'error', 'message' => 'No friend ID provided.']);
}

$conn->close();
