<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}


require 'config.php';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the reply form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $contact_message_id = $_POST['contact_message_id'];
    $admin_id = 1; // Set the admin ID here, or fetch from session
    $reply = $_POST['reply'];

    // Insert reply into admin_replies table
    $sql = "INSERT INTO admin_replies (contact_message_id, admin_id, reply) 
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $contact_message_id, $admin_id, $reply);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Reply sent successfully!</div>";
        header("Location: admin_messages.php");
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Fetch contact messages
$sql = "SELECT cm.id, cm.user_id, cm.message, cm.created_at, u.name AS user_name 
        FROM contact_messages cm 
        JOIN users u ON cm.user_id = u.id";  // Assuming you have a users table
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Messages</title>
    <link rel="stylesheet" href="dashstyles.css">
    <style>
        /* Base Styles */
        body {
            background-color: #f8f9fa;
            /* Light background color */
            font-family: Arial, sans-serif;
            /* Font style */
            color: #333;
            /* Dark text color */
        }

        .container {
            width: 95%;
            /* max-width: 800px; */
            /* Limit the width of the container */
            margin: auto;
            /* Center the container */
            padding: 50px;
            /* Add some padding */
        }

        /* Heading Styles */
        h2 {
            text-align: center;
            /* Center the heading */
            margin-bottom: 40px;
            /* Space below the heading */
            color: #007bff;
            padding: 20px
                /* Primary color for heading */
        }

        /* Card Styles */
        .card {
            border: 1px solid #ced4da;
            /* Light border around cards */
            border-radius: 0.5rem;
            /* Rounded corners */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            /* Subtle shadow for depth */
            margin-bottom: 20px;
            /* Space between cards */
            background-color: #ffffff;
            /* White background for cards */
            padding: 20px;
        }

        .card-header {
            background-color: #e9ecef;
            /* Light gray background for headers */
            font-weight: bold;
            /* Bold text */
            font-size: 1.1rem;
            padding: 10px;
            /* Slightly larger font size */
        }

        .card-body {
            padding: 15px;
            /* Padding inside the card body */
        }

        .card-body p {
            font-size: 16px;
            font-weight: bold;
            margin: 10px;
            /* Remove default margin for paragraphs */
        }

        /* Alert Styles */
        .alert {
            margin-top: 20px;
            /* Space above alerts */
        }

        /* Form Styles */
        .form-group {
            margin-top: 15px;
            /* Space above form groups */
        }

        textarea {
            padding: 5px 15px;
            border: 1px solid #ccc;
            resize: none;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size:16px;
            /* Prevent resizing of the textarea */
        }

        /* Button Styles */
        .btn {
            background-color: #007bff;
            /* Primary button color */
            color: white;
            /* White text */
            border: none;
            /* Remove border */
            border-radius: 0.3rem;
            /* Rounded corners */
            transition: background-color 0.3s;
            padding: 10px 15px;
            /* Transition effect */
        }

        .btn:hover {
            background-color: #0056b3;
            /* Darker shade on hover */
        }

        /* Responsive Styles */
        @media (max-width: 576px) {
            .container {
                padding: 10px;
                /* Less padding on smaller screens */
            }

            h2 {
                font-size: 1.5rem;
                /* Smaller heading on mobile */
            }
        }
    </style>
</head>

<body>

<header>
    <nav>
        <ul>
            <li><a href="admin.php">Go back to admin panel</a></li>
        </ul>
    </nav>
</header>
    <div class="container mt-5">
        <h2>User Messages</h2>

        <?php
        // Fetch messages from the contact_messages table
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card mb-3'>";
                echo "<div class='card-header'>Message from: " . htmlspecialchars($row['user_name']) . "</div>";
                echo "<div class='card-body'>";
                echo "<p>" . htmlspecialchars($row['message']) . "</p>";
                echo "<p class='text-muted'>Received on: " . htmlspecialchars($row['created_at']) . "</p>";

                // Reply form
                echo '<form method="POST" action="">
                <input type="hidden" name="contact_message_id" value="' . $row['id'] . '">
                <div class="form-group">
                    <textarea name="reply" class="form-control" required placeholder="Your reply..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Reply</button>
              </form>';

                // Fetch replies for this message
                $contact_message_id = $row['id'];
                $reply_query = "SELECT * FROM admin_replies WHERE contact_message_id = ?";
                $stmt = $conn->prepare($reply_query);
                $stmt->bind_param("i", $contact_message_id);
                $stmt->execute();
                $replies_result = $stmt->get_result();

                // Display replies
                if ($replies_result->num_rows > 0) {
                    echo "<h5>Replies:</h5>";
                    while ($reply = $replies_result->fetch_assoc()) {
                        echo "<div class='alert alert-info'>";
                        echo "<strong>Admin:</strong> " . htmlspecialchars($reply['reply']) . "<br>";
                        echo "<small class='text-muted'>Replied on: " . htmlspecialchars($reply['created_at']) . "</small>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='alert alert-secondary'>No replies yet.</div>";
                }

                echo "</div></div>";
            }
        } else {
            echo "<div class='alert alert-warning'>No messages found.</div>";
        }
        ?>
    </div>


</body>

</html>

<?php
$conn->close();
?>