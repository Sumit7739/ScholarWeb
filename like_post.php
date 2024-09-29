<?php
include 'db.php';
session_start();

if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    // Increment the like count
    $update_likes = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?";
    $stmt = $pdo->prepare($update_likes);
    $stmt->execute([$post_id]);

    echo "success";
}
?>
