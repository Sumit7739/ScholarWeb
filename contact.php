<?php
// Start session to get the logged-in user's data
session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Include database connection (assumed you have db.php for connection)
include 'config.php';

$user_id = $_SESSION['user_id'];
$messageStatus = ""; // Variable to store success or error message

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];

    // Insert the message into the database
    $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $message);

    if ($stmt->execute()) {
        // Success message
        $messageStatus = "<div class='message-success'>Message sent successfully!</div>";
        header("Location: contact.php");
    } else {
        // Error message
        $messageStatus = "<div class='message-error'>Error sending message. Please try again.</div>";
    }
    $stmt->close();
}

// Fetch messages and their replies for the logged-in user
$messagesQuery = "
    SELECT cm.id AS message_id, cm.message, cm.created_at AS message_created_at, 
           ar.reply, ar.created_at AS reply_created_at 
    FROM contact_messages cm
    LEFT JOIN admin_replies ar ON cm.id = ar.contact_message_id
    WHERE cm.user_id = ?
    ORDER BY cm.created_at DESC";

$stmt = $conn->prepare($messagesQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$messagesResult = $stmt->get_result();

// Create an array to group replies by message_id
$messages = [];
while ($row = $messagesResult->fetch_assoc()) {
    $message_id = $row['message_id'];
    if (!isset($messages[$message_id])) {
        $messages[$message_id] = [
            'message' => $row['message'],
            'created_at' => $row['message_created_at'],
            'replies' => []
        ];
    }

    if (!empty($row['reply'])) {
        $messages[$message_id]['replies'][] = [
            'reply' => $row['reply'],
            'reply_created_at' => $row['reply_created_at']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .contact-container {
            width: 95%;
            /* max-width: 600px; */
            margin: auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .contact-form label {
            display: block;
            font-weight: bold;
            margin: 10px 0 5px;
            color: #333;
        }

        .contact-form textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .contact-form button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 30px;
            transition: background-color 0.3s;
        }

        .contact-form button:hover {
            background-color: #218838;
        }

        .message-card {
            margin-top: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .message-content {
            margin-bottom: 10px;
            color: #333;
        }

        .message-timestamp {
            color: #888;
            font-size: 12px;
        }

        .admin-reply {
            background-color: #f1f1f1;
            border-left: 4px solid #28a745;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
        }

        .btn-back {
            margin-top: 30px;
            /* position: fixed; */
            bottom: 30px;
            left: 40%;
            right: 35%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .btn-back a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        .message-success,
        .message-error {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        p{
            margin-top: 10px;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="contact-container">
        <h2>Contact Admin</h2>

        <!-- Display success or error message if set -->
        <?php echo $messageStatus; ?>

        <div class="contact-form">
            <form action="" method="POST">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Write your message here..." rows="6" required></textarea>

                <button type="submit">Send Message</button>
            </form>
        </div>

        <!-- Display Sent Messages and Replies -->
        <h3>Your Sent Messages</h3>
        <?php
        if (!empty($messages)) {
            foreach ($messages as $message_id => $message_data) {
                echo "<div class='message-card'>";
                echo "<p class='message-content'>" . htmlspecialchars($message_data['message']) . "</p>";
                echo "<p class='message-timestamp'>Sent on: " . htmlspecialchars($message_data['created_at']) . "</p>";

                if (!empty($message_data['replies'])) {
                    echo "<div class='admin-reply'>";
                    echo "<strong>Admin Replies:</strong>";
                    foreach ($message_data['replies'] as $reply) {
                        echo "<div class='reply-item'>";
                        echo "<p>" . htmlspecialchars($reply['reply']) . "</p>";
                        echo "<p class='message-timestamp'>Replied on: " . htmlspecialchars($reply['reply_created_at']) . "</p>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p class='message-timestamp'>No reply yet.</p>";
                }

                echo "</div>";
            }
        } else {
            echo "<p>No messages sent yet.</p>";
        }
        ?>

        <button class="btn-back"><a href="profile.php">Go back</a></button>
    </div>
</body>

</html>