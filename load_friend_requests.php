<?php
session_start();
include("config.php");

$current_user_id = $_SESSION['user_id'];

$sql = "SELECT f.id, u.name FROM friendships f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.friend_id = $current_user_id AND f.status = 'pending'";

$result = $conn->query($sql);

$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = [
        'id' => $row['id'],
        'name' => $row['name']
    ];
}

echo json_encode(['requests' => $requests]);

$conn->close();
?>
