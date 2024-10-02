<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page
ini_set('display_errors', 1);
session_start();
include 'config.php'; // Database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['post_content'])) {
        $post_content = $_POST['post_content'];
        $user_id = $_SESSION['user_id']; // Assuming user is logged in
        $post_image = ""; // Initialize empty string for image path

        // Check if an image was uploaded
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
            $image_name = $_FILES['post_image']['name'];
            $image_tmp = $_FILES['post_image']['tmp_name'];
            $upload_dir = 'uploads/'; // Ensure this directory exists and is writable

            // Create the uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Move the uploaded file to the uploads directory
            $target_file = $upload_dir . basename($image_name);
            if (move_uploaded_file($image_tmp, $target_file)) {
                $post_image = $target_file; // Store image path to save in the database
            } else {
                echo "Error uploading the image.";
            }
        }

        // Prepare the INSERT statement with image
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $post_content, $post_image);

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
