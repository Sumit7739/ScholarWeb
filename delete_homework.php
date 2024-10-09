<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access";
    exit;
}

// Include your database connection
include 'config.php';

// Get the homework ID from the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homework_id'])) {
    $homework_id = intval($_POST['homework_id']);
    $user_id = $_SESSION['user_id'];

    // Prepare the delete statement based on the unique ID (primary key)
    $sql = "DELETE FROM user_homework WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $homework_id, $user_id);

    // Execute the statement and check if successful
    if ($stmt->execute()) {
        echo "Homework deleted successfully!";
    } else {
        echo "Error deleting homework: " . $conn->error;
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
