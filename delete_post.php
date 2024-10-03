<?php
session_start();
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

require 'config.php'; // Include database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the user is the owner of the post
    $query = "SELECT user_id FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);

    if ($post && $post['user_id'] == $user_id) {
        // Delete the post
        $delete_query = "DELETE FROM posts WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt_delete, 'i', $post_id);
        mysqli_stmt_execute($stmt_delete);

        // Optional: Delete replies associated with the post
        $delete_replies_query = "DELETE FROM replies WHERE post_id = ?";
        $stmt_delete_replies = mysqli_prepare($conn, $delete_replies_query);
        mysqli_stmt_bind_param($stmt_delete_replies, 'i', $post_id);
        mysqli_stmt_execute($stmt_delete_replies);

        // Redirect back to the feed
        header("Location: feed.php?message=Post+deleted+successfully");
        exit;
    } else {
        // Unauthorized action
        header("Location: feed.php?error=Unauthorized");
        exit;
    }
} else {
    // Post ID not set
    header("Location: feed.php?error=Invalid+request");
    exit;
}
