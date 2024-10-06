<?php
session_start();
include("config.php");

$current_user_id = $_SESSION['user_id'];

if (isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);

    // Delete or update the friend request status to declined
    $sql = "DELETE FROM friendships WHERE id = $request_id AND friend_id = $current_user_id";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Friend request declined']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error declining request']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No request ID provided']);
}

$conn->close();
?>
