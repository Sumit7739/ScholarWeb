<?php
include 'db.php';
session_start();

if (isset($_POST['reply_content']) && isset($_POST['post_id'])) {
    $reply_content = $_POST['reply_content'];
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id']; // Assuming user is logged in

    // Insert the reply
    $insert_reply = "INSERT INTO replies (post_id, user_id, reply_content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insert_reply);
    $stmt->execute([$post_id, $user_id, $reply_content]);

    header('Location: feed.php'); // Redirect back to the feed page
}
