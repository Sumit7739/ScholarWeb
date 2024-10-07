<?php
session_start();
include("config.php");

$current_user_id = $_SESSION['user_id'];

// Fetch unread messages for the current user
$sql = "SELECT chats.id, chats.sender_id, chats.message, users.name AS sender_name 
        FROM chats 
        JOIN users ON chats.sender_id = users.id
        WHERE chats.receiver_id = ? AND chats.is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender_id' => $row['sender_id'],
        'sender_name' => $row['sender_name']
    ];
}

echo json_encode(['messages' => $messages]);

$stmt->close();
$conn->close();
?>
