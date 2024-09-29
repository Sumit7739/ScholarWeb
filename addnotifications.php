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
include 'db.php'; // Include your database connection file

// Function to log actions
function logAction($pdo, $action)
{
    $admin_id = $_SESSION['admin_id']; // Assuming the admin ID is stored in the session
    $activity_description = "Admin $action.";
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', :activity_description, NOW(), 'action')");
    $stmt_log->execute(['activity_description' => $activity_description]);
}

// Handle adding a new notification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_notification'])) {
    $notification_text = htmlspecialchars($_POST['notification_text']);

    $stmt_add = $pdo->prepare("INSERT INTO notifications (notification_text) VALUES (:notification_text)");
    $stmt_add->execute(['notification_text' => $notification_text]);

    logAction($pdo, "added notification: $notification_text");

    header("Location: addnotifications.php"); // Redirect to avoid resubmission
    exit;
}

// Handle marking a notification as read
if (isset($_GET['mark_read'])) {
    $notification_id = intval($_GET['mark_read']);

    $stmt_update = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
    $stmt_update->execute(['id' => $notification_id]);

    logAction($pdo, "marked notification ID $notification_id as read");

    header("Location: addnotifications.php");
    exit;
}

// Handle deleting a notification
if (isset($_GET['delete'])) {
    $notification_id = intval($_GET['delete']);

    $stmt_delete = $pdo->prepare("DELETE FROM notifications WHERE id = :id");
    $stmt_delete->execute(['id' => $notification_id]);

    logAction($pdo, "Deleted notification ID $notification_id");

    header("Location: addnotifications.php");
    exit;
}

// Fetch all notifications
$stmt_notifications = $pdo->prepare("SELECT * FROM notifications ORDER BY created_at DESC");
$stmt_notifications->execute();
$notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);

// Fetch logs
$stmt_logs = $pdo->prepare("SELECT * FROM activity ORDER BY date DESC");
$stmt_logs->execute();
$logs = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <!-- <link rel="stylesheet" href="dashstyles.css"> -->
    <style>
        body{
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }

        .notifications {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .notifications h2 {
            font-size: 24px;
            font-weight: 400;
            margin-bottom: 20px;
            color: #333;
        }

        .notifications form {
            margin-bottom: 20px;
        }

        .notifications label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .notifications textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            resize: vertical;
        }

        .notifications button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .notifications button:hover {
            background-color: #3e8e41;
        }

        .notifications ul {
            list-style: none;
            padding: 0;
        }

        .notifications li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .notifications li a {
            color: #4285f4;
            text-decoration: none;
        }

        .notifications li a:hover {
            text-decoration: underline;
        }

        /* Header */
        header {
            background-color: #4285f4;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        header .logo {
            font-size: 24px;
            font-weight: bold; 
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo">Admin Panel</div>
            <ul class="menu">
                <li><a href="admin_profile.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
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