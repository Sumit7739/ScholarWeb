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
    } else {
        // Error message
        $messageStatus = "<div class='message-error'>Error sending message. Please try again.</div>";
    }
    $stmt->close();
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
            max-width: 600px;
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
            border-radius: 4px;
            font-size: 16px;
            resize: vertical;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        }

        .contact-form button:hover {
            background-color: #218838;
        }

        .btn-back {
            position: fixed;
            bottom: 30px;
            left: 40%;
            right: 35%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .btn-back a{
            text-decoration: none;
            color:#333;
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
    </div>

    <button class="btn-back""><a href="profile.php">Go back</a></button>
</body>

</html>