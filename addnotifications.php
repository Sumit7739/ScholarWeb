<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Database connection
include 'config.php'; // Include your database connection file

// Function to log actions
function logAction($conn, $action)
{
    $admin_id = $_SESSION['admin_id']; // Assuming the admin ID is stored in the session
    $activity_description = "Admin $action.";
    $query_log = "INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', '$activity_description', NOW(), 'action')";

    if (!mysqli_query($conn, $query_log)) {
        echo "Error logging action: " . mysqli_error($conn);
    }
}

// Handle adding a new notification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_notification'])) {
    $notification_text = htmlspecialchars($_POST['notification_text']);

    $query_add = "INSERT INTO notifications (notification_text) VALUES ('$notification_text')";

    if (mysqli_query($conn, $query_add)) {
        logAction($conn, "added notification: $notification_text");
        header("Location: addnotifications.php");
        exit;
    } else {
        echo "Error adding notification: " . mysqli_error($conn);
    }
}

// Handle marking a notification as read
if (isset($_GET['mark_read'])) {
    $notification_id = intval($_GET['mark_read']);

    $query_update = "UPDATE notifications SET is_read = 1 WHERE id = $notification_id";

    if (mysqli_query($conn, $query_update)) {
        logAction($conn, "marked notification ID $notification_id as read");
        header("Location: addnotifications.php");
        exit;
    } else {
        echo "Error marking notification as read: " . mysqli_error($conn);
    }
}

// Handle deleting a notification
if (isset($_GET['delete'])) {
    $notification_id = intval($_GET['delete']);

    $query_delete = "DELETE FROM notifications WHERE id = $notification_id";

    if (mysqli_query($conn, $query_delete)) {
        logAction($conn, "deleted notification ID $notification_id");
        header("Location: addnotifications.php");
        exit;
    } else {
        echo "Error deleting notification: " . mysqli_error($conn);
    }
}

// Fetch all notifications
$query_notifications = "SELECT * FROM notifications ORDER BY created_at DESC";
$result_notifications = mysqli_query($conn, $query_notifications);

if (!$result_notifications) {
    die("Error fetching notifications: " . mysqli_error($conn));
}

$notifications = mysqli_fetch_all($result_notifications, MYSQLI_ASSOC);

// Fetch logs
$query_logs = "SELECT * FROM activity ORDER BY date DESC";
$result_logs = mysqli_query($conn, $query_logs);

if (!$result_logs) {
    die("Error fetching logs: " . mysqli_error($conn));
}

$logs = mysqli_fetch_all($result_logs, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }

        .notifications {
            width: 95%;
            /* max-width: 800px; */
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .notifications h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .notifications form {
            margin-bottom: 20px;
        }

        .notifications label {
            display: block;
            margin-bottom: 30px;
            font-weight: bold;
            color: #333;
        }

        .notifications textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            resize: vertical;
            background-color: #fafafa;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .notifications button {
            background-color: #4285f4;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .notifications button:hover {
            background-color: #357ae8;
        }

        .notifications ul {
            list-style: none;
            padding: 0;
        }

        .notifications li {
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff9db;
            /* Yellow background for notifications */
            border: 1px solid #f7d60c;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
        }

        .notifications li span {
            font-size: 12px;
            color: #666;
            margin-left: 10px;
        }

        .notifications li a {
            color: #4285f4;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            background-color: #f1f1f1;
        }

        .notifications li a:hover {
            background-color: #e0e0e0;
            color: #333;
        }

        /* Header */
        header {
            background-color: #333;
            color: white;
            padding: 15px 0;
            text-align: center;
        }

        header .logo {
            font-size: 28px;
            font-weight: bold;
        }

        /* Footer */
        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <div class="logo">Admin Panel</div>
        </nav>
    </header>

    <section class="notifications">
        <h2>Manage Notifications</h2>

        <form method="POST">
            <label for="notification_text">New Notification:</label>
            <textarea id="notification_text" name="notification_text" required></textarea>
            <button type="submit" name="add_notification">Add Notification</button>
        </form>

        <h3>Existing Notifications</h3>
        <ul>
            <?php if (empty($notifications)): ?>
                <li>No notifications found.</li>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <?php echo htmlspecialchars($notification['notification_text']); ?>
                        <span>(Created at: <?php echo htmlspecialchars($notification['created_at']); ?>)</span>
                        <span><?php echo $notification['is_read'] ? "Read" : "Unread"; ?></span>
                        <a href="?mark_read=<?php echo $notification['id']; ?>">Mark as Read</a>
                        <a href="?delete=<?php echo $notification['id']; ?>">Delete</a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </section>

    <footer>
        <p>&copy; 2024 ScholarWeb. All Rights Reserved.</p>
    </footer>

</body>

</html>