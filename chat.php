<?php
session_start();
include("config.php");

$current_user_id = $_SESSION['user_id'];

// Get the friend ID from the query string
if (isset($_GET['friend_id'])) {
    $friend_id = intval($_GET['friend_id']);

    // Fetch friend details
    $friend_sql = "SELECT id, name, profile_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($friend_sql);
    $stmt->bind_param("i", $friend_id);
    $stmt->execute();
    $friend_result = $stmt->get_result();
    $friend = $friend_result->fetch_assoc();

    if (!$friend) {
        die("Friend not found.");
    }

    // Mark all unread messages from this friend as read
    $update_sql = "UPDATE chats 
                   SET is_read = 1 
                   WHERE sender_id = ? 
                   AND receiver_id = ? 
                   AND is_read = 0";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $friend_id, $current_user_id);
    $stmt->execute();
    $stmt->close();
} else {
    die("No friend selected.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat with <?php echo $friend['name']; ?></title>
        <link rel="stylesheet" href="dashstyles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: 'Arial', sans-serif;
                background-color: #333;
            }

            .chat-container {
                display: flex;
                flex-direction: column;
                padding: 10px;
                height: 90vh;
                margin: 0 auto;
                background-color: #000;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            .friend-info {
                display: flex;
                align-items: center;
                justify-content: space-between;
                /* This ensures items are spaced out */
                /* margin-bottom: 10px; */
                padding: 15px;
                border-radius: 10px;
                background-color: #fff;
                color: white;
                position: sticky;
                top: 0;
                /* z-index: 1000; */
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            .back-arrow {
                color: #3b5998;
                /* Change this to the color you want for the arrow */
                text-decoration: none;
                /* Remove underline from link */
                font-size: 20px;
                /* Adjust size as needed */
            }

            .back-arrow:hover {
                color: #334d84;
                /* Change color on hover */
            }


            .friend-info img {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                margin-right: 10px;
            }

            .friend-info h3 {
                margin: 0;
                font-size: 18px;
                color: #333;
            }

            .msg {
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 10px;
                margin-top: 5px;
                margin-bottom: 5px;
                background-color: #4bb543;
                animation: showAndHide 5s forwards;
            }

            .msg p {
                align-items: center;
                text-align: center;
                color: #fff;
                font-size: 12px;
                font-weight: bold;
            }

            @keyframes showAndHide {
                0% {
                    opacity: 0;
                }

                50% {
                    opacity: 0.5;
                }

                100% {
                    opacity: 1;
                }
            }

            .chat-container {
                display: flex;
                flex-direction: column;
                padding: 10px;
                height: 90vh;
                /* This looks fine */
                margin: 0 auto;
                background-color: #000;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            /* Force scrollbar to be visible */
            .chat-box {
                flex-grow: 1;
                padding: 20px;
                background-color: #ddd;
                overflow-y: scroll;
                scroll-behavior: smooth;
                display: flex;
                flex-direction: column;
                border-radius: 10px 10px 0 0;
                gap: 10px;
                /* Space between messages */
                height: calc(100vh - 150px);
                /* Explicit height for web view compatibility */
                -webkit-overflow-scrolling: touch;
                /* Smooth scrolling for mobile */
                scrollbar-width: thin;
                /* For Firefox */
                scrollbar-color: #888 #ddd;
                /* For Firefox: custom scrollbar color */
            }

            /* Custom scrollbar for Webkit browsers (e.g., Chrome, Safari) */
            .chat-box::-webkit-scrollbar {
                width: 8px;
                /* Width of the scrollbar */
            }

            .chat-box::-webkit-scrollbar-thumb {
                background-color: #ffff02;
                /* Scrollbar color */
                border-radius: 10px;
            }

            .chat-box::-webkit-scrollbar-track {
                background-color: #ddd;
                /* Scrollbar background */
            }

            .chat-message.sent {
                background-color: #3b5998;
                color: white;
                margin-left: auto;
                /* Align to the right */
            }

            .chat-message.received {
                background-color: #f1f0f0;
                color: #333;
                margin-right: auto;
                /* Align to the left */
            }

            .message-date {
                font-size: 12px;
                color: #888;
                margin-top: 5px;
                text-align: right;
            }

            .message-input-container {
                display: flex;
                padding: 10px;
                background-color: #fff;
                border-top: 1px solid #ddd;
                border-radius: 0 0 10px 10px;
            }

            .camera-icon {
                cursor: pointer;
                margin-right: 10px;
                /* Space between camera icon and message input */
                color: #3b5998;
                /* Color of the camera icon */
                font-size: 24px;
                /* Size of the camera icon */
            }

            .message-input {
                flex-grow: 1;
                padding: 10px;
                border-radius: 20px;
                border: 1px solid #ddd;
                outline: none;
            }

            .send-button {
                padding: 10px 20px;
                background-color: #3b5998;
                color: white;
                border: none;
                border-radius: 20px;
                margin-left: 10px;
                cursor: pointer;
            }

            .send-button:hover {
                background-color: #334d84;
            }

            /* Responsive design */
            @media screen and (max-width: 768px) {
                .chat-container {
                    max-width: 100%;
                    padding: 10px;
                }

                .friend-info h3 {
                    font-size: 20px;
                }

                .message-input {
                    padding: 10px;
                }

                .send-button {
                    padding: 10px 15px;
                }
            }
        </style>
    </head>

<body>
    <header>
        <nav>
            <div class="logo">ScholarWeb</div>
            <div class="hamburger" id="hamburger">
                <i class="fa fa-bars"></i> <!-- Hamburger Icon -->
            </div>
            <ul class="menu" id="menu">
                <li><a href="profile.php"><i class="fa fa-user"></i>&nbsp; Profile</a></li>
                <li><a href="friends_list.php"><i class="fa fa-users" id="active"></i>&nbsp; Friends</a></li>
                <li><a href="feed.php"><i class="fa-solid fa-newspaper"></i>&nbsp; Feed</a></li>
                <li><a href="all_tasks.php"><i class="fa fa-tasks"></i>&nbsp; Tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; Homework</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp;Progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; Activities</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; Notifications</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="chat-container">
        <div class="friend-info">
            <img src="uploads/profile_pics/<?php echo $friend['profile_pic'] ?: 'default.png'; ?>" alt="Profile Picture">
            <h3><?php echo $friend['name']; ?></h3>
            <a href="friends_list.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
        </div>

        <div class="msg">
            <p>The messages are end to end encrypted</p>
        </div>
        <div class="chat-box" id="chat-box">

            <!-- Chat messages will be dynamically loaded here -->
        </div>

        <div class="message-input-container">
            <input type="text" id="message" class="message-input" placeholder="Type your message...">
            <button class="send-button" onclick="sendMessage()">Send</button>
        </div>
    </div>
    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const friendId = <?php echo $friend['id']; ?>; // Get friend ID from PHP

            function fetchMessages() {
                $.ajax({
                    url: 'fetch_messages.php',
                    method: 'GET',
                    data: {
                        friend_id: friendId
                    },
                    success: function(response) {
                        const messages = JSON.parse(response);
                        $('#chat-box').empty(); // Clear the chat box

                        messages.forEach(function(msg) {
                            const messageBubble = $('<div></div>')
                                .addClass('chat-message')
                                .addClass(msg.sender_id === <?php echo $current_user_id; ?> ? 'sent' : 'received')
                                .text(msg.message);

                            // Append message only (removed date)
                            $('#chat-box').append(messageBubble);
                        });

                        scrollChatToBottom(); // Scroll to the bottom
                    }
                });
            }

            function sendMessage() {
                const message = $('#message').val();
                if (message.trim() === '') return;

                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: {
                        friend_id: friendId,
                        message: message
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.status === 'success') {
                            const messageBubble = $('<div></div>')
                                .addClass('chat-message sent')
                                .text(message);

                            // Append message only (removed date)
                            $('#chat-box').append(messageBubble);
                            $('#message').val(''); // Clear input
                        } else {
                            alert(data.message); // Handle error
                        }
                    }
                });
            }

            // Fetch messages every 2 seconds
            setInterval(fetchMessages, 2000);

            // Send message on button click
            $('button').on('click', sendMessage);
        });

        function scrollChatToBottom() {
            var chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>