<?php
session_start();
include("config.php");
include("encryption.php"); // Include the encryption functions

$current_user_id = $_SESSION['user_id'];
$friend_id = intval($_GET['friend_id']); // Get friend ID from query string

$encrypted_message = encryptMessage($message, $encryption_key);

$sql = "SELECT sender_id, message FROM chats WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $friend_id, $friend_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // Decrypt the message
    $decrypted_message = decryptMessage($row['message'], $encryption_key);

    $messages[] = [
        'sender_id' => $row['sender_id'],
        'message' => $decrypted_message,
    ];
}

echo json_encode($messages);

$stmt->close();
$conn->close();
