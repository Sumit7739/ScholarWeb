<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['post_content'])) {
        $post_content = $_POST['post_content'];
        $user_id = $_SESSION['user_id']; // Assuming user is logged in

        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (:user_id, :post_content)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':post_content', $post_content);

        if ($stmt->execute()) {
            header("Location: feed.php"); // Redirect back to the feed
            exit;
        } else {
            echo "Error posting your doubt!";
        }
    }
}
?>
