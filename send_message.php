<?php

// Add error logging at the top of your PHP file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("config.php");
include("encryption.php"); // Include your encryption functions

$current_user_id = $_SESSION['user_id'];
$friend_id = intval($_POST['friend_id']); // Get friend ID from POST request

$message = isset($_POST['message']) ? $_POST['message'] : '';
$encrypted_message = encryptMessage($message, $encryption_key); // Encrypt the message

$image_path = null;

// Check if an image is uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "uploads/chat_images/"; // Specify your upload directory
    $image_name = basename($_FILES['image']['name']);
    $target_file = $target_dir . $image_name;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = $image_name; // Store the image name for database insertion
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error uploading image.']);
        exit();
    }
}

// Insert message into database
$sql = "INSERT INTO chats (sender_id, receiver_id, message, sent_at, image) VALUES (?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $current_user_id, $friend_id, $encrypted_message, $image_path);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error sending message.']);
}

$stmt->close();
$conn->close();
