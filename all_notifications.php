<?php
session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch notifications for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM notifications ORDER BY created_at DESC");
// $stmt->bindParam(':id', $id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications</title>
    <link rel="stylesheet" href="ham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 30px;
        }

        .notification-box {
            background-color: #fff8b3;
            /* Light yellow background */
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .notification-header {
            font-weight: bold;
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .notification-content {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
        }

        .notification-timestamp {
            font-size: 14px;
            color: #888;
            text-align: right;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .notification-box {
                padding: 10px;
            }

            .notification-header {
                font-size: 16px;
            }

            .notification-content {
                font-size: 14px;
            }
        }

        #active {
            color: red;
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
                <li><a href="feed.php"><i class="fa-solid fa-newspaper"></i>&nbsp; Feed</a></li>
                <li><a href="all_tasks.php"><i class="fa fa-tasks"></i>&nbsp; All tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; All homework</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell" id="active"></i>&nbsp; All notifications</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp; All progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; All activities</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h2><i class="fa fa-bell"></i> Notifications</h2>

        <?php if ($notifications): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-box">
                    <div class="notification-content">
                        <?php echo htmlspecialchars($notification['notification_text']); ?>
                    </div>
                    <div class="notification-timestamp">
                        <?php echo htmlspecialchars($notification['created_at']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notifications found.</p>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>

</html>